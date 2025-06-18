<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_transferts_retours_ui.class.php,v 1.5.2.2 2021/12/23 13:55:44 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_transferts_retours_ui extends list_transferts_ui {
	
	protected function get_form_title() {
		global $msg;
		global $transferts_retour_lot;
		if ($transferts_retour_lot=="1") {
			return "<h3>".$msg["transferts_circ_retours_lot"]."</h3>";
		} else {
			return "<h3>".$msg["transferts_circ_retour_list"]."</h3>";
		}
	}
	
	protected function init_default_columns() {
		global $action, $transferts_retour_lot;
		$this->add_column('record');
		$this->add_column('cb');
		$this->add_column('empr');
		$this->add_column('source', 'transferts_circ_destination');
		$this->add_column('expl_owner');
		$this->add_column('formatted_date_reception');
		$this->add_column('formatted_bt_date_retour', 'transferts_circ_date_retour');
		$this->add_column('motif');
		$this->add_column('transfert_ask_user_num');
		$this->add_column('transfert_send_user_num');
		if(($action == '' || $action == 'list') && $transferts_retour_lot == '1') {
			$this->add_column_sel_button();
		}
	}
	
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'site_origine' => 'transferts_circ_retour_filtre_destination',
						'f_etat_date' => 'transferts_circ_retour_filtre_etat',
						'f_etat_dispo' => 'transferts_circ_retour_filtre_dispo_title'
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('site_origine');
		$this->add_selected_filter('f_etat_date');
		$this->add_selected_filter('f_etat_dispo');
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = parent::_get_query_filters();
		$filter_query .= " AND num_expl not in (select num_expl from transferts_demande,transferts WHERE id_transfert=num_transfert and etat_transfert=0 AND etat_demande=1 )";
		return $filter_query;
	}	
	
	protected function get_display_selection_actions() {
		global $msg;
		return "<input type='button' class='bouton' name='".$msg["transferts_circ_btRetour"]."' value='".$msg["transferts_circ_btRetour"]."' onclick='verifChk(document.".$this->get_form_name().",\"aff_ret\")'>";
	}
	
	protected function get_display_no_results() {
		global $msg;
		global $list_transferts_ui_no_results;
		$display = $list_transferts_ui_no_results;
		$display = str_replace('!!message!!', $msg["transferts_retour_liste_vide"], $display);
		return $display;
	}
	
	protected function get_valid_form_title() {
		global $msg;
		return "<h3>".$msg["transferts_circ_retour_valide_liste"]."</h3>";
	}
	
	public function get_display_list() {
		global $list_transferts_ui_script_chg_date_retour;
		
		$display = parent::get_display_list();
		$display .= $list_transferts_ui_script_chg_date_retour;
		return $display;
	}
}