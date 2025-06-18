<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_reservations_circ_ui.class.php,v 1.12.2.2 2021/10/28 08:27:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_reservations_circ_ui extends list_reservations_ui {
	
	protected static $info_gestion = GESTION_INFO_GESTION;
	
	public static function set_globals_from_selected_filters() {
		global $f_loc;
		
		$objects_type = str_replace('list_', '', static::class);
		$initialization = $objects_type.'_initialization';
		global ${$initialization};
	    if(empty($f_loc) && (empty(${$initialization}) || ${$initialization} != 'reset')) {
			global $reservations_circ_ui_removal_location;
			$f_loc = $reservations_circ_ui_removal_location;
		}
	}
	
	public static function set_globals_from_json_filters($json_filters) {
	    global $f_loc;
	    
	    $filters = (!empty($json_filters) ? encoding_normalize::json_decode($json_filters, true) : array());
	    if(empty($f_loc) && !empty($filters['f_loc'])) {
	        $f_loc = $filters['f_loc'];
	    }
	}
	
	protected function init_available_filters() {
		parent::init_available_filters();
		unset($this->available_filters['main_fields']['resa_loc_retrait']);
	}
	
	protected function init_default_selected_filters() {
		global $pmb_transferts_actif, $pmb_location_reservation;
		
		$this->add_selected_filter('montrerquoi');
		if ($pmb_transferts_actif=="1" || $pmb_location_reservation) {
			$this->add_selected_filter('removal_location');
		}
	}
	
	protected function init_available_columns() {
		global $pmb_transferts_actif;
		parent::init_available_columns();
		if ($pmb_transferts_actif=="1") {
			$this->available_columns['main_fields']['resa_transfert'] = 'transferts_circ_resa_lib_choix_expl';
		}
	}
	
	protected function init_default_columns() {
		global $pmb_transferts_actif;
		global $pmb_resa_planning;
		
		$this->add_column_selection();
		$this->add_column('record');
		$this->add_column('expl_cote');
		$this->add_column('empr');
		$this->add_column('empr_location');
		$this->add_column('rank');
		$this->add_column('resa_date');
		$this->add_column('resa_condition');
		if ($pmb_resa_planning) {
			$this->add_column('resa_date_debut');
		}
		$this->add_column('resa_date_fin');
		$this->add_column('resa_validee');
		$this->add_column('resa_confirmee');
		if ($pmb_transferts_actif=="1") {
			$this->add_column('resa_loc_retrait');
			$this->add_column('transfert_location_source');
		}
		if ($pmb_transferts_actif=="1") {
			$this->add_column('resa_transfert');
		}
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('empr', 'align', 'left');
		$this->set_setting_column('empr_location', 'align', 'left');
		$this->set_setting_column('resa_loc_retrait', 'align', 'left');
	}
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('record');
		$this->add_applied_sort('resa_date');
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'resa_transfert':
				$resa_situation = $this->get_resa_situation($object);
				$resa_situation->get_display(static::$info_gestion);
				if ($resa_situation->lien_transfert) {
					if($object->transfert_resa_dispo($this->filters['f_loc'])){
						$img= get_url_icon("peb_in.png");
					}else {
						$img= get_url_icon("peb_out.png");
					}
					$content .= "
						<a href='#' onclick=\"choisiExpl(this);return(false);\" id_resa=\"".$object->id."\" idnotice=\"".$object->id_notice."\" idbul=\"".$object->id_bulletin."\" loc=\"".$this->filters['f_loc']."\" alt=\"".$msg["transferts_circ_resa_lib_choix_expl"]."\" title=\"".$msg["transferts_circ_resa_lib_choix_expl"]."\">
						<img src='$img'></a>";
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_html_content_selection() {
		return "<div class='center'><input type='checkbox' id='suppr_id_resa_!!id!!' name='suppr_id_resa[!!id!!]' class='".$this->objects_type."_selection' value='!!id!!'></div>";
	}
	
	protected function get_selection_actions() {
		global $msg, $pdflettreresa_priorite_email_manuel;
		
		if(!isset($this->selection_actions)) {
			$this->selection_actions = array();
			if ($pdflettreresa_priorite_email_manuel!=3) {
				$impression_confirmation_link = array(
						'href' => static::get_controller_url_base()."&action=imprimer_confirmation",
						'confirm' => ''
				);
				$this->selection_actions[] = $this->get_selection_action('impression_confirmation', $msg['resa_impression_confirmation'], '', $impression_confirmation_link);
			}
			$delete_link = array(
					'href' => static::get_controller_url_base()."&action=suppr_resa",
					'confirm' => $msg['resa_valider_suppression_confirm']
			);
			$this->selection_actions[] = $this->get_selection_action('delete', $msg['63'], 'interdit.gif', $delete_link);
		}
		return $this->selection_actions;
	}
	
	protected function get_selection_mode() {
		return 'button';
	}
	
	protected function get_name_selected_objects() {
		return "suppr_id_resa";
	}
	
	public function get_export_icons() {
		global $msg, $base_path;
		
		if($this->get_setting('display', 'search_form', 'export_icons')) {
			//le lien pour l'edition
			if (SESSrights & EDIT_AUTH) {
				return "<a href='".$base_path."/edit.php?categ=notices&sub=resa'>".$msg['1100']." : ".$msg['edit_resa_menu']."</a> / <a href='".$base_path."/edit.php?categ=notices&sub=resa_a_traiter'>".$msg['1100']." : ".$msg['edit_resa_menu_a_traiter']."</a>" ;
			}
		}
		return "";
	}
	
	public static function get_controller_url_base() {
		global $base_path, $sub;
		
		return $base_path.'/circ.php?categ=listeresa'.(!empty($sub) ? '&sub='.$sub : '');
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $current_module, $sub;
		return $base_path.'/ajax.php?module='.$current_module.'&categ=resa&sub='.$sub;
	}
}