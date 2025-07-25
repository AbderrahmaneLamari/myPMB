<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: author.class.php,v 1.183 2022/12/02 09:30:40 gneveu Exp $
if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
	die("no access");

use Pmb\Ark\Entities\ArkEntityPmb;
	// d�finition de la classe de gestion des 'auteurs'
if (! defined('AUTEUR_CLASS')) {
	define('AUTEUR_CLASS', 1);
	
	global $class_path, $include_path;
	
	require_once ($class_path ."/notice.class.php");
	require_once ("$class_path/aut_link.class.php");
	require_once ("$class_path/aut_pperso.class.php");
	require_once ("$class_path/audit.class.php");
	require_once ($class_path ."/synchro_rdf.class.php");
	require_once ($class_path ."/index_concept.class.php");
	require_once ($class_path ."/vedette/vedette_composee.class.php");
	require_once ($include_path ."/misc.inc.php");
	require_once ($include_path ."/isbn.inc.php");
	require_once($class_path.'/authorities_statuts.class.php');
	require_once($class_path."/indexation_authority.class.php");
	require_once($class_path."/authority.class.php");
	require_once $class_path.'/event/events/event_author.class.php';
	require_once $class_path.'/event/events/event_author_deduplication.class.php';
	require_once ($class_path.'/indexations_collection.class.php');
	require_once ($class_path.'/authorities_collection.class.php');
	require_once ($class_path.'/indexation_stack.class.php');
	require_once($include_path.'/templates/authors.tpl.php');
	require_once ($class_path.'/interface/entity/interface_entity_author_form.class.php');
	
	class auteur {
		
		// ---------------------------------------------------------------
		// propri�t�s de la classe
		// ---------------------------------------------------------------
		public $id; // MySQL id in table 'authors'
		public $type; // author type (70 or 71)
		public $name; // author name
		public $rejete; // author name (rejected element)
		public $date; // dates
		public $author_web; // web de l'auteur
		public $author_web_link; // lien web de l'auteur
		public $author_isni; // ISNI de l'auteur
		public $see; // 'see' author MySQL id
		public $see_libelle; // printable form of 'see' author (in fact 'display' of retained form)
		public $display; // usable form for displaying ( _name_, _rejete_ (_date1_-_date2_) )
		public $isbd_entry; // isbd like version ( _rejete_ _name_ (_date1_-_date2_))
		public $isbd_entry_lien_gestion; // lien sur le nom vers la gestion
		public $lieu; // lieu du congr�s
		public $ville; // ville du congr�s
		public $pays; // pays du congr�s
		public $subdivision; // subdivision
		public $numero; // numero de congr�s
		public $author_comment; // Commentaire, peut contenir du HTML
		public $duplicate_from_id = 0;
		public $import_denied = 0; // bool�en pour interdire les modification depuis un import d'autorit�s
		public $info_bulle ="";
		public $num_statut = 1;
		public $authority;
		public $cp_error_message = '';
		public $delete_error_message = '';
		protected static $long_maxi_name;
		protected static $long_maxi_rejete;
		protected static $controller;

		// ---------------------------------------------------------------
		// auteur($id) : constructeur
		// ---------------------------------------------------------------
		public function __construct($id = 0, $recursif = 0) {
			$this->id = intval($id);
			if ($this->id) {
				// on cherche � atteindre un auteur existant
				$this->recursif = $recursif;
			}
			$this->getData();
		}
		
		// ---------------------------------------------------------------
		// getData() : r�cup�ration infos auteur
		// ---------------------------------------------------------------
		public function getData() {
			global $msg;
			$this->type = '';
			$this->name = '';
			$this->rejete = '';
			$this->date = '';
			$this->author_web = '';
			$this->author_isni = '';
			$this->see = '';
			$this->see_libelle = '';
			$this->display = '';
			$this->isbd_entry = '';
			$this->author_comment = '';
			$this->subdivision = '';
			$this->lieu = '';
			$this->ville = '';
			$this->pays = '';
			$this->numero = '';
			$this->import_denied = 0;
			$this->num_statut = 1;
			$this->authority = '';
			if ($this->id) {
				$requete = "SELECT * FROM authors WHERE author_id=$this->id LIMIT 1 ";
				$result = pmb_mysql_query($requete);
				if (pmb_mysql_num_rows($result)) {
					$row = pmb_mysql_fetch_object($result);
					pmb_mysql_free_result($result);
					
					$this->id = $row->author_id;
					$this->type = $row->author_type;
					$this->name = $row->author_name;
					$this->rejete = $row->author_rejete;
					$this->date = $row->author_date;
					$this->author_web = $row->author_web;
					$this->author_isni = $row->author_isni;
					$this->see = $row->author_see;
					$this->author_comment = $row->author_comment;
					// Ajout pour les congr�s
					$this->subdivision = $row->author_subdivision;
					$this->lieu = $row->author_lieu;
					$this->ville = $row->author_ville;
					$this->pays = $row->author_pays;
					$this->numero = $row->author_numero;
					$this->import_denied = $row->author_import_denied;
					$this->authority = authorities_collection::get_authority(AUT_TABLE_AUTHORITY,0, ['num_object'=>$this->id, 'type_object' =>AUT_TABLE_AUTHORS]);
					$this->num_statut = $this->authority->get_num_statut();
					if ($this->type ==71) {
						// C'est une collectivit�
						$this->isbd_entry = $row->author_name;
						$this->display = $row->author_name;
						
						if ($row->author_subdivision) {
							$this->isbd_entry .= ". " .$row->author_subdivision;
							$this->display .= ". " .$row->author_subdivision;
						}
						
						if ($row->author_rejete) {
							$this->isbd_entry .= ", " .$row->author_rejete;
							$this->display .= ", " .$row->author_rejete;
							// $this->info_bulle=$row->author_rejete;
						}
						$liste_field = $liste_lieu = array();
						
						if ($row->author_numero) {
							$liste_field[] = $row->author_numero;
						}
						if ($row->author_date) {
							$liste_field[] = $row->author_date;
						}
						if ($row->author_lieu) {
							$liste_lieu[] = $row->author_lieu;
						}
						if ($row->author_ville) {
							$liste_lieu[] = $row->author_ville;
						}
						if ($row->author_pays) {
							$liste_lieu[] = $row->author_pays;
						}
						if (count($liste_lieu))
							$liste_field[] = implode(", ", $liste_lieu);
						if (count($liste_field)) {
							$liste_field = implode("; ", $liste_field);
							$this->isbd_entry .= ' (' .$liste_field .')';
							$this->display .= ' (' .$liste_field .')';
						}
					} elseif ($this->type ==72) {
						// C'est un congr�s
						$libelle = $msg["congres_libelle"] .": ";
						if ($row->author_rejete) {
							$this->isbd_entry = $row->author_name .", " .$row->author_rejete;
							$this->display = $libelle .$row->author_name .", " .$row->author_rejete;
						} else {
							$this->isbd_entry = $row->author_name;
							$this->display = $libelle .$row->author_name;
						}
						$liste_field = $liste_lieu = array();
						if ($row->author_subdivision) {
							$liste_field[] = $row->author_subdivision;
						}
						if ($row->author_numero) {
							$liste_field[] = $row->author_numero;
						}
						if ($row->author_date) {
							$liste_field[] = $row->author_date;
						}
						if ($row->author_lieu) {
							$liste_lieu[] = $row->author_lieu;
						}
						if ($row->author_ville) {
							$liste_lieu[] = $row->author_ville;
						}
						if ($row->author_pays) {
							$liste_lieu[] = $row->author_pays;
						}
						if (count($liste_lieu))
							$liste_field[] = implode(", ", $liste_lieu);
						if (count($liste_field)) {
							$liste_field = implode("; ", $liste_field);
							$this->isbd_entry .= ' (' .$liste_field .')';
							$this->display .= ' (' .$liste_field .')';
						}
					} else {
						// auteur physique
						if ($row->author_rejete) {
							$this->isbd_entry = "$row->author_name, $row->author_rejete";
							$this->display = "$row->author_name, $row->author_rejete";
						} else {
							$this->isbd_entry = $row->author_name;
							$this->display = $row->author_name;
						}
						if ($row->author_date) {
							$this->isbd_entry .= ' (' .$row->author_date .')';
						}
					}
					
					// Ajoute un lien sur la fiche auteur si l'utilisateur � acc�s aux autorit�s
					// defined('SESSrights') dans le cas de l'indexation il 'y a pas de AUTH ni de session
					if (defined('SESSrights') && ( intval(SESSrights) & AUTORITES_AUTH)) {
						$this->isbd_entry_lien_gestion = "<a href='./autorites.php?categ=see&sub=author&id=" .$this->id ."' class='lien_gestion' title='" .$this->info_bulle ."'>" .$this->display ."</a>";
					} else {
						$this->isbd_entry_lien_gestion = $this->display;
					}
					
					if ($row->author_web)
						$this->author_web_link = " <a href='$row->author_web' target=_blank><img src='".get_url_icon('globe.gif')."' border=0 /></a>";
					else
						$this->author_web_link = "";
					
					if ($row->author_see &&! $this->recursif) {
						$see = authorities_collection::get_authority(AUT_TABLE_AUTHORS, $row->author_see, array('recursif' => 1));
						$this->see_libelle = $see->display;
					} else {
						$this->see_libelle = '';
					}
				}
			}
		}
		
		public function build_header_to_export() {
		    global $msg;
		    
		    $data = array(
		    		$msg[205],
		    		$msg[201],
		    		$msg[202],
		    		$msg[653],
		    		$msg[147],
		            $msg['author_isni'],
		    		$msg[707],
		    		$msg['congres_subdivision_libelle'],
		    		$msg['congres_lieu_libelle'],
		    		$msg['congres_ville_libelle'],
		    		$msg['congres_pays_libelle'],
		    		$msg['congres_numero_libelle'],
		    		$msg[4019],
		    		$msg[147],
		    		$msg[206],
		    );
		    return $data;
		}
		
		public function build_data_to_export() {
		    $data = array(
		    		$this->type,
		    		$this->name,
		    		$this->rejete,
		    		$this->date,
		            $this->author_web,
		            $this->author_isni,
		    		$this->author_comment,
		    		$this->subdivision,
		    		$this->lieu,
		    		$this->ville,
		    		$this->pays,
		    		$this->numero,
		    		$this->num_statut,
		    		$this->author_web_link,
		    		$this->see_libelle,
		    );
		    return $data;
		}
		
		protected function get_content_form() {
			global $charset, $thesaurus_concepts_active;
			global $author_content_form;
			
			$content_form = $author_content_form;
			$content_form = str_replace('!!id!!', $this->id, $content_form);
			
			// mise � jour de la zone type
			$sel_coll = "";
			$sel_congres = "";
			$sel_pp = "";
			switch ($this->type) {
				case 71 :
					$sel_coll = " SELECTED";
						break;
				case 72 :
					$sel_congres = " SELECTED";
					break;
				default :
					$sel_pp = " SELECTED";
					break;
			}
			if ($this->import_denied ==1 || !$this->id) {
				$import_denied_checked = "checked='checked'";
			} else {
				$import_denied_checked = "";
			}
			
			$aut_link = new aut_link(AUT_TABLE_AUTHORS, $this->id);
			$content_form = str_replace('<!-- aut_link -->', $aut_link->get_form('saisie_auteur'), $content_form);
			
			$aut_pperso = new aut_pperso("author", $this->id);
			$content_form = str_replace('!!aut_pperso!!', $aut_pperso->get_form(), $content_form);
			
			$content_form = str_replace('!!author_nom!!', htmlentities($this->name, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!author_rejete!!', htmlentities($this->rejete, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!voir_id!!', $this->see, $content_form);
			$content_form = str_replace('!!voir_libelle!!', htmlentities($this->see_libelle, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!date!!', htmlentities($this->date, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!lieu!!', htmlentities($this->lieu, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!ville!!', htmlentities($this->ville, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!pays!!', htmlentities($this->pays, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!subdivision!!', htmlentities($this->subdivision, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!numero!!', htmlentities($this->numero, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!author_web!!', htmlentities($this->author_web, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!author_isni!!', htmlentities($this->author_isni, ENT_QUOTES, $charset), $content_form);
			$content_form = str_replace('!!sel_pp!!', $sel_pp, $content_form);
			$content_form = str_replace('!!sel_coll!!', $sel_coll, $content_form);
			$content_form = str_replace('!!sel_congres!!', $sel_congres, $content_form);
			$content_form = str_replace('!!author_comment!!', $this->author_comment, $content_form);
			$content_form = str_replace('!!author_import_denied!!', $import_denied_checked, $content_form);
			
			if ($thesaurus_concepts_active ==1) {
				$index_concept = new index_concept($this->id, TYPE_AUTHOR);
				$content_form = str_replace('!!concept_form!!', $index_concept->get_form('saisie_auteur'), $content_form);
			} else {
				$content_form = str_replace('!!concept_form!!', "", $content_form);
			}
			$authority = authorities_collection::get_authority(AUT_TABLE_AUTHORITY, 0, [ 'num_object' => $this->id, 'type_object' => AUT_TABLE_AUTHORS]);
			$content_form = str_replace('!!thumbnail_url_form!!', thumbnail::get_form('authority', $authority->get_thumbnail_url()), $content_form);
			
			return $content_form;
		}
		
		public function get_form($duplicate = false) {
			global $msg;
			global $user_input, $nbr_lignes, $page;
			
			$interface_form = new interface_entity_author_form('saisie_auteur');
			if(isset(static::$controller) && is_object(static::$controller)) {
				$interface_form->set_controller(static::$controller);
			}
			$interface_form->set_enctype('multipart/form-data');
			if($this->id && !$duplicate) {
				switch ($this->type) {
					case 71 :
						$interface_form->set_label($msg['aut_modifier_coll']);
						break;
					case 72 :
						$interface_form->set_label($msg['aut_modifier_congres']);
						break;
					default :
						$interface_form->set_label($msg['199']);
						break;
				}
				$interface_form->set_document_title($this->name.($this->rejete ? ', '.$this->rejete : '').' - '.$interface_form->get_label());
			} else {
				switch ($this->type) {
					case 71 :
						$interface_form->set_label($msg['aut_ajout_collectivite']);
						break;
					case 72 :
						$interface_form->set_label($msg['aut_ajout_congres']);
						break;
					default :
						$interface_form->set_label($msg['207']);
						break;
				}
				$interface_form->set_document_title($interface_form->get_label());
			}
			$interface_form->set_object_id(($duplicate ? 0 : $this->id))
			->set_num_statut($this->num_statut)
			->set_author_type($this->type)
			->set_content_form($this->get_content_form())
			->set_table_name('authors')
			->set_field_focus('author_nom')
			->set_url_base(static::format_url())
			->set_duplicable(true);
			
			$interface_form->set_page($page)
			->set_nbr_lignes($nbr_lignes)
			->set_user_input($user_input);
			return $interface_form->get_display();
		}
		
		public function get_liste_renvoyes() {
			global $msg;
			
			$liste_renvoyes = "";
			$requete = "SELECT * FROM authors WHERE ";
			$requete .= "author_see = '$this->id' ";
			$requete .= "ORDER BY author_name, author_rejete ";
			$res = pmb_mysql_query($requete);
			$nbr_lignes = pmb_mysql_num_rows($res);
			if ($nbr_lignes) {
				$liste_renvoyes = "<br /><div class='row'><h3>$msg[aut_list_renv_titre]</h3><table>";
				$parity = 1;
				while ( ($author_renvoyes = pmb_mysql_fetch_object($res)) ) {
					$author_renvoyes->author_name = $author_renvoyes->author_name;
					$author_renvoyes->author_rejete = $author_renvoyes->author_rejete;
					if ($author_renvoyes->author_rejete)
						$author_entry = $author_renvoyes->author_name .',&nbsp;' .$author_renvoyes->author_rejete;
						else
							$author_entry = $author_renvoyes->author_name;
							if ($author_renvoyes->author_date)
								$author_entry .= "&nbsp;($author_renvoyes->author_date)";
								$link_auteur = "./autorites.php?categ=see&sub=author&id=".$author_renvoyes->author_id;
								if ($parity %2) {
									$pair_impair = "even";
								} else {
									$pair_impair = "odd";
								}
								$parity += 1;
								$tr_javascript = " onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='$link_auteur';\" ";
								$liste_renvoyes .= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>
									<td style='vertical-align:top'>
								$author_entry
								</td>
							</tr>";
				} // fin while
				$liste_renvoyes .= "</table></div>";
			}
			return $liste_renvoyes;
		}
		
		// ---------------------------------------------------------------
		// show_form : affichage du formulaire de saisie
		// ---------------------------------------------------------------
		public function show_form($type_autorite = 70, $duplicate=false) {
			if (! $this->id) {
				$this->type = $type_autorite;
			}
			print $this->get_form($duplicate);
			if ($this->id && !$duplicate) {
				print $this->get_liste_renvoyes();
			}
		}
		
		// ---------------------------------------------------------------
		// replace_form : affichage du formulaire de remplacement
		// ---------------------------------------------------------------
		public function replace_form() {
			global $author_replace_content_form;
			global $msg;
			global $include_path;
			
			// a compl�ter
			
			if (! $this->id ||! $this->name) {
				require_once ("$include_path/user_error.inc.php");
				error_message($msg[161], $msg[162], 1, static::format_url('&sub=&id='));
				return false;
			}
			
			$content_form = $author_replace_content_form;
			$content_form = str_replace('!!id!!', $this->id, $content_form);
			
			$interface_form = new interface_autorites_replace_form('author_replace');
			$interface_form->set_object_id($this->id)
			->set_label($msg["159"]." ".$this->display)
			->set_content_form($content_form)
			->set_table_name('authors')
			->set_field_focus('author_libelle')
			->set_url_base(static::format_url());
			print $interface_form->get_display();
			return true;
		}
		
		// ---------------------------------------------------------------
		// delete() : suppression de l'auteur
		// ---------------------------------------------------------------
		public function delete() {
			global $msg;
			
			if (! $this->id) // impossible d'acc�der � cette notice auteur
				return $msg[403];

// 			if($event->get_error_message()){
// 				return '<strong>' .$this->display ."</strong><br />" .$event->get_error_message().'<br/>';
// 			}
			
			$is_used = $this->check_uses();
			if(!$is_used){ //Check uses a renvoy� false, l'auteur n'est pas utilis� par une autre autorit�

				$evt_handler = events_handler::get_instance();
				$event = new event_author("author", "delete");
				$event->set_id_author($this->id);
				$evt_handler->send($event);
				
				// liens entre autorit�s
				$aut_link = new aut_link(AUT_TABLE_AUTHORS, $this->id);
				$aut_link->delete();
				$aut_pperso = new aut_pperso("author", $this->id);
				$aut_pperso->delete();
					
				// nettoyage indexation concepts
				$index_concept = new index_concept($this->id, TYPE_AUTHOR);
				$index_concept->delete();
					
				// nettoyage indexation 
				indexation_authority::delete_all_index($this->id, "authorities", "id_authority", AUT_TABLE_AUTHORS);
					
				// suppression dans la table de stockage des num�ros d'autorit�s...
				auteur::delete_autority_sources($this->id);
					
				// on supprime automatiquement les formes rejetes
				$query = "select author_id from authors where author_see = " .$this->id;
				$result = pmb_mysql_query($query);
				if (pmb_mysql_num_rows($result)) {
					while ( $row = pmb_mysql_fetch_object($result) ) {
						// on regarde si cette forme est utilis�e...
						$query2 = "select count(responsability_author) from responsability where responsability_author =" .$row->author_id;
						$result2 = pmb_mysql_query($query2);
						$query3 = "select count(responsability_tu_author_num) from responsability_tu where responsability_tu_author_num =" .$row->author_id;
						$result3 = pmb_mysql_query($query3);
						$rejete = new auteur($row->author_id);
						// elle est utilis�e donc on nettoie juste la r�f�rence
						if (pmb_mysql_num_rows($result2) ||pmb_mysql_num_rows($result3)) {
							pmb_mysql_query("update authors set author_see= 0  where author_id = " .$row->author_id);
						} else {
							// sinon, on supprime...
							$rejete->delete();
						}
					}
				}
				audit::delete_audit(AUDIT_AUTHOR, $this->id);
				// effacement dans l'entrepot rdf
				auteur::delete_enrichment($this->id);
				// effacement de l'identifiant unique d'autorit�
				$authority = new authority(0, $this->id, AUT_TABLE_AUTHORS);
				$authority->delete();
				// effacement dans la table des auteurs
				$requete = "DELETE FROM authors WHERE author_id='$this->id' ";
				pmb_mysql_query($requete);
			}else{ //Lorsque l'autorit� est utilis� par un autre �l�ment, la m�thode check uses renvoi le d�tail de ces utilisations
				//On ne peut pas la supprimer, on renvoi un message d'erreur indiquant le d�tail de ces utilisations
				return $is_used; 
			}
			
			return false;
		}
		
		protected function check_uses(){
			global $msg;
			
			$message = "";
			
			//publication d'un event !
			$evt_handler = events_handler::get_instance();
			$event = new event_author("author", "author_check_uses");
			$event->set_id_author($this->id);
			$evt_handler->send($event);
			
			//tester retour event ; stocker le mesasge dans l'event author, sotcker les ids test�s dans l'event author
			
			if(!$event->get_error_message()){
				if(($usage=aut_pperso::delete_pperso(AUT_TABLE_AUTHORS, $this->id,0) )){
					// Cette autorit� est utilis�e dans des champs perso, impossible de supprimer
					$message.= '<strong>'.$this->display.'</strong><br />'.$msg['autority_delete_error'].'<br /><br />'.$usage['display'];
				}
					
				// r�cup�ration du nombre de notices affect�es
				$requete = "SELECT count(1) FROM responsability WHERE ";
				$requete .= "responsability_author='$this->id' ";
				
				$res = pmb_mysql_query($requete);
				$nbr_lignes = pmb_mysql_result($res, 0, 0);
				if ($nbr_lignes) {
					// Cet auteur est utilis� dans des notices, impossible de le supprimer
					$message.= '<strong>' .$this->display ."</strong><br />${msg[402]}";
				}
				
				// r�cup�ration du nombre de titres affect�es
				$requete = "SELECT count(1) FROM responsability_tu WHERE ";
				$requete .= "responsability_tu_author_num='$this->id' ";
				
				$res = pmb_mysql_query($requete);
				$nbr_lignes = pmb_mysql_result($res, 0, 0);
				if ($nbr_lignes) {
					// Cet auteur est utilis� dans des tirres uniformes, impossible de le supprimer
					$message.= '<strong>' .$this->display ."</strong><br />${msg['tu_dont_del_author']}";
				}
				
				$attached_vedettes = vedette_composee::get_vedettes_built_with_element($this->id, TYPE_AUTHOR);
				
				if(count($attached_vedettes)){
					if(isset($event->get_elements()['concept'])){
						if(count(array_diff($event->get_elements()['concept'], $attached_vedettes))){
							$message.= '<strong>' .$this->display ."</strong><br />" .$msg["vedette_dont_del_autority"].'<br/>'.vedette_composee::get_vedettes_display($attached_vedettes);
						}
					}else{
						$message.= '<strong>' .$this->display ."</strong><br />" .$msg["vedette_dont_del_autority"].'<br/>'.vedette_composee::get_vedettes_display($attached_vedettes);
					}
				}
				return $message;
			}
			return $event->get_error_message();
			
		}
		
		// ---------------------------------------------------------------
		// delete_autority_sources($idcol=0) : Suppression des informations d'import d'autorit�
		// ---------------------------------------------------------------
		static public function delete_autority_sources($idaut = 0) {
			$tabl_id = array();
			if (! $idaut) {
				$requete = "SELECT DISTINCT num_authority FROM authorities_sources LEFT JOIN authors ON num_authority=author_id  WHERE authority_type = 'author' AND author_id IS NULL";
				$res = pmb_mysql_query($requete);
				if (pmb_mysql_num_rows($res)) {
					while ( $ligne = pmb_mysql_fetch_object($res) ) {
						$tabl_id[] = $ligne->num_authority;
					}
				}
			} else {
				$tabl_id[] = $idaut;
			}
			foreach ( $tabl_id as $value ) {
				// suppression dans la table de stockage des num�ros d'autorit�s...
				$query = "select id_authority_source from authorities_sources where num_authority = " .$value ." and authority_type = 'author'";
				$result = pmb_mysql_query($query);
				if (pmb_mysql_num_rows($result)) {
					while ( $ligne = pmb_mysql_fetch_object($result) ) {
						$query = "delete from notices_authorities_sources where num_authority_source = " .$ligne->id_authority_source;
						pmb_mysql_query($query);
					}
				}
				$query = "delete from authorities_sources where num_authority = " .$value ." and authority_type = 'author'";
				pmb_mysql_query($query);
			}
		}
		
		// ---------------------------------------------------------------
		// replace($by) : remplacement de l'auteur
		// ---------------------------------------------------------------
		public function replace($by, $link_save = 0) {
			global $msg;
			global $pmb_synchro_rdf;
			global $pmb_ark_activate;
			
			if (($this->id ==$by) ||(! $this->id)) {
				return $msg[223];
			}
			
			//publication d'un event permettant de signifier que l'on va remplacer un auteur par un autre ; 
			$evt_handler = events_handler::get_instance();
			$event = new event_author("author", "replace");
			$event->set_id_author($this->id);
			$event->set_replacement_id($by);
			
			$evt_handler->send($event);
			
			$aut_link = new aut_link(AUT_TABLE_AUTHORS, $this->id);
			// "Conserver les liens entre autorit�s" est demand�
			if ($link_save) {
				// liens entre autorit�s
				$aut_link->add_link_to(AUT_TABLE_AUTHORS, $by);
				// Voir aussi
				if ($this->see) {
					$requete = "UPDATE authors SET author_see='" .$this->see ."'  WHERE author_id='$by' ";
					pmb_mysql_query($requete);
				}
			}
			$aut_link->delete();
			
			// remplacement des renvoi voir (Forme retenue)
		    $requete = "UPDATE authors SET author_see='" .$by ."'  WHERE author_see='".$this->id."' ";
		    pmb_mysql_query($requete);
			    
			vedette_composee::replace(TYPE_AUTHOR, $this->id, $by);
			
			// remplacement dans les responsabilit�s
			$requete = "UPDATE responsability SET responsability_author='$by' WHERE responsability_author='$this->id' ";
			pmb_mysql_query($requete);
			
			// effacement dans les responsabilit�s
			$requete = "DELETE FROM responsability WHERE responsability_author='$this->id' ";
			pmb_mysql_query($requete);
			
			// remplacement dans les titres uniformes
			$requete = "UPDATE responsability_tu SET responsability_tu_author_num='$by' WHERE responsability_tu_author_num='$this->id' ";
			pmb_mysql_query($requete);
			$requete = "DELETE FROM responsability_tu WHERE responsability_tu_author_num='$this->id' ";
			pmb_mysql_query($requete);
			
			// nettoyage d'autorities_sources
			$query = "select * from authorities_sources where num_authority = " .$this->id ." and authority_type = 'author'";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				while ( $row = pmb_mysql_fetch_object($result) ) {
					if ($row->authority_favorite ==1) {
						// on suprime les r�f�rences si l'autorit� a �t� import�e...
						$query = "delete from notices_authorities_sources where num_authority_source = " .$row->id_authority_source;
						pmb_mysql_query($query);
						$query = "delete from authorities_sources where id_authority_source = " .$row->id_authority_source;
						pmb_mysql_query($query);
					} else {
						// on fait suivre le reste
						$query = "update authorities_sources set num_authority = " .$by ." where id_authority_source = " .$row->id_authority_source;
						pmb_mysql_query($query);
					}
				}
			}
			
			// nettoyage indexation concepts
			$index_concept = new index_concept($this->id, TYPE_AUTHOR);
			$index_concept->delete();
			
			//Remplacement dans les champs persos s�lecteur d'autorit�
			aut_pperso::replace_pperso(AUT_TABLE_AUTHORS, $this->id, $by);
			
			audit::delete_audit(AUDIT_AUTHOR, $this->id);
			
			// nettoyage indexation
			indexation_authority::delete_all_index($this->id, "authorities", "id_authority", AUT_TABLE_AUTHORS);
			
			if ($pmb_ark_activate) {
			    $idReplaced = authority::get_authority_id_from_entity($this->id, AUT_TABLE_AUTHORS);
			    $idReplacing = authority::get_authority_id_from_entity($by, AUT_TABLE_AUTHORS);
			    if ($idReplaced && $idReplacing) {
			        $arkEntityReplaced = ArkEntityPmb::getEntityClassFromType(TYPE_AUTHORITY, $idReplaced);
			        $arkEntityReplacing = ArkEntityPmb::getEntityClassFromType(TYPE_AUTHORITY, $idReplacing);
			        $arkEntityReplaced->markAsReplaced($arkEntityReplacing);
			    }
			}
			// effacement de l'identifiant unique d'autorit�
			$authority = new authority(0, $this->id, AUT_TABLE_AUTHORS);
			$authority->delete();
			
			// effacement dans la table des auteurs
			$requete = "DELETE FROM authors WHERE author_id='$this->id' ";
			pmb_mysql_query($requete);
			
			auteur::update_index($by);
			
			// mise � jour de l'oeuvre rdf
			if ($pmb_synchro_rdf) {
				$synchro_rdf = new synchro_rdf();
				$synchro_rdf->replaceAuthority($this->id, $by, 'auteur');
			}
			
			return FALSE;
		}
		
		/**
		 * Initialisation du tableau de valeurs pour update et import
		 */
		protected static function get_default_data() {
			return array(
					'type' => '',
					'name' => '',
					'rejete' => '',
					'date' => '',
					'lieu' => '',
					'ville' => '',
					'pays' => '',
					'subdivision' => '',
					'numero' => '',
					'voir_id' => 0,
			        'author_web' => '',
			        'author_isni' => '',
					'author_comment' => '',
					'import_denied' => 0,
					'statut' => 1,
					'thumbnail_url' => ''
			);
		}
		
		// ---------------------------------------------------------------
		// update($value) : mise � jour de l'auteur
		// ---------------------------------------------------------------
		public function update($value, $force = false) {
			global $msg, $charset;
			global $include_path;
			global $pmb_synchro_rdf;
			global $thesaurus_concepts_active;
			global $opac_enrichment_bnf_sparql;
			global $pmb_controle_doublons_diacrit;
			
			$value = array_merge(static::get_default_data(), $value);
			
			if (! $value['name'])
				return false;
				
			// nettoyage des cha�nes en entr�e
			$value['name'] = clean_string($value['name']);
			$value['rejete'] = clean_string($value['rejete']);
			$value['date'] = clean_string($value['date']);
			$value['lieu'] = clean_string($value['lieu']);
			$value['ville'] = clean_string($value['ville']);
			$value['pays'] = clean_string($value['pays']);
			$value['subdivision'] = clean_string($value['subdivision']);
			$value['numero'] = clean_string($value['numero']);
			
			if (!$force) {
			    // s'assurer que l'auteur n'existe pas d�j�
			    $and_dedoublonnage = '';
			    switch ($value['type']) {
			        case 71 : // Collectivit�
			            $and_dedoublonnage = " and author_subdivision ='" .$value['subdivision'] ."' and author_lieu='" .$value['lieu'] ."' and author_ville = '" .$value['ville'] ."' and author_pays = '" .$value['pays'] ."' and author_numero ='" .$value['numero'] ."' ";
			            break;
			        case 72 : // Congr�s
			            $and_dedoublonnage = " and author_subdivision ='" .$value['subdivision'] ."' and author_lieu='" .$value['lieu'] ."' and author_ville = '" .$value['ville'] ."' and author_pays = '" .$value['pays'] ."' and author_numero ='" .$value['numero'] ."' ";
			            break;
			    }
			    $binary = '';
			    if ($pmb_controle_doublons_diacrit) {
			        $binary = 'BINARY';
			    }
			    if ($this->id) {
			        $and_dedoublonnage.= " and author_id!='" .$this->id ."' ";
			    }
			    $dummy = "SELECT author_id FROM authors WHERE author_type='" . $value['type'] ."' AND " . $binary . " author_name='" . $value['name'] ."'
                            AND " . $binary . " author_rejete='" . $value['rejete'] ."'
                            AND author_date='" . $value['date'] . "' $and_dedoublonnage";			    
			    $check = pmb_mysql_query($dummy);
			    if (pmb_mysql_num_rows($check)) {			        
			        $auteur_exists = new auteur(pmb_mysql_result($check, 0, "author_id"));
			        print $this->warning_already_exist($msg[200], $msg[220] ." -> " .$auteur_exists->display, $value);
			        return FALSE;
			    }
			    // s'assurer que la forme_retenue ne pointe pas dans les deux sens
			    if ($this->id) {
			        $dummy = "SELECT * FROM authors WHERE author_id='" .$value['voir_id'] ."' and  author_see='" .$this->id ."'";
			        $check = pmb_mysql_query($dummy);
			        if (pmb_mysql_num_rows($check)) {
			        	print $this->warning_already_exist($msg[200], $msg['author_forme_retenue_error'] ." -> " .$auteur_exists->display, $value);
			            return FALSE;
			        }
			    }
			}
			$requete = 'SET author_type="'.$value['type'].'", ';
			$requete .= 'author_name="'.$value['name'].'", ';
			$requete .= 'author_rejete="'.$value['rejete'].'", ';
			$requete .= 'author_date="'.$value['date'].'", ';
			$requete .= 'author_lieu="'.$value['lieu'].'", ';
			$requete .= 'author_ville="'.$value['ville'].'", ';
			$requete .= 'author_pays="'.$value['pays'].'", ';
			$requete .= 'author_subdivision="'.$value['subdivision'].'", ';
			$requete .= 'author_numero="'.$value['numero'].'", ';
			$requete .= 'author_web="'.$value['author_web'].'", ';
			$requete .= 'author_isni="'.$value['author_isni'].'", ';
			$requete .= 'author_see="'.$value['voir_id'].'", ';
			$requete .= 'author_comment="'.$value['author_comment'].'", ';
			$word_to_index = $value['name'].' '.$value['rejete'].' '.$value['lieu'].' '.$value['ville'].' '.$value['pays'].' '.$value['numero'].' '.$value['subdivision'];
			if ($value['type'] ==72)
				$word_to_index .= ' '.$value['date'];
			$requete .= 'index_author=" '.strip_empty_chars($word_to_index).' ",';
			$requete .= 'author_import_denied="'.($value['import_denied'] ? 1 : 0).'"';
			if ($this->id) {
				
				audit::insert_modif(AUDIT_AUTHOR, $this->id);
				
				// update
				// on check s'il n'y a pas un renvoi circulaire
				if ($this->id ==$value['voir_id']) {
					require_once ("$include_path/user_error.inc.php");
					warning($msg[199], htmlentities($msg[222] ." -> " .$this->display, ENT_QUOTES, $charset));
					return FALSE;
				}
				
				$requete = 'UPDATE authors ' .$requete;
				$requete .= ' WHERE author_id=' .$this->id .' ;';
				if (pmb_mysql_query($requete)) {
					// liens entre autorit�s
					$aut_link = new aut_link(AUT_TABLE_AUTHORS, $this->id);
					$aut_link->save_form();
					$aut_pperso = new aut_pperso("author", $this->id);
					if($aut_pperso->save_form()){
						$this->cp_error_message = $aut_pperso->error_message;
						return false; 
					}
					
					// mise � jour de l'auteur dans la base rdf
					if ($pmb_synchro_rdf) {
						$synchro_rdf = new synchro_rdf();
						$synchro_rdf->updateAuthority($this->id, 'auteur');
					}
					
					// ////////////////////////modif de l'update///////////////////////////////
					if($opac_enrichment_bnf_sparql){
						$query = "select 1 from authors where (author_enrichment_last_update < now()-interval '0' day) and author_id=$this->id";
						$result = pmb_mysql_query($query);
						if ($result && pmb_mysql_num_rows($result)) {
							auteur::author_enrichment($this->id);
						}
					}
					// ////////////////////////////////////////////////////////////////////////
				} else {
					require_once ("$include_path/user_error.inc.php");
					warning($msg[199], htmlentities($msg[208] ." -> " .$this->display, ENT_QUOTES, $charset));
					return FALSE;
				}
			} else {
				// creation
				$requete = 'INSERT INTO authors ' .$requete .' ';
				if (pmb_mysql_query($requete)) {
					$this->id = pmb_mysql_insert_id();
					
					audit::insert_creation(AUDIT_AUTHOR, $this->id);
					
					// liens entre autorit�s
					$aut_link = new aut_link(AUT_TABLE_AUTHORS, $this->id);
					$aut_link->save_form();
					$aut_pperso = new aut_pperso("author", $this->id);
					if($aut_pperso->save_form()){
						$this->cp_error_message = $aut_pperso->error_message;
						return false; 
					}
					
					// ajout des enrichissements si activ�s
					if ($opac_enrichment_bnf_sparql) {						
						auteur::author_enrichment($this->id);
					}
					
				} else {
					require_once ("$include_path/user_error.inc.php");
					warning($msg[200], htmlentities($msg[221] ." -> " .$requete, ENT_QUOTES, $charset));
					return FALSE;
				}
			}
			//update authority informations
			$authority = new authority(0, $this->id, AUT_TABLE_AUTHORS);
			$authority->set_num_statut($value['statut']);
			$authority->set_thumbnail_url($value['thumbnail_url']);
			$authority->update();
			// Indexation concepts
			if ($thesaurus_concepts_active ==1) {
				$index_concept = new index_concept($this->id, TYPE_AUTHOR);
				$index_concept->save();
			}

			// Mise � jour des vedettes compos�es contenant cette autorit�
			vedette_composee::update_vedettes_built_with_element($this->id, TYPE_AUTHOR);

			auteur::update_index($this->id);

			//publication d'un event !
			$evt_handler = events_handler::get_instance();
			$event = new event_author("author", "update");
			$event->set_id_author($this->id);
			$evt_handler->send($event);
			return TRUE;
		}
		
		// ---------------------------------------------------------------
		// import() : import d'un auteur
		// ---------------------------------------------------------------
		// fonction d'import de notice auteur (membre de la classe 'author');
		static public function import($data) {
			
			// cette m�thode prend en entr�e un tableau constitu� des informations �diteurs suivantes :
			// $data['type'] type de l'autorit� (70 , 71 ou 72)
			// $data['name'] �l�ment d'entr�e de l'autorit�
			// $data['rejete'] �l�ment rejet�
			// $data['date'] dates de l'autorit�
			// $data['lieu'] lieu du congr�s 210$e
			// $data['ville'] ville du congr�s
			// $data['pays'] pays du congr�s
			// $data['subdivision'] 210$b
			// $data['numero'] numero du congr�s 210$d
			// $data['voir_id'] id de la forme retenue (sans objet pour l'import de notices)
			// $data['author_comment'] commentaire
			// $data['authority_number'] Num�ro d'autortit�
			
			// TODO gestion du d�doublonnage !
			global $pmb_controle_doublons_diacrit;
			
			// check sur le type de la variable pass�e en param�tre
			if ((empty($data) && !is_array($data)) || !is_array($data)) {
				// si ce n'est pas un tableau ou un tableau vide, on retourne 0
				return 0;
			}
			$data = array_merge(static::get_default_data(), $data);
			
			// check sur les �l�ments du tableau (data['name'] ou data['rejete'] est requis).
			if(!isset(static::$long_maxi_name)) {
				static::$long_maxi_name = pmb_mysql_field_len(pmb_mysql_query("SELECT author_name FROM authors limit 1"), 0);
			}
			if(!isset(static::$long_maxi_rejete)) {
				static::$long_maxi_rejete = pmb_mysql_field_len(pmb_mysql_query("SELECT author_rejete FROM authors limit 1"), 0);
			}
			
			// #109747 - Avant on enlevait les crochets via une regex. Si un jour l'import pose probl�me, �a peut �tre li�
			$data['name'] = rtrim(substr(rtrim(ltrim($data['name'])), 0, static::$long_maxi_name));
			$data['rejete'] = rtrim(substr(rtrim(ltrim($data['rejete'])), 0, static::$long_maxi_rejete));
			
			if (! $data['name'] &&! $data['rejete']) {
				return 0;
			}
			
			// check sur le type d'autorit�
			if (! $data['type'] ==70 &&! $data['type'] ==71 &&! $data['type'] ==72) {
				return 0;
			}
			
			// tentative de r�cup�rer l'id associ�e dans la base (implique que l'autorit� existe)
			
			// pr�paration de la requ�te
			$key0 = $data['type'];
			$key1 = addslashes($data['name']);
			$key2 = addslashes($data['rejete']);
			$key3 = addslashes($data['date']);
			$key4 = addslashes($data['subdivision']);
			$key5 = addslashes($data['lieu']);
			$key6 = addslashes($data['ville']);
			$key7 = addslashes($data['pays']);
			$key8 = addslashes($data['numero']);
			
			// Le lieu correspon � "ville; Pays"
			if ($key5 == $key6."; ".$key7) {
			    // Ne pas le prendre en compte pour le test de doublon
			    $key5 = ""; 
			}
			
			$data['lieu'] = addslashes($data['lieu']);
			$data['ville'] = addslashes($data['ville']);
			$data['pays'] = addslashes($data['pays']);
			$data['subdivision'] = addslashes($data['subdivision']);
			$data['numero'] = addslashes($data['numero']);
			$data['author_comment'] = addslashes($data['author_comment']);
			$data['author_web'] = addslashes($data['author_web']);
			$data['author_isni'] = addslashes($data['author_isni']);
    		if(!$data['statut']){
    		    $data['statut'] = 1;
    		}else{
    		    $data['statut']+=0;
    		}
    		
    		$binary = '';
    		if ($pmb_controle_doublons_diacrit) {
    		    $binary = 'BINARY';
    		}    		
    		$query = "SELECT author_id FROM authors WHERE author_type='${key0}' AND " . $binary . " author_name='${key1}' AND " . $binary . "  author_rejete='${key2}' AND author_date='${key3}'";
			if ($data["type"] >70) {
				$query .= " and author_subdivision='${key4}' and author_lieu='${key5}' and author_ville='${key6}' and author_pays='${key7}' and author_numero='${key8}'";
			}
			$query .= " LIMIT 1";
			$result = pmb_mysql_query($query);
			if (! $result)
				die("can't SELECT in database");
				// r�sultat
				
			// r�cup�ration du r�sultat de la recherche
			if(pmb_mysql_num_rows($result)) {
				$aut = pmb_mysql_fetch_object($result);
				// du r�sultat et r�cup�ration �ventuelle de l'id
				if ($aut->author_id)
					return $aut->author_id;
			}
				
				// id non-r�cup�r�e, il faut cr�er l'auteur
			$query = 'INSERT INTO authors SET author_type="'.$key0.'", ';
			$query .= 'author_name="'.$key1.'", ';
			$query .= 'author_rejete="'.$key2.'", ';
			$query .= 'author_date="'.$key3.'", ';
			$query .= 'author_lieu="'.$data['lieu'].'", ';
			$query .= 'author_ville="'.$data['ville'].'", ';
			$query .= 'author_pays="'.$data['pays'].'", ';
			$query .= 'author_subdivision="'.$data['subdivision'].'", ';
			$query .= 'author_numero="'.$data['numero'] .'", ';
			$query .= 'author_web="'.$data['author_web'].'", ';
			$query .= 'author_isni="'.$data['author_isni'].'", ';
			$query .= 'author_comment="'.$data['author_comment'].'", ';
			$word_to_index = $key1 .' ' .$key2 .' ' .$data['lieu'] .' ' .$data['ville'] .' ' .$data['pays'] .' ' .$data['numero'] .' ' .$data["subdivision"];
			if ($key0 =="72")
				$word_to_index .= " " .$key3;
			$query .= 'index_author=" '.strip_empty_chars($word_to_index).' " ';
			
			$result = pmb_mysql_query($query);
			if (! $result)
				die("can't INSERT into table authors :<br /><b>$query</b> ");
			
			$id = pmb_mysql_insert_id();
			audit::insert_creation(AUDIT_AUTHOR, $id);
			
			//update authority informations
			$authority = new authority(0, $id, AUT_TABLE_AUTHORS);
			$authority->set_num_statut($data['statut']);
			$authority->set_thumbnail_url($data['thumbnail_url']);
			$authority->update();
			
			auteur::update_index($id);
			
			return $id;
		}
		
		// ---------------------------------------------------------------
		// search_form() : affichage du form de recherche
		// ---------------------------------------------------------------
		public static function search_form($type_autorite = 7) {
			global $user_query;
			global $msg;
			global $user_input, $charset;
			global $authority_statut;
			
			$sel_tout = ($type_autorite ==7) ? 'selected' : " ";
			$sel_pp = ($type_autorite ==70) ? 'selected' : " ";
			$sel_coll = ($type_autorite ==71) ? 'selected' : " ";
			$sel_congres = ($type_autorite ==72) ? 'selected' : " ";
			
			$libelleBtn = $msg[207];
			if ($type_autorite ==7 ||$type_autorite ==70)
				$libelleBtn = $msg[207];
			elseif ($type_autorite ==71)
				$libelleBtn = $msg["aut_ajout_collectivite"];
			elseif ($type_autorite ==72)
				$libelleBtn = $msg["aut_ajout_congres"];
			
			$libelleRech = $msg[133];
			if ($type_autorite ==7 ||$type_autorite ==70)
				$libelleRech = $msg[133];
			elseif ($type_autorite ==71)
				$libelleRech = $msg[204];
			elseif ($type_autorite ==72)
				$libelleRech = $msg["congres_libelle"];
			
			$sel_autorite_auteur = '<select class="saisie-30em" id="id_autorite" name="type_autorite">';
			$sel_autorite_auteur .= "<option value ='7' $sel_tout>" .$msg["autorites_auteurs_all"] ."</option>";
			$sel_autorite_auteur .= "<option value='70'$sel_pp>$msg[203]</option>";
			$sel_autorite_auteur .= "<option value='71'$sel_coll>$msg[204]</option>";
			$sel_autorite_auteur .= "<option value='72'$sel_congres>" .$msg["congres_libelle"] ."</option>";
			$sel_autorite_auteur .= "</select>";
			
			$user_query = str_replace("<!-- sel_autorites -->", $sel_autorite_auteur, $user_query);
		    $user_query = str_replace("<!-- sel_authority_statuts -->", authorities_statuts::get_form_for(AUT_TABLE_AUTHORS, $authority_statut, true), $user_query);
			
			$user_query = str_replace('!!user_query_title!!', $msg[357] ." : " .$libelleRech, $user_query);
			$user_query = str_replace('!!action!!', static::format_url('&sub=reach&id='), $user_query);
			$user_query = str_replace('!!add_auth_msg!!', $libelleBtn, $user_query);
			$user_query = str_replace('!!add_auth_act!!', static::format_url('&sub=author_form&type_autorite=' .$type_autorite), $user_query);
			$user_query = str_replace('<!-- lien_derniers -->', "<a href='".static::format_url('&sub=last')."'>$msg[1310]</a>", $user_query);
			$user_query = str_replace("!!user_input!!", htmlentities(stripslashes($user_input), ENT_QUOTES, $charset), $user_query);
			print pmb_bidi($user_query);
		}
		// ---------------------------------------------------------------
		// update_index($id) : maj des index
		// ---------------------------------------------------------------
		public static function update_index($id, $datatype = 'all') {
			indexation_stack::push($id, TYPE_AUTHOR, $datatype);
			
			// On cherche tous les n-uplet de la table notice correspondant � cet auteur.
			$query = "select distinct responsability_notice as notice_id from responsability where responsability_author='" .$id ."'";
			authority::update_records_index($query, 'author');
			
			// On met � jour les titres uniformes correspondant � cet auteur
			$found = pmb_mysql_query("select distinct responsability_tu_num from responsability_tu where responsability_tu_author_num='" .$id ."'");
			// Pour chaque n-uplet trouv�s on met a jour l'index du titre uniforme avec l'auteur modifi� :
			$tu_ids = array();
			while ( ($mesTu = pmb_mysql_fetch_object($found)) ) {
				$tu_ids[] = $mesTu->responsability_tu_num;
			}
			if(count($tu_ids)) {
				foreach ($tu_ids as $tu_id) {
					titre_uniforme::update_index_tu($tu_id);
					titre_uniforme::update_index($tu_id, 'author');
				}
			}
		}

		public static function get_informations_from_unimarc($fields, $zone, $type, $field = "") {
			$data = array();
			// zone 200
			if ($zone =="2") {
				switch ($type) {
					case 70 :
						if (!$field) {
							$field = $zone ."00";
						}
						$data['type'] = 70;
						$data['name'] = $fields[$field][0]['a'][0];
						$data['rejete'] = (isset($fields[$field][0]['b'][0]) ? $fields[$field][0]['b'][0] : '');
						$data['date'] = (isset($fields[$field][0]['f'][0]) ? $fields[$field][0]['f'][0] : '');
						$data['subdivision'] = "";
						$data['lieu'] = "";
						$data['ville'] = "";
						$data['pays'] = "";
						$data['numero'] = "";
						break;
					case 71 :
						if (! $field) {
							$field = $zone ."10";
						}
						if (substr($fields[$field][0]['IND'], 0, 1) ==1) {
							$data['type'] = 72;
						} else {
							$data['type'] = 71;
						}
						$data['name'] = $fields[$field][0]['a'][0] .(isset($fields[$field][0]['c']) && (count($fields[$field][0]['c']) !=0) ? " (" .implode(", ", $fields[$field][0]['c']) .")" : "");
						$data['rejete'] = (isset($fields[$field][0]['g'][0]) ? $fields[$field][0]['g'][0] : '');
						$data['date'] = (isset($fields[$field][0]['f'][0]) ? $fields[$field][0]['f'][0] : '');
						if (isset($fields[$field][0]['b']) && count($fields[$field][0]['b'])) {
							$data['subdivision'] = implode(". ", $fields[$field][0]['b']);
						} else {
							$data['subdivision'] = "";
						}
						$data['lieu'] = (isset($fields[$field][0]['e'][0]) ? $fields[$field][0]['e'][0] : '');
						$data['ville'] = "";
						$data['pays'] = "";
						$data['numero'] = (isset($fields[$field][0]['d'][0]) ? $fields[$field][0]['d'][0] : '');
						break;
				}
				$data['author_comment'] = "";
				for($i = 0; $i <count($fields['300']); $i ++) {
					for($j = 0; $j <count($fields['300'][$i]['a']); $j ++) {
						if ($data['author_comment'] != "") {
							$data['author_comment'] .= "\n";
						}
						$data['author_comment'] .= $fields['300'][$i]['a'][$j];
					}
				}
				$data['author_web'] = $fields['856'][0]['u'][0];
			} else {
				// zone 400 / 500 / 700
				$data['authority_number'] = $fields['3'][0];
				switch ($type) {
					case 70 :
						$data['type'] = 70;
						$data['name'] = $fields['a'][0];
						$data['rejete'] = (isset($fields['b'][0]) ? $fields['b'][0] : '');
						$data['date'] = (isset($fields['f'][0]) ? $fields['f'][0] : '');
						$data['subdivision'] = "";
						$data['lieu'] = "";
						$data['ville'] = "";
						$data['pays'] = "";
						$data['numero'] = "";
						break;
					case 71 :
						if (substr($fields['IND'], 0, 1) ==1) {
							$data['type'] = 72;
						} else {
							$data['type'] = 71;
						}
						$data['name'] = $fields['a'][0] .(isset($fields['c']) && (count($fields['c']) !=0) ? " (" .implode(", ", $fields['c']) .")" : "");
						$data['rejete'] = (isset($fields['g'][0]) ? $fields['g'][0] : '');
						$data['date'] = (isset($fields['f'][0]) ? $fields['f'][0] : '');
						if (isset($fields['b']) && count($fields['b'])) {
							$data['subdivision'] = implode(". ", $fields['b']);
						} else {
							$data['subdivision'] = "";
						}
						$data['lieu'] = (isset($fields['e'][0]) ? $fields['e'][0] : '');
						$data['ville'] = "";
						$data['pays'] = "";
						$data['numero'] = (isset($fields['d'][0]) ? $fields['d'][0] : '');
						break;
				}
			}
			$data['type_authority'] = "author";
			return $data;
		}

		public static function check_if_exists($data) {
		    global $pmb_controle_doublons_diacrit;
		    
		    if ((empty($data) && !is_array($data)) || !is_array($data)) {
		        // si ce n'est pas un tableau ou un tableau vide, on retourne 0
				return 0;
			}
			// check sur les �l�ments du tableau (data['name'] ou data['rejete'] est requis).
			if(!isset(static::$long_maxi_name)) {
				static::$long_maxi_name = pmb_mysql_field_len(pmb_mysql_query("SELECT author_name FROM authors limit 1"), 0);
			}
			if(!isset(static::$long_maxi_rejete)) {
				static::$long_maxi_rejete = pmb_mysql_field_len(pmb_mysql_query("SELECT author_rejete FROM authors limit 1"), 0);
			}
			
			$data['name'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['name']))), 0, static::$long_maxi_name));
			$data['rejete'] = rtrim(substr(preg_replace('/\[|\]/', '', rtrim(ltrim($data['rejete']))), 0, static::$long_maxi_rejete));
			
			if (! $data['name'] &&! $data['rejete'])
				return 0;
				
				// check sur le type d'autorit�
			if (! $data['type'] ==70 &&! $data['type'] ==71 &&! $data['type'] ==72)
				return 0;
				
				// tentative de r�cup�rer l'id associ�e dans la base (implique que l'autorit� existe)
				
			// pr�paration de la requ�te
			$key0 = $data['type'];
			$key1 = addslashes($data['name']);
			$key2 = addslashes($data['rejete']);
			$key3 = addslashes($data['date']);
			$key4 = addslashes($data['subdivision']);
			$key5 = addslashes($data['lieu']);
			$key6 = addslashes($data['ville']);
			$key7 = addslashes($data['pays']);
			$key8 = addslashes($data['numero']);
			
			// Le lieu correspon � "ville; Pays"
			if ($key5 == $key6."; ".$key7) {
			    // Ne pas le prendre en compte pour le test de doublon
			    $key5 = "";
			}
			
			$data['lieu'] = addslashes($data['lieu']);
			$data['ville'] = addslashes($data['ville']);
			$data['pays'] = addslashes($data['pays']);
			$data['subdivision'] = addslashes($data['subdivision']);
			$data['numero'] = addslashes($data['numero']);
			$data['author_comment'] = addslashes($data['author_comment']);
			$data['author_web'] = addslashes($data['author_web']);			
			$data['author_isni'] = addslashes($data['author_isni']);
			
			$binary = '';
			if ($pmb_controle_doublons_diacrit) {
			    $binary = 'BINARY';
			} 
			$base_query = "SELECT author_id FROM authors WHERE author_type='${key0}' AND " . $binary . " author_name='${key1}' AND " . $binary . " author_rejete='${key2}' AND author_date='${key3}'";
			if ($data["type"] >70) {
				$query .= " and author_subdivision='${key4}' and author_lieu='${key5}' and author_ville='${key6}' and author_pays='${key7}' and author_numero='${key8}'";
			}
			$query = $base_query." LIMIT 1";
			$result = pmb_mysql_query($query);
			if (! $result)
				die("can't SELECT in database");
				// r�sultat
				
			if(pmb_mysql_num_rows($result)) {
				// r�cup�ration du r�sultat de la recherche
				$aut = pmb_mysql_fetch_object($result);
				// du r�sultat et r�cup�ration �ventuelle de l'id
				
				/*
				 * Publication d'un �v�nement sur le d�doublonnage d'un auteur
				 * Permet d'ajouter des crit�res de v�rification dans les plugins clients
				 * Si un message d'erreur est pr�sent sur l'instance du plugin
				 *  -> L'auteur est un doublon
				 * Sinon 
				 *  -> On retourne 0
				 */
				
				if ($aut->author_id){
				    $evt_handler = events_handler::get_instance();
				    $event = new event_author_deduplication("author", "check_if_exist");
				    $event->set_author_query($base_query);
				    $evt_handler->send($event);				    
				    if($evt_handler->get_hooks()) {
				        return $event->get_id_author() ? $event->get_id_author() : 0;
				    }
				    return $aut->author_id;
				}
					
			}
			return 0;
		}
		
		public function get_id_bnf($id) {
			// autre moyen de r�cuperer authority_number?
			// ---------------------------------------------------------------
			// verification de l'id bnf dans la base
			// ---------------------------------------------------------------
			
			$id_bnf = "";
			$query = "SELECT authority_number from authorities_sources WHERE num_authority='$id' ";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				$id_bnf = pmb_mysql_result($result, 0, 0);
			}
			return $id_bnf;
		}
		
		public static function delete_enrichment($id) {
			// to Do
		}
		
		public static function author_enrichment($id) {
			global $opac_enrichment_bnf_sparql;
			global $lang;
			global $charset;
			
			if ($opac_enrichment_bnf_sparql) {
				$enrichment = array();
				// definition des endpoints databnf et dbpedia
				$configbnf = array(
						'remote_store_endpoint' => 'http://data.bnf.fr/sparql'
				);
				$storebnf = ARC2::getRemoteStore($configbnf);
				$configdbp = array(
						'remote_store_endpoint' => 'http://dbpedia.org/sparql'
				);
				$storedbp = ARC2::getRemoteStore($configdbp);
				// verifier la date de author_enrichment_last_update => if(self)
				$aut_id_bnf = self::get_id_bnf($id);
				
				// si l'auteur est dans la base on r�cup�re son uri bnf...
				if ($aut_id_bnf !="") {
					
					$sparql = "
						PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
						PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
						PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
						SELECT distinct ?author WHERE {
						?author rdf:type skos:Concept .
						?author bnf-onto:FRBNF $aut_id_bnf
					}";
					
					$rows = $storebnf->query($sparql, 'rows');
					// On v�rifie qu'il n'y a pas d'erreur sinon on stoppe le programme et on renvoi une chaine vide
					$err = $storebnf->getErrors();
					if ($err) {
						return;
					}
				}
				
				// definition de l'uri bnf
				if ($rows[0]["author"]) {
					$uri_bnf = $rows[0]["author"];
					$enrichment['links']['uri_bnf'] = $uri_bnf;
					// ... ainsi que son uri dbpedia si elle existe
					$sparql = "
						PREFIX rdagroup2elements: <http://rdvocab.info/ElementsGr2/>
						PREFIX owl:<http://www.w3.org/2002/07/owl#>
						PREFIX foaf: <http://xmlns.com/foaf/0.1/>
						SELECT  ?dbpedia WHERE{
						<$uri_bnf> foaf:focus ?author.
						OPTIONAL {?author owl:sameAs ?dbpedia.
							FILTER regex(str(?dbpedia), 'http://dbpedia', 'i')}.
					}";
					try {
						$rows = $storebnf->query($sparql, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					
					if ($rows[0]["dbpedia"]) {
						$sub_dbp_uri = substr($rows[0]["dbpedia"], 28);
						$uri_dbpedia = "http://dbpedia.org/resource/" .rawurlencode($sub_dbp_uri);
						$enrichment['links']['uri_dbpedia'] = $uri_dbpedia;
					}
				}
				
				// debut de la requete d'enrichissement
				if ($uri_bnf !="") {
					// recuperation des infos biographiques bnf
					$sparql = "
						PREFIX foaf: <http://xmlns.com/foaf/0.1/>
						PREFIX rdagroup2elements: <http://rdvocab.info/ElementsGr2/>
						PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
						PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
						PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
						SELECT * WHERE {
							<$uri_bnf> foaf:focus ?person .
							<$uri_bnf> skos:prefLabel ?isbd .
							?person foaf:page ?page .
							OPTIONAL {
								?person rdagroup2elements:biographicalInformation ?biography
							}.
							OPTIONAL {
								?person rdagroup2elements:dateOfBirth ?birthdate.
								?birthdate rdfs:label ?birth.
							}
							OPTIONAL {?person bnf-onto:firstYear ?birthfirst.}
							OPTIONAL {?person rdagroup2elements:placeOfBirth ?birthplace .}
							OPTIONAL {
								?person rdagroup2elements:dateOfDeath ?deathdate .
								?deathdate rdfs:label ?death.
							}
							OPTIONAL {?person rdagroup2elements:placeOfDeath ?deathplace .}
						}";
					try {
						$rows = $storebnf->query($sparql, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					if ($rows[0]['birth'])
						$birthdate = $rows[0]['birth'];
					else {
						if ($rows[0]['birthfirst'])
							$birthdate = $rows[0]['birthfirst'];
						else
							$birthdate = "";
					}
					
					$enrichment['bio'] = array(
							'isbd' => $rows[0]['isbd'],
							'biography_bnf' => $rows[0]['biography'],
							'birthdate' => $birthdate,
							'birthplace' => $rows[0]['birthplace'],
							'deathdate' => $rows[0]['death'],
							'deathplace' => $rows[0]['deathplace']
					);
					// fin bio bnf
					
					// vignettes bnf
					$sparql = "
							PREFIX foaf: <http://xmlns.com/foaf/0.1/>
							PREFIX dc: <http://purl.org/dc/elements/1.1/>
							PREFIX dcterm: <http://purl.org/dc/terms/>
							SELECT * WHERE {
								<$uri_bnf> foaf:focus ?person .
								?person foaf:depiction ?url .
							}";
					try {
						$rows = $storebnf->query($sparql, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					
					$depictions = array();
					foreach ( $rows as $row ) {
						$depictions[] = $row['url'];
					}
					$enrichment['depictions']['depictions_bnf'] = $depictions;
					
					// biblio bnf
					$sparql = "
							PREFIX foaf: <http://xmlns.com/foaf/0.1/>
							PREFIX dcterms: <http://purl.org/dc/terms/>
							PREFIX rdarelationships: <http://rdvocab.info/RDARelationshipsWEMI/>
							SELECT ?work ?date ?dates ?work_concept  ?title MIN(?minUrl) AS ?url MIN(?minGallica) AS ?gallica WHERE {
								<$uri_bnf> foaf:focus ?person .
								?work dcterms:creator ?person .
								OPTIONAL { ?work dcterms:date ?date } .
								OPTIONAL { ?work <http://rdvocab.info/Elements/dateOfWork> ?dates } .
								?work_concept foaf:focus ?work .
								?work dcterms:title ?title .
								OPTIONAL{?work foaf:depiction ?minUrl .}
								OPTIONAL{
									?manifestation rdarelationships:workManifested ?work .
									?manifestation rdarelationships:electronicReproduction ?minGallica .
								}
							}  order by ?dates";
					
					try {
						$rows = $storebnf->query($sparql, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					if ($rows[0]['work']) {
						$aut_works = array();
						foreach ( $rows as $row ) {
							
							$tab_isbn = array();
							$sparql ="
								PREFIX rdarelationships: <http://rdvocab.info/RDARelationshipsWEMI/>
								PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
								SELECT distinct ?isbn WHERE {
									?manifestation rdarelationships:workManifested <".$row['work'].">.
									?manifestation bnf-onto:isbn ?isbn
								}order by ?isbn";
							try {
								$isbns = $storebnf->query ( $sparql, 'rows' );
							} catch ( Exception $e ) {
								$isbns = array ();
							}		
							foreach ($isbns as $isbn){
								$isbn['isbn']=formatISBN($isbn['isbn']);
								$tab_isbn[] = "'".$isbn['isbn']."'";
							}
							$aut_works[] = array(
									'title' => $row['title'],
									'uri_work' => $row['work'],
									'date' => $row['date'],
									'work_concept' => $row['work_concept'],
									'url' => $row['url'],
									'gallica' => $row['gallica'],
									'tab_isbn' => $tab_isbn
							);
						
						}
						$enrichment['biblio'] = $aut_works;
					}
				}
				
				// si uri dbpedia on recherche la bio dbpedia et l'image
				if ($uri_dbpedia !="") {
					$langue = substr($lang, 0, 2);
					$sparqldbp = "
						PREFIX dbpedia-owl:<http://dbpedia.org/ontology/>
						SELECT  ?comment ?image WHERE{
							<$uri_dbpedia> dbpedia-owl:abstract ?comment FILTER langMatches( lang(?comment), '" .$langue ."' ).
							OPTIONAL {<$uri_dbpedia> dbpedia-owl:thumbnail ?image} .
						}";
					try {
						$rows = $storedbp->query($sparqldbp, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					

					$enrichment['bio']['biography_dbpedia'] = encoding_normalize::clean_cp1252($rows[0]['comment'], "utf-8");
					if ($rows[0]['image'])
						$enrichment['depictions']['depiction_dbpedia'] = $rows[0]['image'];
						
						// recherche du mouvement litteraire ...
					$sparqldbp = "
						PREFIX dbpedia-owl:<http://dbpedia.org/ontology/>
						PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
						SELECT ?movement ?mov WHERE{
							<$uri_dbpedia> dbpedia-owl:movement ?mov.
							?mov rdfs:label ?movement
								FILTER langMatches( lang(?movement), '$langue').
						}";
					try {
						$rows = $storedbp->query($sparqldbp, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					
					foreach ( $rows as $row ) {
						$movement = array();
						$list_aut = array();
						$movement['title'] = $row['movement'];
						
						$sparqldbp = "
							PREFIX dbpedia-owl:<http://dbpedia.org/ontology/>
							PREFIX foaf: <http://xmlns.com/foaf/0.1/>
							SELECT distinct ?auts WHERE{
								?auts ?p <" .$row['mov'] .">.
									FILTER( ?p = dbpedia-owl:genre || ?p = dbpedia-owl:movement)
								?auts rdf:type foaf:Person
							}";
						try {
							$rows = $storedbp->query($sparqldbp, 'rows');
						} catch ( Exception $e ) {
							$rows = array();
						}
						
						foreach ( $rows as $row ) {
							if ($row['auts'] !=$uri_dbpedia) {
								$list_aut[] = rawurldecode($row['auts']);
							}
						}
						$list_aut = array_unique($list_aut);
						foreach ( array_chunk($list_aut, 10) as $chunk ) {
							
							$sparql = "
									PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
									PREFIX foaf: <http://xmlns.com/foaf/0.1/>
									PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
									PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
									SELECT ?numaut ?name WHERE{
										?aut rdf:type foaf:Person .
							    		?author foaf:focus ?aut.
							    		?author skos:exactMatch ?uri_dbpedia.
											FILTER (?uri_dbpedia = <" .implode("> || ?uri_dbpedia = <", $chunk) .">) 
										?author bnf-onto:FRBNF ?numaut.
										?aut foaf:name ?name.
									}";
							
							try {
								$rows = $storebnf->query($sparql, 'rows');
							} catch ( Exception $e ) {
								$rows = array();
							}
							
							foreach ( $rows as $row ) {
								
								$aauthor = Array(
										"id_bnf" => $row['numaut'],
										"name" => $row['name']
								);
								
								$movement['authors'][] = $aauthor;
							}
						}
						$enrichment['movement'][] = $movement;
					}
					// ... et du genre
					$sparqldbp = "
						PREFIX dbpedia-owl:<http://dbpedia.org/ontology/>
						PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
						SELECT ?genre ?mov WHERE{
							<$uri_dbpedia> dbpedia-owl:genre ?mov.
							?mov rdfs:label ?genre
								FILTER langMatches( lang(?genre), '$langue').
						}";
					try {
						$rows = $storedbp->query($sparqldbp, 'rows');
					} catch ( Exception $e ) {
						$rows = array();
					}
					
					foreach ( $rows as $row ) {
						$genre = array();
						$list_aut = array();
						$genre['title'] = $row['genre'];
						
						$sparqldbp = "
							PREFIX dbpedia-owl:<http://dbpedia.org/ontology/>
							PREFIX foaf: <http://xmlns.com/foaf/0.1/>
							SELECT distinct ?auts WHERE{
								?auts ?p <" .$row['mov'] .">.
									FILTER( ?p = dbpedia-owl:genre || ?p = dbpedia-owl:genre)
								?auts rdf:type foaf:Person
							}";
						try {
							$rows = $storedbp->query($sparqldbp, 'rows');
						} catch ( Exception $e ) {
							$rows = array();
						}
						
						foreach ( $rows as $row ) {
							if ($row['auts'] !=$uri_dbpedia) {
								$list_aut[] = rawurldecode($row['auts']);
							}
						}
						$list_aut = array_unique($list_aut);
						foreach ( array_chunk($list_aut, 10) as $chunk ) {
							
							$sparql = "
									PREFIX bnf-onto: <http://data.bnf.fr/ontology/bnf-onto/>
									PREFIX foaf: <http://xmlns.com/foaf/0.1/>
									PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
									PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
									SELECT ?numaut ?name WHERE{
										?aut rdf:type foaf:Person .
							    		?author foaf:focus ?aut.
							    		?author skos:exactMatch ?uri_dbpedia.
											FILTER (?uri_dbpedia = <" .implode("> || ?uri_dbpedia = <", $chunk) .">) 
										?author bnf-onto:FRBNF ?numaut.
										?aut foaf:name ?name.
									}";
							
							try {
								$rows = $storebnf->query($sparql, 'rows');
							} catch ( Exception $e ) {
								$rows = array();
							}
							
							foreach ( $rows as $row ) {
								
								$aauthor = Array(
										"id_bnf" => $row['numaut'],
										"name" => $row['name']
								);
								
								$genre['authors'][] = $aauthor;
							}
						}
						$enrichment['genre'][] = $genre;
					}
				}
				
				if ($charset !='utf-8'){
					$enrichment = pmb_utf8_array_decode($enrichment);
					
					
				}
				$enrichments = serialize($enrichment);
				$enrichments = addslashes($enrichments);
				
				$query = "UPDATE authors SET author_enrichment = '" .$enrichments ."', author_enrichment_last_update = NOW() WHERE author_id='" .$id ."'";
				pmb_mysql_query($query);
				// update
			}
		}
		
		public function get_header() {
			return $this->display;
		}
		
		public function get_cp_error_message(){
			return $this->cp_error_message;
		}
		
		public function get_gestion_link(){
			return './autorites.php?categ=see&sub=author&id='.$this->id;
		}
		
		public function get_isbd() {
			return $this->isbd_entry;
		}
		
		public static function get_format_data_structure($antiloop = false) {
			global $msg;
			
			$main_fields = array();
			$main_fields[] = array(
					'var' => "type",
					'desc' => $msg['205']
			);
			$main_fields[] = array(
					'var' => "name",
					'desc' => $msg['201']
			);
			$main_fields[] = array(
					'var' => "rejete",
					'desc' => $msg['202']
			);
			$main_fields[] = array(
					'var' => "date",
					'desc' => $msg['713']
			);
			$main_fields[] = array(
					'var' => "lieu",
					'desc' => $msg['congres_lieu_libelle']
			);
			$main_fields[] = array(
					'var' => "ville",
					'desc' => $msg['congres_ville_libelle']
			);
			$main_fields[] = array(
					'var' => "pays",
					'desc' => $msg['congres_pays_libelle']
			);
			$main_fields[] = array(
					'var' => "subdivision",
					'desc' => $msg['congres_subdivision_libelle']
			);
			$main_fields[] = array(
					'var' => "numero",
					'desc' => $msg['congres_numero_libelle']
			);
			if(!$antiloop) {
				$main_fields[] = array(
					'var' => "see",
					'desc' => $msg['206'],
					'children' => authority::prefix_var_tree(auteur::get_format_data_structure(true),"see")
				);
			}
			$main_fields[] = array(
					'var' => "web",
					'desc' => $msg['147']
			);
			$main_fields[] = array(
					'var' => "comment",
					'desc' => $msg['author_comment']
			);
			$authority = new authority(0, 0, AUT_TABLE_AUTHORS);
			$main_fields = array_merge($authority->get_format_data_structure(), $main_fields);
			return $main_fields;
		}
		
		public function format_datas($antiloop = false){
			$see_datas = array();
			if(!$antiloop) {
				if($this->see) {
					$see = new auteur($this->see);
					$see_datas = $see->format_datas(true);
				}
			}
			$formatted_data = array(
					'type' => $this->type,
					'name' => $this->name,
					'rejete' => $this->rejete,
					'date' => $this->date,
					'lieu' => $this->lieu,
					'ville' => $this->ville,
					'pays' => $this->pays,
					'subdivision' => $this->subdivision,
					'numero' => $this->numero,
					'see' => $see_datas,
			        'web' => $this->author_web,
			        'isni' => $this->author_isni,
					'comment' => $this->author_comment
			);
			$authority = new authority(0, $this->id, AUT_TABLE_AUTHORS);
			$formatted_data = array_merge($authority->format_datas(), $formatted_data);
			return $formatted_data;
		}
		
		public static function set_controller($controller) {
			static::$controller = $controller;
		}
		
		protected static function format_url($url='') {
			global $base_path;
			
			if(isset(static::$controller) && is_object(static::$controller)) {
				return 	static::$controller->get_url_base().$url;
			} else {
				return $base_path.'/autorites.php?categ=auteurs'.$url;
			}
		}
		
		protected static function format_back_url() {
			if(isset(static::$controller) && is_object(static::$controller)) {
				return 	static::$controller->get_back_url();
			} else {
				return "history.go(-1)";
			}
		}
		
		protected static function format_delete_url($url='') {
			if(isset(static::$controller) && is_object(static::$controller)) {
				return 	static::$controller->get_delete_url();
			} else {
				return static::format_url("&sub=delete".$url);
			}
		}
		
		protected function warning_already_exist($error_title, $error_message, $values=array())  {
			global $msg;
			
			$authority = new authority(0, $this->id, AUT_TABLE_AUTHORS);
			$display = $authority->get_display_authority_already_exist($error_title, $error_message, $values);
		    $display = str_replace("!!action!!", static::format_url('&sub=update&id='.$this->id.'&forcing=1'), $display);
		    $label = (empty($this->id) ? $msg[287] : $msg['force_modification']);
		    $display = str_replace("!!forcing_button!!", $authority->get_display_forcing_button($label) , $display);
		    $hidden_specific_values = $authority->put_global_in_hidden_field('concept');
		    $hidden_specific_values .= $authority->put_global_in_hidden_field('tab_concept_order');
		    $display = str_replace('!!hidden_specific_values!!', $hidden_specific_values, $display);
		    return $display;
		}
		
		public function get_concepts(){
		    $index_concept = new index_concept($this->id, TYPE_AUTHOR);
		    return $index_concept->get_concepts();
		}
	} // class auteur
}

