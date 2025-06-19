<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_recouvr_reader_ui.class.php,v 1.3 2022/09/27 06:32:58 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_recouvr_reader_ui extends list_recouvr_ui {
	
	protected function _get_query_base() {
	    $query = "SELECT recouvrements.recouvr_id as id, recouvrements.*, empr.*, exemplaires.* 
			FROM recouvrements
			JOIN empr ON id_empr=empr_id
			JOIN docs_location ON empr_location=idlocation
			LEFT JOIN exemplaires on expl_id=id_expl";
	    return $query;
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('date_rec');
		$this->add_column('type');
		$this->add_column('titre');
		$this->add_column('expl_cb');
		$this->add_column('expl_cote');
		$this->add_column('date_pret');
		$this->add_column('date_relance1');
		$this->add_column('date_relance2');
		$this->add_column('date_relance3');
		$this->add_column('prix_calcul');
		$this->add_column('montant');
	}
	
	protected function get_selection_mode() {
		return 'button';
	}
	
	protected function get_name_selected_objects() {
		return "recouvr_ligne";
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		
		parent::init_default_selection_actions();
		$del_line_link = array(
				'href' => static::get_controller_url_base()."&act_line=del_line",
				'confirm' => $msg['relance_recouvrement_confirm_del']
		);
		$this->add_selection_action('del_line', $msg['relance_recouvrement_del_all_lines'], '', $del_line_link);
		$solde_link = array(
				'href' => static::get_controller_url_base()."&act_line=solde",
				'confirm' => $msg['relance_recouvrement_confirm_solder']
		);
		$this->add_selection_action('solde', $msg['relance_recouvrement_solder'], '', $solde_link);
	}
	
	protected function get_button_cancel() {
		global $msg;
		
		return "<input class='bouton' type='button' value=' ".$msg['76']." ' onClick=\"document.location='./circ.php?categ=relance&sub=recouvr&act=recouvr_liste'\" />";
	}
	
	protected function get_button_add() {
		global $msg;
		
		return "<input class='bouton' type='button' value=' ".$msg['relance_recouvrement_add_line']." ' onClick=\"document.location='".static::get_controller_url_base()."&act_line=update_line'\" />";
	}
	
	protected function get_button_export() {
		global $msg, $id_empr;
		
		return "<input class='bouton' type='button' value=' ".$msg['relance_recouvrement_export_tableur']." ' onClick=\"document.location='./circ/relance/recouvr_reader_excel.php?id_empr=$id_empr'\" />";
	}
	
	protected function get_display_left_actions() {
		return $this->get_button_cancel().$this->get_button_add().$this->get_button_export();
	}
	
	public static function get_controller_url_base() {
		global $id_empr;
		$id_empr = intval($id_empr);
		return parent::get_controller_url_base().'&act=recouvr_reader&id_empr='.$id_empr;
	}
}