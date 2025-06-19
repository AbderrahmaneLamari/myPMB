<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facettes_controller.class.php,v 1.15 2022/04/29 15:17:09 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// Controleur de facettes
global $class_path;
require_once($class_path."/facette_search_opac.class.php");
require_once($class_path."/facette.class.php");

class facettes_controller extends lists_controller {
	
	protected static $object_id = 0;
	
	protected static $type;
	
	protected static $is_external = 0;
	
	protected static function get_list_ui_instance($filters=array(), $pager=array(), $applied_sort=array()) {
		return new static::$list_ui_class_name(array('type' => static::$type), $pager, $applied_sort);
	}
	
	protected static function init_list_ui_class_name() {
		global $sub;
		switch ($sub) {
			case 'facettes_authorities':
				static::$list_ui_class_name = 'list_configuration_opac_facettes_authorities_ui';
				break;
			case 'facettes_external':
				static::$list_ui_class_name = 'list_configuration_opac_facettes_external_ui';
				break;
			default:
				static::$list_ui_class_name = 'list_configuration_opac_facettes_ui';
				break;
		}
	}
	public static function proceed($id=0) {
		global $sub;
		global $action;
		
		$id = intval($id);
		static::init_list_ui_class_name();
		if($sub == 'facettes_authorities') {
			print static::get_authorities_tabs();
		}
		$list_ui_class_name = static::$list_ui_class_name;
		$facette_search = self::get_facette_search_opac_instance(static::$type, static::$is_external);
		$list_ui_class_name::set_facettes_model($facette_search);
		switch($action) {
			case "add":
			case "edit":
				$facette = self::get_facette_instance($id);
				$facette->set_type(static::$type);
				print $facette->get_form();
				break;
			case "save":
				$facette = self::get_facette_instance($id);
				$facette->set_type(static::$type);
				$facette->set_properties_from_form();
				$facette->save();
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
			case "delete":
			    $facette = self::get_facette_instance($id);
				$facette->delete();
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
			case "up":
				facette_search_opac::facette_up($id, static::$type);
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
			case "down":
				facette_search_opac::facette_down($id, static::$type);
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
			case "order":
				facette_search_opac::facette_order_by_name(static::$type);
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
			default:
				$list_ui_instance = static::get_list_ui_instance();
				print $list_ui_instance->get_display_list();
				break;
		}
	}
	
	public static function get_authority_tab($type, $label='') {
		global $msg;
		global $base_path;
		
		$url_base = $base_path.'/admin.php?categ=opac&sub=facettes_authorities';
		return "<span".ongletSelect(substr($url_base, strpos($url_base, '?')+1)."&type=".$type).">
			<a title='".$msg[$type]."' href='".$url_base."&type=".$type."'>
				".$msg[$type]."
			</a>
		</span>";
	}
	
	public static function get_authorities_tabs() {
		$authorities_tabs = "<div class='hmenu'>";
		$authorities_tabs .= static::get_authority_tab('authors');
		$authorities_tabs .= static::get_authority_tab('categories');
		$authorities_tabs .= static::get_authority_tab('publishers');
		$authorities_tabs .= static::get_authority_tab('collections');
		$authorities_tabs .= static::get_authority_tab('subcollections');
		$authorities_tabs .= static::get_authority_tab('series');
		$authorities_tabs .= static::get_authority_tab('titres_uniformes');
		$authorities_tabs .= static::get_authority_tab('indexint');
		$authorities_tabs .= static::get_authority_tab('authperso');
		$authorities_tabs .= "</div>";
		return $authorities_tabs;
	}
	
	public static function proceed_ajax($object_type, $directory='') {
		global $sub, $object_type;
		global $action;
		global $type;
		global $list_crit,$sub_field;
		global $suffixe_id, $no_label;
		global $authperso_id, $field;
		
		static::init_list_ui_class_name();
		switch($sub){
		    case "lst_fields_facet":
		    case "lst_fields_facettes_authorities":
		    case "lst_fields_facettes":
			    if( strpos($type, "authperso") !== false && !empty($authperso_id)) {
			        $type = "authperso_".$authperso_id;
			    }
			    $facettes = self::get_facette_search_opac_instance($type);
			    print $facettes->create_list_fields($field);
			    break;
			case "lst_facet":
			case "lst_facettes_authorities":
		    case "lst_facettes":
				$facettes = self::get_facette_search_opac_instance($type);
				print $facettes->create_list_subfields($list_crit,$sub_field,$suffixe_id,$no_label);
				break;
			case "lst_facettes_external":
			    $facettes_external = self::get_facette_search_opac_instance('notices_externes',1);
				print $facettes_external->create_list_subfields($list_crit,$sub_field,$suffixe_id,$no_label);
				break;
			default:
			    $facette = self::get_facette_instance(static::$object_id);
				switch($action) {
					case "add":
					case "edit":
						$facette->set_type(static::$type);
						print $facette->get_form();
						break;
					case "save":
						$facette->set_type(static::$type);
						$facette->set_properties_from_form();
						$facette->save();
						return $facette->get_id();
						break;
					case "list":
						$facette_search = self::get_facette_search_opac_instance(static::$type, static::$is_external);
						$list_ui_class_name = static::$list_ui_class_name;
						$list_ui_class_name::set_facettes_model($facette_search);
						parent::proceed_ajax($object_type, $directory);
						break;
				}
				break;
		}
	}
	
	private static function get_facette_instance($id) {
	    if (strpos(static::$type, "authperso") !== false) {
	    	return new facette_authperso($id, static::$is_external);
	    }
	    if (strpos(static::$type, "external") !== false) {
	    	static::$is_external = true;
	    }
	    return new facette($id, static::$is_external);
	}
	
	public static function get_facette_search_opac_instance($type='notices', $is_external=false) {
	    if (empty($type)) {
	        $type = "notices";
	    }
	    if (strpos($type, "authperso") !== false) {
	        return new facette_authperso_search_opac($type, $is_external);
	    }
	    return new facette_search_opac($type, $is_external);
	}
	
	public static function set_object_id($object_id) {
		static::$object_id = intval($object_id);
	}
	
	public static function set_type($type) {
		static::$type = $type;
	}
	
	public static function set_is_external($is_external) {
		static::$is_external = intval($is_external);
	}
}

