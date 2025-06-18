<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reservations_circ_controller.class.php,v 1.1.2.2 2021/10/21 12:26:15 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/reservations/reservations_controller.class.php");

class reservations_circ_controller extends reservations_controller {
	
	protected static $list_ui_class_name = 'list_reservations_circ_ui';
	
	protected static $ancre;
	
	public static function set_ancre($ancre) {
		static::$ancre = $ancre;
	}
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
		global $f_loc, $pmb_lecteurs_localises, $deflt_resas_location;
		
		if(!isset($f_loc) || $f_loc=="") {
			if ($pmb_lecteurs_localises){
				$f_loc = $deflt_resas_location;
			} else {
				$f_loc = 0;
			}
		}
		$list_ui_instance = new static::$list_ui_class_name(array('id_notice' => 0, 'id_bulletin' => 0, 'id_empr' => 0, 'resa_state' => 'encours', 'f_loc' => $f_loc));
		if(!empty(static::$ancre)) {
			$list_ui_instance->set_ancre(static::$ancre);
		}
		return $list_ui_instance;
	}
	
}