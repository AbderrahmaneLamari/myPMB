<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_loans_edition_ui.class.php,v 1.8.2.1 2021/09/07 09:14:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/list/loans/list_loans_edition_ui.tpl.php");
require_once($class_path."/emprunteur.class.php");

class list_loans_edition_ui extends list_loans_ui {
	
	protected function get_title() {
		return "<div class='row'><p class='message'>".$this->get_display_late()."</p></div>";
	}
	
	protected function get_form_title() {
		global $msg;
		global $sub;
		
		$form_title = '';
		switch($sub) {
			case "retard" :
				$form_title .= $msg[1112];
				break;
			case "retard_par_date" :
				$form_title .= $msg['edit_expl_retard_par_date'];
				break;
			case 'short_loans' :
				$form_title .= $msg['current_short_loans'];
				break;
			case 'unreturned_short_loans' :
				$form_title .= $msg['unreturned_short_loans'];
				break;
			case 'overdue_short_loans' :
				$form_title .= $msg['overdue_short_loans'];
				break;
			default :
			case "encours" :
				$form_title .= $msg[1111];
				break;
		}
		return $form_title;
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		global $base_path;
		global $pmb_gestion_amende, $pmb_gestion_financiere;
		global $sub;
		
		parent::init_default_selection_actions();
		if($pmb_gestion_amende==0 || $pmb_gestion_financiere==0) {
			if($sub == 'pargroupe') {
				$relance_link = array(
						'openPopUp' => $base_path."/pdf.php?pdfdoc=lettre_retard_groupe",
						'openPopUpTitle' => 'lettre'
				);
				$this->add_selection_action('relance_groupe', $msg['lettres_relance_groupe'], 'print.gif', $relance_link);
			}
			if($sub == 'retard' || $sub == 'retard_par_date') {
				$relance_link = array(
						'href' => static::get_controller_url_base()."&action=print"
				);
				$this->add_selection_action('relance', $msg['lettres_relance'], 'print.gif', $relance_link);
			}
		}
	}
	
	protected function init_default_columns() {
		global $pmb_gestion_amende, $pmb_gestion_financiere;
		global $sub;
	
		if ($pmb_gestion_amende==0 || $pmb_gestion_financiere==0) {
			if($sub == 'retard' || $sub == 'retard_par_date' || $sub == 'pargroupe') {
				$this->add_column_selection();
			}
		}
		$this->add_column('cb_expl');
		$this->add_column('cote');
		$this->add_column('typdoc');
		$this->add_column('record');
		$this->add_column('author');
		$this->add_column('empr');
		$this->add_column('pret_date');
		$this->add_column('pret_retour');
		$this->add_column('late_letter', '369', '', false);
	}
	
	protected function get_sub_title() {
		global $sub, $msg;
		switch($sub) {
			case 'retard':
				return $msg['1112'];
				break;
			case 'retard_par_date':
				return $msg['edit_expl_retard_par_date'];
				break;
			case 'short_loans':
				return $msg['current_short_loans'];
				break;
			case 'unreturned_short_loans':
				return $msg['unreturned_short_loans'];
				break;
			case 'overdue_short_loans':
				return $msg['overdue_short_loans'];
				break;
			case 'encours':
			default :
				return $msg['1111'];
				break;
		}
	}
	
	protected function get_display_spreadsheet_title() {
		global $sub, $msg;
		switch ($sub) {
			case 'short_loans' :
			case 'unreturned_short_loans' :
			case 'overdue_short_loans' :
				$this->spreadsheet->write_string(0,0,$this->get_sub_title());
				break;
			default :
				$this->spreadsheet->write_string(0,0,$msg[1110]." : ".$this->get_sub_title());
				break;
		}
	}
	
	protected function get_html_title() {
		global $sub, $msg;
		switch ($sub) {
			case 'short_loans' :
			case 'unreturned_short_loans' :
			case 'overdue_short_loans' :
				return "<h1>".$this->get_sub_title()."</h1>";
			default :
				return "<h1>".$msg[1110]." : ".$this->get_sub_title()."</h1>";
				break;
		}
	}
	
	protected function get_evaluated_parameter($parameter_name) {
		global $biblio_name, $biblio_phone, $biblio_email, $biblio_commentaire, $biblio_website;
		global $biblio_adr1, $biblio_adr2, $biblio_cp, $biblio_town;
		
		global ${$parameter_name};
		eval ("\$evaluated=\"".addslashes(${$parameter_name})."\";");
		return stripslashes($evaluated);
	}
	
	public function print_relances() {
		global $msg, $charset;
		global $mailretard_priorite_email;
		global $PMBuseremailbcc, $biblio_name, $biblio_email;
		global $relance;
		global $pmb_lecteurs_localises;
		
		$not_all_mail = array();
		$mail_sended_id_empr = array();
		foreach ($this->objects as $object) {
			$mail_sended = 0;
			if ((($mailretard_priorite_email==1)||($mailretard_priorite_email==2))&&(emprunteur::get_mail_empr($object->id_empr))) {
				if ((!count($mail_sended_id_empr)) || (!in_array($object->id_empr,$mail_sended_id_empr))) {
					if (!$relance) $relance = 1;
					// l'objet du mail
					$objet = $this->get_evaluated_parameter("mailretard_".$relance."objet");
					
					// la formule de politesse du bas (le signataire)
					$fdp = $this->get_evaluated_parameter("mailretard_".$relance."fdp");
					
					// le texte après la liste des ouvrages en retard
					$after_list = $this->get_evaluated_parameter("mailretard_".$relance."after_list");
					
					// le texte avant la liste des ouvrges en retard
					$before_list = $this->get_evaluated_parameter("mailretard_".$relance."before_list");
					
					// le "Madame, Monsieur," ou tout autre truc du genre "Cher adhérent,"
					$madame_monsieur = $this->get_evaluated_parameter("mailretard_".$relance."madame_monsieur");
					
					$texte_mail='';
					if($madame_monsieur) $texte_mail.=$madame_monsieur."\r\n\r\n";
					if($before_list) $texte_mail.=$before_list."\r\n\r\n";
					
					//Récupération des exemplaires
					$rqt = "select expl_cb from pret, exemplaires where pret_idempr='".$object->id_empr."' and pret_retour < CURDATE() and pret_idexpl=expl_id order by pret_date " ;
					$req_cb = pmb_mysql_query($rqt);
					
					while ($data = pmb_mysql_fetch_array($req_cb)) {
					
						/* Récupération des infos exemplaires et prêt */
						$requete = "SELECT notices_m.notice_id as m_id, notices_s.notice_id as s_id, expl_cb, pret_date, pret_retour, tdoc_libelle, section_libelle, location_libelle, trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if (mention_date, concat(' (',mention_date,')') ,''))) as tit, ";
						$requete.= " date_format(pret_date, '".$msg["format_date"]."') as aff_pret_date, ";
						$requete.= " date_format(pret_retour, '".$msg["format_date"]."') as aff_pret_retour, ";
						$requete.= " IF(pret_retour>sysdate(),0,1) as retard, notices_m.tparent_id, notices_m.tnvol " ;
						$requete.= "FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), docs_type, docs_section, docs_location, pret ";
						$requete.= "WHERE expl_cb='".addslashes($data['expl_cb'])."' and expl_typdoc = idtyp_doc and expl_section = idsection and expl_location = idlocation and pret_idexpl = expl_id  ";
					
						$req = pmb_mysql_query($requete);
						$expl = pmb_mysql_fetch_object($req);
					
						$responsabilites = get_notice_authors(($expl->m_id+$expl->s_id)) ;
						$header_aut = gen_authors_header($responsabilites);
						$header_aut ? $auteur=" / ".$header_aut : $auteur="";
					
						// récupération du titre de série
						$tit_serie="";
						if ($expl->tparent_id && $expl->m_id) {
							$parent = new serie($expl->tparent_id);
							$tit_serie = $parent->name;
							if($expl->tnvol)
								$tit_serie .= ', '.$expl->tnvol;
						}
						if($tit_serie) {
							$expl->tit = $tit_serie.'. '.$expl->tit;
						}
					
						$texte_mail.=$expl->tit.$auteur."\r\n";
						$texte_mail.="    -".$msg['fpdf_date_pret']." ".$expl->aff_pret_date." ".$msg['fpdf_retour_prevu']." ".$expl->aff_pret_retour."\r\n";
						$texte_mail.="    -".$expl->location_libelle." : ".$expl->section_libelle." (".$expl->expl_cb.")\r\n\r\n\r\n";
					}
					$texte_mail.="\r\n";
					if($after_list) $texte_mail.=$after_list."\r\n\r\n";
					if($fdp) $texte_mail.=$fdp."\r\n\r\n";
					$texte_mail.=mail_bloc_adresse() ;
					
					//Si mail de rappel affecté au responsable du groupe
					$requete="select id_groupe,resp_groupe from groupe,empr_groupe where id_groupe=groupe_id and empr_id=".$object->id_empr." and resp_groupe and mail_rappel limit 1";
					$req=pmb_mysql_query($requete);
					/* Récupération du nom, prénom et mail du lecteur destinataire */
					if(pmb_mysql_num_rows($req) > 0) {
						$requete="select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr='".pmb_mysql_result($req, 0,1)."'";
						$result=pmb_mysql_query($requete);
						$coords_dest=pmb_mysql_fetch_object($result);
					} else {
						$requete="select id_empr, empr_mail, empr_nom, empr_prenom from empr where id_empr=".$object->id_empr;
						$result=pmb_mysql_query($requete);
						$coords_dest=pmb_mysql_fetch_object($result);
					}
					
					/* Récupération du nom, prénom et mail du lecteur concerné */
					$requete="select id_empr, empr_mail, empr_nom, empr_prenom, empr_cb from empr where id_empr=".$object->id_empr;
					$req=pmb_mysql_query($requete);
					$coords=pmb_mysql_fetch_object($req);
					
					//remplacement nom et prenom
					$texte_mail=str_replace("!!empr_name!!", $coords->empr_nom,$texte_mail);
					$texte_mail=str_replace("!!empr_first_name!!", $coords->empr_prenom,$texte_mail);
					
					$headers = "Content-type: text/plain; charset=".$charset."\n";
					
					$mail_sended=mailpmb($coords_dest->empr_prenom." ".$coords_dest->empr_nom, $coords_dest->empr_mail, $objet." : ".$coords->empr_prenom." ".mb_strtoupper($coords->empr_nom,$charset)." (".$coords->empr_cb.")",$texte_mail, $biblio_name, $biblio_email,$headers, "", $PMBuseremailbcc,1);
				} else {
					$mail_sended = 1;
				}
			}
			if (!$mail_sended) {
				$not_all_mail[] = $object->id_empr;
			} else {
				$mail_sended_id_empr[] = $object->id_empr;
			}
		}
		if (count($not_all_mail) > 0) {
			$restrict_localisation ="";
			if ($pmb_lecteurs_localises) {
				if ($this->filters['empr_location_id']!="") $restrict_localisation .= "&empr_location_id=".$this->filters['empr_location_id'];
				if ($this->filters['docs_location_id']!="") $restrict_localisation .= "&docs_location_id=".$this->filters['docs_location_id'];
			}
			print "<form name='print_empr_ids' action='pdf.php?pdfdoc=lettre_retard$restrict_localisation' target='lettre' method='post'>";
			for ($i=0; $i<count($not_all_mail); $i++) {
				print "<input type='hidden' name='empr_print[]' value='".$not_all_mail[$i]."'/>";
			}
			print "	<script>openPopUp('','lettre');
				document.print_empr_ids.submit();
				</script>
			</form>";
		}	
	}
}