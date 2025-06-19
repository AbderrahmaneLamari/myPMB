<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: module_account.class.php,v 1.17 2022/09/27 09:41:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($class_path."/modules/module.class.php");
require_once($include_path."/templates/modules/module_account.tpl.php");
require_once($class_path.'/parameters/parameter.class.php');

class module_account extends module{
	
	public function proceed_header(){
		global $dest, $account_js_script_layout;
		
		parent::proceed_header();
		switch($dest) {
			case "TABLEAU":
				break;
			case "TABLEAUHTML":
				break;
			case "TABLEAUCSV":
				break;
			case "EXPORT_NOTI":
				break;
			case "PLUGIN_FILE": // utiliser pour les plugins
				break;
			default:
				print $account_js_script_layout;
				break;
		}
	}
	
	public function proceed_favorites() {
		global $base_path;
		
		include "$base_path/account/favorites/favorites.inc.php";
	}
	
	public function proceed_mails() {
		global $sub;
		
		switch($sub) {
			case 'configuration':
				$this->load_class("/mails/mails_configuration_controller.class.php");
				mails_configuration_controller::proceed($this->object_id);
				break;
// 			case 'settings':
// 				$this->load_class("/mails/mails_settings_controller.class.php");
// 				mails_settings_controller::proceed($this->object_id);
// 				break;
		}
	}
	
	public function proceed_lists() {
		$this->load_class("/list/lists_ui_controller.class.php");
		lists_ui_controller::proceed($this->object_id);
	}
	
	public function proceed_tabs() {
		$this->load_class("/tabs/tabs_controller.class.php");
		tabs_controller::proceed($this->object_id);
	}
	
	public function proceed_modules() {
		global $action;
		global $name;
		
		$this->load_class("/modules/module_model.class.php");
		switch($action){
			case 'edit':
				if(isset($name) && $name) {
					$model_instance = new module_model($name);
					print $model_instance->get_form();
				}
				break;
			case 'save':
				$model_instance = new module_model($name);
				$model_instance->set_properties_from_form();
				$model_instance->save();
				
				$list_modules_ui = new list_modules_ui();
				print $list_modules_ui->get_display_list();
				break;
			case 'delete':
				module_model::delete($name);
				
				$list_modules_ui = new list_modules_ui();
				print $list_modules_ui->get_display_list();
				break;
			default :
				$list_modules_ui = new list_modules_ui();
				print $list_modules_ui->get_display_list();
				break;
		}
	}
	
	public function proceed_selectors() {
		$this->load_class("/selectors/selectors_controller.class.php");
		selectors_controller::proceed($this->object_id);
	}
}