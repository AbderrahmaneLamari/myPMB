<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: module_admin.class.php,v 1.18.2.1 2021/11/25 14:31:39 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($class_path."/modules/module.class.php");
require_once($include_path."/templates/modules/module_admin.tpl.php");

class module_admin extends module{
	
	public function proceed_misc() {
		global $sub;
		global $module_admin_misc_files_content;
		global $include_path, $lang;
		
		switch($sub) {
			case 'tables':
				$this->load_class("/misc/misc_tables_controller.class.php");
				misc_tables_controller::proceed($this->object_id);
				break;
			case 'tables_data':
				$this->load_class("/misc/misc_tables_data_controller.class.php");
				misc_tables_data_controller::proceed($this->object_id);
				break;
			case 'mysql':
				include("./admin/misc/mysql.inc.php");
				break;
			case 'files':
				$this->load_class("/misc/files/misc_files.class.php");
				print $module_admin_misc_files_content;
				break;
			default:
				include("$include_path/messages/help/$lang/admin_misc.txt");
				break;
		}
	}
	
	public function proceed_facets() {
		global $sub, $action, $type;
		
		switch($sub){
			case "facettes":
				$this->load_class("/facettes_controller.class.php");
				facettes_controller::set_type('notices');
				facettes_controller::proceed($this->object_id);
				break;
			case "facettes_authorities":
				$this->load_class("/facettes_controller.class.php");
				facettes_controller::set_type($type);
				facettes_controller::proceed($this->object_id);
				break;
			case "facettes_external":
				$this->load_class("/facettes_controller.class.php");
				facettes_controller::set_type('notices_externes');
				facettes_controller::set_is_external(1);
				facettes_controller::proceed($this->object_id);
				break;
			case "facettes_comparateur":
				$this->load_class("/facette_search_compare.class.php");
				$facette_compare = new facette_search_compare();
				switch($action) {	
					case "update":
					case "save":
						$facette_compare->save_form();
						print $facette_compare->get_display_parameters();
					break;
					case "modify":
						print $facette_compare->get_form();
						break;
					case "display":
					default:
						print $facette_compare->get_display_parameters();
					break;
				}
				break;
		}
	}
	
	public function proceed_mails_waiting() {
		$this->load_class("/mails_waiting.class.php");
		
		mails_waiting::proceed();
	}
	
	public function proceed_search_universes() {	    
		global $sub, $msg, $database_window_title, $include_path;
		global $lang;
		
		$this->load_class("/search_universes/search_universes_controller.class.php");
	  
        switch($sub) {
        	case 'universe':
        		$search_universes_controller = new search_universes_controller($this->object_id);
        	    $search_universes_controller->proceed_universe();
        		break;
        	case 'segment':
        		$search_universes_controller = new search_universes_controller($this->object_id);
        		$search_universes_controller->proceed_segment();
        		break;
        	default:
        		echo window_title($database_window_title. $msg['admin_menu_search_universes'].$msg[1003].$msg[1001]);
         		include($include_path."/messages/help/".$lang."/admin_search_universes.txt");
        		break;
        }    
	}
	
	public function proceed(){
		global $categ;
		global $module_layout_end;
	
		if($categ && method_exists($this, "proceed_".$categ)) {
			$method_name = "proceed_".$categ;
			$this->{$method_name}();
		} else {
			$layout_template = $this->get_layout_template();
			$layout_template = str_replace("!!menu_contextuel!!", "", $layout_template);
			print str_replace("!!menu_sous_rub!!","",$layout_template);
		}
		print $module_layout_end;
	}
	
	public function proceed_ajax_misc() {
		global $class_path;
		global $sub;
		global $action;
		global $path, $filename;
		global $object_type;
		
		$this->load_class("/misc/files/misc_files.class.php");
		switch($sub){
			case 'tables':
				switch($action){
					case 'list':
						$this->load_class("/misc/misc_tables_controller.class.php");
						misc_tables_controller::proceed_ajax($object_type, 'misc');
						break;
				}
				break;
			case 'tables_data':
				switch($action){
					case 'list':
						$this->load_class("/misc/misc_tables_data_controller.class.php");
						list_misc_tables_data_ui::set_table(substr($object_type,strpos($object_type, '_ui_')+4));
						misc_tables_data_controller::proceed_ajax(substr($object_type,0,strpos($object_type, '_ui_')+3), 'misc');
						break;
				}
				break;
			case 'files':
				switch($action){
					case 'get_datas':
						$misc_files = new misc_files();
						print encoding_normalize::json_encode(encoding_normalize::utf8_normalize($misc_files->get_tree_data()));
						break;
				}
				break;
			case 'file':
				switch($action){
					case 'get_form':
						header('Content-type: text/html;charset=utf-8');
						$misc_file = misc_files::get_model_instance($path, $filename);
						print encoding_normalize::utf8_normalize($misc_file->get_form());
						break;
					case 'get_contents':
						$misc_file = misc_files::get_model_instance($path, $filename);
						$is_writable_dir = 0;
						if(is_writable($path)) {
							$is_writable_dir = 1;
						}
						header('Content-type: application/json;charset=utf-8');
						print encoding_normalize::json_encode(array('contents' => $misc_file->get_contents(), 'is_writable_dir' => $is_writable_dir));
						break;
					case 'save_contents':
						$misc_file = misc_files::get_model_instance($path, $filename);
						$saved = $misc_file->save_contents();
						print encoding_normalize::json_encode(array('status' => $saved, 'elementId' => $misc_file->get_full_path()));
						break;
					case 'save':
						$misc_file = misc_files::get_model_instance($path, $filename);
						$misc_file->set_properties_from_form();
						$saved = $misc_file->save();
						print encoding_normalize::json_encode(array('status' => $saved, 'elementId' => $misc_file->get_full_path()));
						break;
					case 'initialization':
						$misc_file = misc_files::get_model_instance($path, $filename);
						$misc_file->set_data();
						$saved = $misc_file->save();
						print encoding_normalize::json_encode(array('status' => $saved, 'elementId' => $misc_file->get_full_path()));
						break;
					case 'delete':
						$misc_file = misc_files::get_model_instance($path, $filename);
						$deleted = $misc_file->delete();
						print encoding_normalize::json_encode(array('status' => $deleted, 'elementId' => $misc_file->get_full_path()));
						break;
					case 'add_substitute':
						break;
					case 'delete_substitute':
						break;
				}
				break;
			
		}
	}
	
	public function proceed_ajax_search_universes(){
		global $class_path;
		global $sub;
		
		$this->load_class("/search_universes/search_universes_controller.class.php");
		
		switch($sub) {
			case 'universe':
			case 'segment':
				$search_universes_controller = new search_universes_controller();
				$search_universes_controller->proceed_ajax();
				break;
			default:
				print encoding_normalize::json_encode(array());
				break;
		}
	}
}