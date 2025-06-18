<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: loans_edition_controller.class.php,v 1.2 2021/04/13 08:04:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/loans/loans_controller.class.php");

class loans_edition_controller extends loans_controller {
	
	protected static $list_ui_class_name = 'list_loans_edition_ui';
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
		global $sub;
		
		$list_ui_instance = null;
		switch($sub) {
		    case 'retard' :
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => date('Y-m-d'), 'short_loan_flag' => 0, 'pret_date_end' => '', 'pret_retour_start' => ''), array(), array('by' => 'empr'));
		        break;
		    case 'retard_par_date' :
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => date('Y-m-d'), 'short_loan_flag' => 0, 'pret_date_end' => '', 'pret_retour_start' => ''), array(), array('by' => 'pret_retour_empr'));
		        break;
		    case 'short_loans':
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => '', 'short_loan_flag' => 1, 'pret_date_end' => '', 'pret_retour_start' => ''), array(), array('by' => 'pret_retour'));
		        break;
		    case 'unreturned_short_loans' :
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => '', 'short_loan_flag' => 1, 'pret_date_end' => date('Y-m-d'), 'pret_retour_start' => date('Y-m-d')), array(), array('by' => 'pret_retour'));
		        break;
		    case 'overdue_short_loans' :
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => date('Y-m-d'), 'short_loan_flag' => 1, 'pret_date_end' => '', 'pret_retour_start' => ''), array(), array('by' => 'pret_retour'));
		        break;
		    case 'archives' :
		        $list_ui_instance = new list_loans_archives_edition_ui();
		        break;
		    default:
		        $list_ui_instance = new static::$list_ui_class_name(array('pret_retour_end' => '', 'short_loan_flag' => 0, 'pret_date_end' => '', 'pret_retour_start' => ''), array(), array('by' => 'pret_retour'));
		        break;
		}
		return $list_ui_instance;
	}
	
	public static function proceed($id=0) {
		global $dest, $action;
		
		parent::proceed($id);
		switch($dest) {
			case "TABLEAU":
				break;
			case "TABLEAUHTML":
				break;
			default:
				//impression/emails (on est dans le cas retards/retards par date)
				if ($action == "print") {
					$list_ui_instance = static::get_list_ui_instance();
					$list_ui_instance->print_relances();
				}
				break;
		}
	}
}