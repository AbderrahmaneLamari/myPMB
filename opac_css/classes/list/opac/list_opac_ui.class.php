<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_ui.class.php,v 1.1.2.3 2023/07/27 13:04:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/list/list_ui.class.php');

class list_opac_ui extends list_ui {
		
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
	}
	
	protected function _get_sort_icon_cell_header($name, $data_sorted) {
	    return '';
	}
	
	public static function get_controller_url_base() {
		global $base_path;
	
		return $base_path.'/index.php';
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $lvl;
		return $base_path.'/ajax.php?module=empr&lvl='.$lvl;
	}
}