<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_transferts_ui.class.php,v 1.25.2.6 2021/12/23 15:49:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path, $class_path, $include_path;
require_once($class_path.'/transfert.class.php');
require_once($include_path.'/templates/list/transferts/list_transferts_ui.tpl.php');
require_once ($class_path."/mono_display.class.php");
require_once ($class_path."/serial_display.class.php");
require_once ($class_path."/lender.class.php");
require_once ($class_path."/docs_statut.class.php");
require_once ($class_path."/docs_location.class.php");
require_once ($base_path."/circ/transferts/affichage.inc.php");

class list_transferts_ui extends list_ui {
	
	protected $cp;
	
	protected $displayed_cp;
	
	protected function _get_query_base() {
		$query = 'select id_transfert from transferts
			INNER JOIN transferts_demande ON id_transfert=num_transfert
			INNER JOIN exemplaires ON num_expl=expl_id
			INNER JOIN docs_section ON expl_section=idsection
			INNER JOIN docs_location AS locd ON num_location_dest=locd.idlocation
			INNER JOIN docs_location AS loco ON num_location_source=loco.idlocation
			INNER JOIN lenders ON expl_owner=idlender
			INNER JOIN docs_statut ON expl_statut=idstatut
            LEFT JOIN resa ON resa_trans=id_resa
			LEFT JOIN empr ON resa_idempr=id_empr
			LEFT JOIN pret ON pret_idexpl=num_expl';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new transfert($row->id_transfert);
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		global $deflt_docs_location;
		/**
		 * etat_transfert => (0 = non fini)
		 * etat_demande => (0 = non validée, 1 = validée, 2 = envoyée, 3 = aller fini, 4 = refus)
		 * type_transfert => (1 = aller-retour)
		 */
		$this->filters = array(
				'site_origine' => $deflt_docs_location,
				'site_destination' => 0,
				'f_etat_date' => '',
				'f_etat_dispo' => '',
				'etat_transfert' => -1,
				'etat_demande' => -1,
				'type_transfert' => '',
				'sens_transfert' => -1,
				'cb' => ''
		);
		parent::init_filters($filters);
	}
	
	protected function init_override_filters() {
		global $deflt_docs_location;
		
		$this->filters['site_origine'] = $deflt_docs_location;
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'id' => '1601',
					'record' => '233',
					'cb' => '232',
					'cote' => '296',
					'location' => '298',
					'statut' => '297',
					'empr' => 'transferts_circ_empr',
					'source' => 'transferts_circ_source',
					'destination' => 'transferts_circ_destination',
					'expl_owner' => '651',
					'formatted_date_creation' => 'transferts_circ_date_creation',
					'formatted_date_envoyee' => 'transferts_circ_date_envoi',
					'formatted_date_refus' => 'transferts_circ_date_refus',
					'formatted_date_reception' => 'transferts_circ_date_reception',
					'formatted_date_retour' => 'transferts_circ_date_retour',
					'motif' => 'transferts_circ_motif',
					'motif_refus' => 'transferts_circ_motif_refus',
					'transfert_ask_user_num' => 'transferts_edition_ask_user',
					'transfert_send_user_num' => 'transferts_edition_send_user',
			)
		);
		
		$this->available_columns['custom_fields'] = array();
		$this->add_custom_fields_available_columns('notices', 'num_notice');
		$this->add_custom_fields_available_columns('expl', 'num_exemplaire');
	}
	
	/**
	 * Initialisation de la pagination par défaut
	 */
	protected function init_default_pager() {
		global $transferts_tableau_nb_lignes;
		parent::init_default_pager();
		$this->pager['nb_per_page'] = $transferts_tableau_nb_lignes;
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('formatted_date_creation', 'desc');
	}
	
	/**
	 * Initialisation des settings par défaut
	 */
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'unfolded_filters', true);
		$this->set_setting_column('default', 'align', 'left');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'id':
					$order .= 'id_transfert';
					break;
				case 'record' :
					break;
				case 'section':
					$order .= 'section_libelle';
					break;
				case 'cote':
					$order .= 'expl_cote';
					break;
				case 'location':
					$order .= 'expl_location';
					break;
				case 'cb':
					$order .= 'expl_cb';
					break;
				case 'statut':
					$order .= 'statut_libelle';
					break;
				case 'empr':
					$order .= "concat(empr_nom,' ',empr_prenom)";
					break;
				case 'expl_owner':
					$order .= "lender_libelle";
					break;
				case 'source':
					$order .= "loco.location_libelle";
					break;
				case 'destination':
					$order .= "locd.location_libelle";
					break;
				case 'formatted_date_creation':
					if(static::class == 'list_transferts_demandes_ui') {
						$order .= "transferts_demande.date_creation";
					} else {
						$order .= "transferts.date_creation";
					}
					break;
				case 'formatted_date_reception':
					$order .= "date_reception";
					break;
				case 'formatted_date_envoyee':
					$order .= "date_envoyee";
					break;
				case 'formatted_date_refus':
					$order .= "date_visualisee";
					break;
				case 'motif_refus':
					$order .= "motif_refus";
					break;
				case 'transfert_ask_formatted_date':
					$order .= "transfert_ask_date";
					break;
				case 'formatted_bt_date_retour':
				case 'date_retour':
					$order .= "date_retour";
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order); 
			} else {
				return "";
			}
		}	
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('site_origine', 'integer');
		$this->set_filter_from_form('site_destination', 'integer');
		$this->set_filter_from_form('f_etat_date', 'integer');
		$this->set_filter_from_form('f_etat_dispo', 'integer');
		$numeros = '';
		foreach ($_REQUEST as $k => $v) {
			//si c'est une case a cocher d'une liste
			if ((substr($k,0,4)=="sel_") && ($v=="1")) {
				//le no de transfert
				$numeros .= substr($k,4,strlen($k)) . ",";
			}
		}
		$this->filters['ids'] = '';
		if($numeros) {
			//on enleve la derniere virgule
			$numeros =  substr($numeros, 0, strlen($numeros)-1);
			$this->filters['ids'] = $numeros;
		}
		$this->set_filter_from_form('cb');
		parent::set_filters_from_form();
	}
	
	protected function get_search_options_locations($loc_select,$tous = true) {
		global $msg;
	
		$options = '';
		$query = "SELECT idlocation, location_libelle FROM docs_location ORDER BY location_libelle ";
		$result = pmb_mysql_query($query);
		if ($tous) {
			$options .= "<option value='0'>".$msg["all_location"]."</option>";
		}
		while ($row = pmb_mysql_fetch_object($result)) {
			$options .= "<option value='".$row->idlocation."' ".($row->idlocation==$loc_select ? "selected='selected'" : "").">";
			$options .= $row->location_libelle."</option>";
		}
		return $options;
	}
	
	protected function get_search_filter_site_origine() {
		global $msg;
		$query = "SELECT idlocation as id, location_libelle as label FROM docs_location ORDER BY label";
		return $this->get_search_filter_simple_selection($query, 'site_origine', $msg["all_location"]);
	}
	
	protected function get_search_filter_site_destination() {
		global $msg;
		$query = "SELECT idlocation as id, location_libelle as label FROM docs_location ORDER BY label";
		return $this->get_search_filter_simple_selection($query, 'site_destination', $msg["all_location"]);
	}
	
	protected function get_search_filter_f_etat_date() {
		global $msg;
	
		$options = array(
				0 => $msg["transferts_circ_retour_filtre_etat_tous"],
				1 => $msg["transferts_circ_retour_filtre_etat_proche"],
				2 => $msg["transferts_circ_retour_filtre_etat_depasse"]
		);
		return $this->get_search_filter_simple_selection('', 'f_etat_date', '', $options);
	}
	
	protected function get_search_filter_f_etat_dispo() {
		global $msg;
	
		$options = array(
				1 => $msg["transferts_circ_retour_filtre_dispo"],
				2 => $msg["transferts_circ_retour_filtre_circ"],
				0 => $msg["transferts_circ_retour_filtre_etat_tous"]
		);
		return $this->get_search_filter_simple_selection('', 'f_etat_dispo', '', $options);
	}
	
	protected function get_search_filter_cb() {
		global $charset;
		return "<input type='text' name='".$this->objects_type."_cb' value='".htmlentities($this->filters['cb'], ENT_QUOTES, $charset)."' />"; 	
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		global $transferts_nb_jours_alerte;
		
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['site_origine']) {
			$filters [] = 'num_location_source = "'.$this->filters['site_origine'].'"';
		}
		if($this->filters['site_destination']) {
			$filters [] = 'num_location_dest = "'.$this->filters['site_destination'].'"';
		}
		if($this->filters['f_etat_date']) {
			switch ($this->filters['f_etat_date']) {
				case "1":
					$filters [] = "(DATEDIFF(DATE_ADD(date_retour,INTERVAL -" . $transferts_nb_jours_alerte . " DAY),CURDATE())<=0
							AND DATEDIFF(date_retour,CURDATE())>=0)";
					break;
				case "2":
					$filters [] = "DATEDIFF(date_retour,CURDATE())<0";
					break;
			}
		}
		if($this->filters['f_etat_dispo']) {
			switch ($this->filters['f_etat_dispo']) {
				case 1 : // pas en pret et non réservé
					$filters [] = "if(id_resa, resa_confirmee=0, 1) and if(pret_idexpl,0 ,1) ";
					break;
				case 2 : // en pret et réservé seulement
					$filters [] = "( if(id_resa, resa_confirmee=1, 0) OR if(pret_idexpl,1 ,0) ) ";
					break;
			}
		}
		if($this->filters['etat_transfert'] !== '' && $this->filters['etat_transfert'] !== -1) {
			$filters [] = 'etat_transfert = "'.$this->filters['etat_transfert'].'"';
		}
		if(is_array($this->filters['etat_demande'])) {
			$filters [] = 'etat_demande IN ('.implode(',', $this->filters['etat_demande']).')';
		} elseif($this->filters['etat_demande'] !== '' && $this->filters['etat_demande'] !== -1) {
			$filters [] = 'etat_demande = "'.$this->filters['etat_demande'].'"';
		}
		if($this->filters['type_transfert'] !== '' && $this->filters['type_transfert'] !== -1) {
			$filters [] = 'type_transfert = "'.$this->filters['type_transfert'].'"';
		}
		if($this->filters['ids']) {
			$filters [] = 'id_transfert IN ('.$this->filters['ids'].')';
		}
		if($this->filters['cb']) {
			$filters [] = 'exemplaires.expl_cb = "'.$this->filters['cb'].'"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	/**
	 * Fonction de callback
	 * @param object $a
	 * @param object $b
	 */
	protected function _compare_objects($a, $b) {
	    if($this->applied_sort[0]['by']) {
	        $sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'formatted_date_creation':
					return strcmp($a->get_date_creation(), $b->get_date_creation());
					break;
				case 'formatted_date_reception':
					return strcmp($a->get_transfert_demande()->get_date_reception(), $b->get_transfert_demande()->get_date_reception());
					break;
				case 'formatted_date_envoyee':
					return strcmp($a->get_transfert_demande()->get_date_envoyee(), $b->get_transfert_demande()->get_date_envoyee());
					break;
				case 'formatted_date_refus':
					return strcmp($a->get_transfert_demande()->get_date_visualisee(), $b->get_transfert_demande()->get_date_visualisee());
					break;
				case 'transfert_ask_formatted_date':
					return strcmp($a->get_transfert_ask_date(), $b->get_transfert_ask_date());
					break;
				case 'transfert_ask_user_num':
				case 'transfert_send_user_num':
					return strcmp(user::get_param(call_user_func_array(array($a, "get_".$sort_by), array()), 'username'), user::get_param(call_user_func_array(array($b, "get_".$sort_by), array()), 'username'));
					break;
				case 'transfert_bt_relancer':
					return '';
					break;
				case 'formatted_bt_date_retour':
					return strcmp($a->get_date_retour(), $b->get_date_retour());
					break;
				default :
					return parent::_compare_objects($a, $b);
					break;
			}
		}
	}
	
	protected function _get_object_property_record($object) {
		if($object->get_num_notice()) {
			return aff_titre($object->get_num_notice(), 0);
		} else {
			return aff_titre(0, $object->get_num_bulletin());
		}
	}
	
	protected function _get_object_property_section($object) {
		return $object->get_exemplaire()->section;
	}
	
	protected function _get_object_property_cote($object) {
		return $object->get_exemplaire()->cote;
	}
	
	protected function _get_object_property_location($object) {
		return $object->get_exemplaire()->location;
	}
	
	protected function _get_object_property_cb($object) {
		return $object->get_exemplaire()->cb;
	}
	
	protected function _get_object_property_statut($object) {
		$docs_statut = new docs_statut($object->get_exemplaire()->statut_id);
		return $docs_statut->libelle;
	}
	
	protected function _get_object_property_empr($object) {
		$id_resa = $object->get_transfert_demande()->get_resa_trans();
		if($id_resa) {
			$query = "select id_empr, empr_cb from empr join resa on id_empr = resa_idempr where id_resa = ".$id_resa;
			$result = pmb_mysql_query($query);
			if(pmb_mysql_num_rows($result) == 1) {
				$row = pmb_mysql_fetch_object($result);
				return emprunteur::get_name($row->id_empr);
			}
		}
		return '';
	}
	
	protected function _get_object_property_expl_owner($object) {
		$lender = new lender($object->get_exemplaire()->owner_id);
		return $lender->lender_libelle;
	}
	
	protected function _get_object_property_source($object) {
		$docs_location = new docs_location($object->get_transfert_demande()->get_num_location_source());
		return $docs_location->libelle;
	}
	
	protected function _get_object_property_destination($object) {
		$docs_location = new docs_location($object->get_transfert_demande()->get_num_location_dest());
		return $docs_location->libelle;
	}
	
	protected function _get_object_property_motif_refus($object) {
		return $object->get_transfert_demande()->get_motif_refus();
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		global $base_path;
		
		$content = '';
		
		$is_cp_property = false;
		if(isset($this->displayed_cp) && is_array($this->displayed_cp)) {
			$is_cp_property = array_search($property, $this->displayed_cp);
		}
		if($is_cp_property) {
			$this->cp->get_values($object->get_exemplaire()->expl_id);
			if(isset($this->cp->values[$is_cp_property])) {
				$values = $this->cp->values[$is_cp_property];
			} else {
				$values = array();
			}
			$aff_column=$this->cp->get_formatted_output($values, $is_cp_property);
			if (!$aff_column) $aff_column="&nbsp;";
			$content .= $aff_column;
		} else {
			switch($property) {
				case 'cb':
					$content .= aff_exemplaire($object->get_exemplaire()->{$property});
					break;
				case 'statut':
					$docs_statut = new docs_statut($object->get_exemplaire()->statut_id);
					$content .= aff_statut_exemplaire($docs_statut->libelle.'###'.$object->get_exemplaire()->expl_id);
					break;
				case 'empr':
					//TODO
					$id_resa = $object->get_transfert_demande()->get_resa_trans();
					if($id_resa) {
						$query = "select id_empr, empr_cb from empr join resa on id_empr = resa_idempr where id_resa = ".$id_resa;
						$result = pmb_mysql_query($query);
						if(pmb_mysql_num_rows($result) == 1) {
							$row = pmb_mysql_fetch_object($result);
							if (SESSrights & CIRCULATION_AUTH) {
								$content = "<a href='./circ.php?categ=pret&form_cb=".$row->empr_cb."'>";
								$content .= emprunteur::get_name($row->id_empr);
								$content .= "</a>";
							} else {
								$content .= emprunteur::get_name($row->id_empr);
							}
						}
					}
					break;
				case 'formatted_date_reception':
					$content .= $object->get_transfert_demande()->get_formatted_date_reception();
					break;
				case 'formatted_date_envoyee':
					$content .= $object->get_transfert_demande()->get_formatted_date_envoyee();
					break;
				case 'formatted_date_refus':
					$content .= $object->get_transfert_demande()->get_formatted_date_visualisee();
					break;
				case 'transfert_ask_user_num':
				case 'transfert_send_user_num':
					$content .= user::get_param(call_user_func_array(array($object, "get_".$property), array()), 'username');
					break;
				case 'transfert_bt_relancer':
					$content .= "<input type='button' class='bouton' value='".$msg["transferts_circ_btRelancer"]."' onclick='document.location=\"./circ.php?categ=trans&sub=refus&action=aff_redem&transid=".$object->get_id()."\"'>";
					break;
				case 'formatted_bt_date_retour':
					$content .= "<input type='button' class='bouton' name='bt_date_retour_".$object->get_id()."' value='".$object->get_formatted_date_retour()."' onClick=\"var reg=new RegExp('(-)', 'g'); openPopUp('".$base_path."/select.php?what=calendrier&caller=".$this->get_form_name()."&date_caller='+".$this->get_form_name().".date_retour_".$object->get_id().".value.replace(reg,'')+'&param1=date_retour_".$object->get_id()."&param2=bt_date_retour_".$object->get_id()."&auto_submit=NO&date_anterieure=YES&after=chgDate%28id_value,".$object->get_id()."%29', 'calendar')\" />
						<input type='hidden' name='date_retour_".$object->get_id()."' value='".$object->get_date_retour()."' />";
					break;
				default :
					$content .= parent::get_cell_content($object, $property);
					break;
			}	
		}
		return $content;
	}
	
	protected function get_edition_link() {
		global $msg;
		global $sub;
		$edition_link = '';
		//le lien pour l'édition si on a le droit ...
		if (SESSrights & EDIT_AUTH) {
			$sub_url = $sub;
			if($sub == 'departs' || $sub == 'valid' || $sub == 'retour') {
			    switch (static::class) {
					case 'list_transferts_envoi_ui':
						$sub_url = 'envoi';
						break;
					case 'list_transferts_retours_ui':
						$sub_url = 'retours';
						break;
					case 'list_transferts_validation_ui':
						$sub_url = 'validation';
						break;
				}
			}
			$url_edition = "./edit.php?categ=transferts&sub=".$sub_url;
			//on applique la seletion du filtre
			if ($this->filters['site_origine']) {
				$url_edition .= "&site_origine=" .$this->filters['site_origine'];
			}
			if ($this->filters['site_destination']) {
				$url_edition .= "&site_destination=" .$this->filters['site_destination'];
			}
			$edition_link = "<a href='" . $url_edition . "'>".$msg[1100]."</a>";
		}
		return $edition_link;
	}
	
	/**
	 * Affichage des éléments de recherche
	 */
	public function get_search_content() {
	    global $msg;
		global $list_transferts_ui_parcours_search_content_form_tpl;
		global $action;
		
		$content_form = $list_transferts_ui_parcours_search_content_form_tpl;
		
		$content_form = str_replace('!!json_filters!!', json_encode($this->filters), $content_form);
		$content_form = str_replace('!!json_selected_columns!!', json_encode($this->selected_columns), $content_form);
		$content_form = str_replace('!!json_settings!!', json_encode($this->settings), $content_form);
		$content_form = str_replace('!!json_applied_group!!', json_encode($this->applied_group), $content_form);
		$content_form = str_replace('!!json_applied_sort!!', json_encode($this->applied_sort), $content_form);
		$content_form = str_replace('!!page!!', $this->pager['page'], $content_form);
		$content_form = str_replace('!!nb_per_page!!', $this->pager['nb_per_page'], $content_form);
		$content_form = str_replace('!!pager!!', json_encode($this->pager), $content_form);
		$content_form = str_replace('!!selected_filters!!', json_encode($this->selected_filters), $content_form);
		$content_form = str_replace('!!ancre!!', (!empty($this->ancre) ? $this->ancre : ''), $content_form);
		$content_form = str_replace('!!go_directly_to_ancre!!', '', $content_form);
		$content_form = str_replace('!!messages!!', $this->get_messages(), $content_form);
		$content_form = str_replace('!!objects_type!!', $this->objects_type, $content_form);
		$content_form = str_replace('!!export_icons!!', $this->get_export_icons(), $content_form);
		$content_form = str_replace('!!list_button_add!!', $this->get_button_add(), $content_form);
		$content_form = str_replace('!!list_search_content_form_tpl!!', $this->get_search_content_form(), $content_form);
		if($this->get_setting('display', 'search_form', 'unfolded_filters')) {
		    $content_form = str_replace('!!expandable_icon!!', get_url_icon('minus.gif'), $content_form);
		    $content_form = str_replace('!!unfolded_filters!!', 'block', $content_form);
		} else {
		    $content_form = str_replace('!!expandable_icon!!', get_url_icon('plus.gif'), $content_form);
		    $content_form = str_replace('!!unfolded_filters!!', 'none', $content_form);
		}
		if((!empty($this->is_displayed_options_block) || $this->get_setting('display', 'search_form', 'options')) && isset($this->available_columns)) {
		    $content_form = str_replace('!!list_options_content_form_tpl!!', $this->get_options_content_form(), $content_form);
		    if((!empty($this->is_displayed_datasets_block) || $this->get_setting('display', 'search_form', 'datasets')) && $action != 'dataset_apply' && $action != 'dataset_save') {
		        $content_form = str_replace('!!list_button_save!!', "<input type='button' id='".$this->objects_type."_button_save' class='bouton' value='".$msg['77']."' onclick=\"this.form.action = '".static::get_controller_url_base()."&action=dataset_edit&id=0'; this.form.submit();\" />", $content_form);
		    } else {
		        $content_form = str_replace('!!list_button_save!!', "", $content_form);
		    }
		} else {
		    $content_form = str_replace('!!list_options_content_form_tpl!!', '', $content_form);
		    $content_form = str_replace('!!list_button_save!!', '', $content_form);
		}
		
		if($this->is_session_values()) {
		    $content_form = str_replace('!!list_button_initialization!!', "<input type='button' id='".$this->objects_type."_button_initialization' class='bouton' value='".$msg['list_ui_initialization']."' onclick=\"this.form.".$this->objects_type."_initialization.value = 'reset'; this.form.submit();\" />", $content_form);
		} else {
		    $content_form = str_replace('!!list_button_initialization!!', '', $content_form);
		}
		$content_form = str_replace('!!list_buttons_extension!!', $this->get_search_buttons_extension(), $content_form);
		if(count($this->get_datasets()['my'])) {
		    $content_form = str_replace('!!list_datasets_my_content_form_tpl!!', $this->get_datasets_content_form('my'), $content_form);
		} else {
		    $content_form = str_replace('!!list_datasets_my_content_form_tpl!!', '', $content_form);
		}
		if(count($this->get_datasets()['shared'])) {
		    $content_form = str_replace('!!list_datasets_shared_content_form_tpl!!', $this->get_datasets_content_form('shared'), $content_form);
		} else {
		    $content_form = str_replace('!!list_datasets_shared_content_form_tpl!!', '', $content_form);
		}
		
		$content_form = str_replace('!!nb_res!!', $this->pager['nb_per_page'], $content_form);
// 		$content_form = str_replace('!!filters!!', $this->get_search_content_form(), $content_form);
// 		$content_form = str_replace('!!json_filters!!', json_encode($this->filters), $content_form);
// 		$content_form = str_replace('!!page!!', $this->pager['page'], $content_form);
// 		$content_form = str_replace('!!nb_per_page!!', $this->pager['nb_per_page'], $content_form);
// 		$content_form = str_replace('!!pager!!', json_encode($this->pager), $content_form);
// 		$content_form = str_replace('!!objects_type!!', $this->objects_type, $content_form);
		$content_form = str_replace('!!edition_link!!', $this->get_edition_link(), $content_form);
		return $content_form;
	}
	
	protected function add_column_sel_button() {
		$this->columns[] = array(
				'property' => '',
				'label' => "<div class='center'><input type='button' class='bouton' name='+' onclick='SelAll(document.".$this->get_form_name().");' value='+'></div>",
				'html' => "<div class='center'><input type='checkbox' name='sel_!!id!!' value='1'></div>",
                'exportable' => false
		);
	}
	
	public function get_display_list() {
		global $current_module;
		global $list_transferts_ui_script_case_a_cocher;
		
		$display = '';
		if($current_module == 'circ') {
			$display .= "
			<br />
			<form name='".$this->get_form_name()."' class='form-".$current_module."' method='post' action='".static::get_controller_url_base()."'>
				".$this->get_form_title()."
				<div class='form-contenu' >";
			$display .= $this->get_search_content();
			if(count($this->objects)) {
				//Récupération du script JS de tris
				$display .= $this->get_js_sort_script_sort();
				//Affichage de la liste des objets
				$display .= "<table id='".$this->objects_type."_list' class='list_ui_list ".$this->objects_type."_list'>";
				$display .= $this->get_display_header_list();
				$display .= $this->get_display_content_list();
				$display .= "</table><br />";
				$display .= $this->get_display_selection_actions();
				$display .= $this->pager();
			} else {
				$display .= $this->get_display_no_results();
			}
			$display .= "</div>
                <input type='hidden' name='action'>
			    <input type='hidden' id='statut_reception_list' name='statut_reception'>
			    <input type='hidden' id='section_reception_list' name='section_reception'>
			</form>
			".$list_transferts_ui_script_case_a_cocher;
		} else {
			$display .= parent::get_display_list();
		}
		return $display;
	}
	
	public function get_display_valid_list() {
		global $msg;
		global $action;
		global $list_transferts_ui_valid_list_tpl;
		
		$display = $this->get_title();
		$display .= $list_transferts_ui_valid_list_tpl;
		
		$display = str_replace('!!submit_action!!', static::get_controller_url_base()."&action=".str_replace('aff_', '', $action), $display);
		$display = str_replace('!!valid_form_title!!', $this->get_valid_form_title(), $display);
		$display_valid_list = $this->get_display_header_list();
		if(count($this->objects)) {
			$display_valid_list .= $this->get_display_content_list();
		}
		$display = str_replace('!!valid_list!!', $display_valid_list, $display);
		$motif = '';
		if(static::class == 'list_transferts_refus_ui' && $action != 'aff_supp') {
			$motif .= "<hr />".$msg["transferts_circ_validation_refus_motif"]."<br />
					<textarea name='motif_refus' cols=60></textarea>"; 
		}
		$display = str_replace('!!motif!!', $motif, $display);
		$display = str_replace('!!valid_action!!', static::get_controller_url_base(), $display);
		$display = str_replace('!!ids!!', $this->filters['ids'], $display);
		$display = str_replace('!!objects_type!!', $this->objects_type, $display);
		return $display;
	}
	
	protected function _get_query_human_site_origine() {
		$docs_location = new docs_location($this->filters['site_origine']);
		return $docs_location->libelle;
	}
	
	protected function _get_query_human_site_destination() {
		$docs_location = new docs_location($this->filters['site_destination']);
		return $docs_location->libelle;
	}
	
	protected function _get_query_human_f_etat_date() {
		global $msg;
		
		$f_etat_date = '';
		if($this->filters['f_etat_date']) {
			switch ($this->filters['f_etat_date']) {
				case '1':
					$f_etat_date .= $msg["transferts_circ_retour_filtre_etat_proche"];
					break;
				case '2':
					$f_etat_date .= $msg["transferts_circ_retour_filtre_etat_depasse"];
					break;
			}
		}
		return $f_etat_date;
	}
	
	protected function _get_query_human_f_etat_dispo() {
		global $msg;
		
		$f_etat_dispo = '';
		if($this->filters['f_etat_dispo']) {
			switch ($this->filters['f_etat_dispo']) {
				case '1':
					$f_etat_dispo .= $msg["transferts_circ_retour_filtre_dispo"];
					break;
				case '2':
					$f_etat_dispo .= $msg["transferts_circ_retour_filtre_circ"];
					break;
			}
		}
		return $f_etat_dispo;
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $current_module;
		return $base_path.'/ajax.php?module='.$current_module.'&categ=transferts';
	}
}