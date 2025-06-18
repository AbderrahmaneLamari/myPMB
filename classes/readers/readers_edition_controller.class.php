<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: readers_edition_controller.class.php,v 1.2 2020/11/05 09:39:48 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/readers/readers_controller.class.php");

class readers_edition_controller extends readers_controller {
	
	protected static $list_ui_class_name = 'list_readers_edition_ui';
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
		global $sub, $dest, $statut_action;
		global $pmb_relance_adhesion;
		
		$list_ui_instance = null;
		switch($sub) {
			case "limite" :
				$list_ui_instance = new static::$list_ui_class_name(array('date_expiration_start' => date('Y-m-d'), 'date_expiration_end' =>'', 'date_expiration_limit' => '((to_days(empr_date_expiration) - to_days(now()) ) <=  '.$pmb_relance_adhesion.' )', 'change_categ' => ''));
				break;
			case "depasse" :
				$list_ui_instance = new static::$list_ui_class_name(array('date_expiration_start' => '', 'date_expiration_end' => date('Y-m-d'), 'date_expiration_limit' => '', 'change_categ' => ''));
				break;
			case "categ_change" :
				$list_ui_instance = new static::$list_ui_class_name(array('date_expiration_start' => '', 'date_expiration_end' => '', 'date_expiration_limit' => '', 'change_categ' => '((((age_min<> 0) || (age_max <> 0)) && (age_max >= age_min)) && (((DATE_FORMAT( curdate() , "%Y" )-empr_year) < age_min) || ((DATE_FORMAT( curdate() , "%Y" )-empr_year) > age_max)))'));
				break;
			case "encours" :
			default :
				$list_ui_instance = new static::$list_ui_class_name(array('date_expiration_start' => date('Y-m-d'), 'date_expiration_end' => '', 'date_expiration_limit' => '', 'change_categ' => ''));
				break;
		}
		switch($dest) {
			case "TABLEAU":
				break;
			case "TABLEAUHTML":
				break;
			default:
				if (isset($statut_action) && $statut_action=="modify") {
					$list_ui_instance->run_change_status();
				}
				break;
		}
		return $list_ui_instance;
	}
	
}