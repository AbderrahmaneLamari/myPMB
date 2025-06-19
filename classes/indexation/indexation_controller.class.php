<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_controller.class.php,v 1.1.2.2 2023/06/13 13:53:44 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/indexation_stack.class.php");

class indexation_controller extends lists_controller {
	
// 	protected static $model_class_name = 'indexation_model';
	
	protected static $list_ui_class_name = 'list_indexation_ui';
	
	public static function proceed($id=0) {
		global $action;
		
		$id = intval($id);
		switch ($action) {
// 			case 'list_manual_indexation':
// 				$list_ui_class_name = static::$list_ui_class_name;
// 				$list_ui_class_name::run_action_list('manual_indexation');
// 				static::redirect_display_list();
// 				break;
			default:
				parent::proceed($id);
				break;
		}
	}
}