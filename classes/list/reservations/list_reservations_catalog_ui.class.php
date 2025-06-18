<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_reservations_catalog_ui.class.php,v 1.4.2.1 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_reservations_catalog_ui extends list_reservations_ui {
		
	protected function init_default_columns() {
		global $pmb_resa_planning;
		
		if(!$this->filters['id_notice'] && !$this->filters['id_bulletin']) {
			$this->add_column('record');
		}
		$this->add_column('expl_cote');
		if(!$this->filters['id_empr']) {
			$this->add_column('empr');
			$this->add_column('empr_location');
		}
		$this->add_column('rank');
		$this->add_column('resa_date');
		$this->add_column('resa_condition');
		if ($pmb_resa_planning) {
			$this->add_column('resa_date_debut');
		}
		$this->add_column('resa_date_fin');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
	}
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('record');
		$this->add_applied_sort('resa_date');
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $current_module, $sub;
		return $base_path.'/ajax.php?module='.$current_module.'&categ=resa&sub='.$sub;
	}
}