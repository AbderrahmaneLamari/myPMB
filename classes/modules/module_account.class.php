<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: module_account.class.php,v 1.10.2.5 2021/12/13 08:37:51 dgoron Exp $

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
	
	protected function get_list_ui_class_name($objects_type) {
		if(strpos($objects_type, 'authorities_caddie_content_ui_') === 0) {
			$object_type = str_replace('authorities_caddie_content_ui_', '', $objects_type);
			$list_ui_class_name = 'list_authorities_caddie_content_ui';
			$list_ui_class_name::set_object_type($object_type);
		} elseif(strpos($objects_type, 'empr_caddie_content_ui_') === 0) {
			$object_type = str_replace('empr_caddie_content_ui_', '', $objects_type);
			$list_ui_class_name = 'list_empr_caddie_content_ui';
			$list_ui_class_name::set_object_type($object_type);
		} elseif(strpos($objects_type, 'caddie_content_ui_') === 0) {
			$object_type = str_replace('caddie_content_ui_', '', $objects_type);
			$list_ui_class_name = 'list_caddie_content_ui';
			$list_ui_class_name::set_object_type($object_type);
		} else {
			$list_ui_class_name = 'list_'.$objects_type;
		}
		
		return $list_ui_class_name;
	}
			
	protected function get_list_ui_instance($class_name) {
		global $empr_sort_rows, $empr_show_rows, $empr_filter_rows;
		
		switch ($class_name) {
			case 'list_readers_circ_ui':
			case 'list_readers_relances_ui':
				if (($empr_sort_rows)||($empr_show_rows)||($empr_filter_rows)) {
					$filter = emprunteur::get_instance_filter_list();
					$class_name::set_used_filter_list_mode(true);
					$class_name::set_filter_list($filter);
				}
				return new $class_name();
			default :
				return new $class_name();
				
		}
	}
	
	public function proceed_lists() {
		global $action;
		global $objects_type;
		
		switch($action){
			case 'edit':
				if(isset($objects_type) && $objects_type) {
					$list_ui_class_name = $this->get_list_ui_class_name($objects_type);
					$list_ui_instance = $this->get_list_ui_instance($list_ui_class_name);
					print "<h2>".$list_ui_instance->get_dataset_title()."</h2>";
					print $list_ui_instance->get_default_dataset_form($this->object_id);
				}
				break;
			case 'save':
				if(isset($objects_type) && $objects_type) {
					$list_ui_class_name = $this->get_list_ui_class_name($objects_type);
					$list_ui_instance = $this->get_list_ui_instance($list_ui_class_name);
					$list_model = new list_model($this->object_id);
					$list_model->set_num_user(0);
					$list_model->set_objects_type($list_ui_instance->get_objects_type());
					$list_model->set_list_ui($list_ui_instance);
					$list_model->set_properties_from_form();
					$has_doublon = false;
					if(method_exists($list_model, 'get_query_if_exists')) {
						$query = $list_model->get_query_if_exists();
						$result = pmb_mysql_query($query);
						$has_doublon = pmb_mysql_result($result, 0, 0);
					}
					if(!$has_doublon) {
						$list_model->save();
					}
				}
				$list_lists_ui = new list_lists_ui();
				print $list_lists_ui->get_display_list();
				break;
			case 'delete':
				list_model::delete_common_list($this->object_id, $objects_type);
				$list_lists_ui = new list_lists_ui();
				print $list_lists_ui->get_display_list();
				break;
			case 'list_delete':
				list_lists_ui::delete();
				$list_lists_ui = new list_lists_ui();
				print $list_lists_ui->get_display_list();
				break;
			default :
				$list_lists_ui = new list_lists_ui();
				print $list_lists_ui->get_display_list();
				break;
		}
	}
	
	public function proceed_tabs() {
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
	
	public function proceed_pdf() {
		$list_parameters_pdf_ui = new list_parameters_pdf_ui();
		print $list_parameters_pdf_ui->get_display_list();
	}
	
	public function proceed_mail() {
		$list_parameters_mail_ui = new list_parameters_mail_ui();
		print $list_parameters_mail_ui->get_display_list();
	}
	
	public function proceed_translations() {
		$list_translations_ui = new list_translations_ui();
		print $list_translations_ui->get_display_list();
	}
	
	public function proceed_forms() {
		$list_forms_ui = new list_forms_ui();
		print $list_forms_ui->get_display_list();
	}
	
	public function proceed_selectors() {
		$this->load_class("/selectors/selectors_controller.class.php");
		selectors_controller::proceed();
	}
	
	public function proceed_logs() {
		global $supervision_logs_active;
		
		print "
		<div class='row'>
			".parameter::get_input_activation('supervision', 'logs_active', $supervision_logs_active)."
		</div>
		";
		$this->load_class("/logs/logs_controller.class.php");
		logs_controller::proceed($this->object_id);
	}
	
	public function proceed_audit() {
		print list_audit_ui::get_instance()->get_display_list();
	}
	
	public function proceed_mails() {
		global $supervision_mails_active;
		
		print "
		<div class='row'>
			".parameter::get_input_activation('supervision', 'mails_active', $supervision_mails_active)."
		</div>
		";
		$this->load_class("/mails/mails_controller.class.php");
		mails_controller::proceed($this->object_id);
	}
	
	public function proceed_mails_waiting() {
		$this->load_class("/mails/mails_waiting_controller.class.php");
		mails_waiting_controller::proceed($this->object_id);
	}
}