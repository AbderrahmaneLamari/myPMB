<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_connecteurs_out_sets_ui.class.php,v 1.4 2021/04/19 07:10:15 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/connecteurs_out_sets.class.php");

class list_configuration_connecteurs_out_sets_ui extends list_configuration_connecteurs_ui {
	
	protected function _get_query_base() {
		return 'SELECT connector_out_set_id, connector_out_set_type FROM connectors_out_sets';
	}
	
	protected function new_connector_out_set_typed($id, $type=0) {
		$id = intval($id);
		if (!$type) {
			$sql = "SELECT connector_out_set_type FROM connectors_out_sets WHERE connector_out_set_id = ".$id;
			$type = pmb_mysql_result(pmb_mysql_query($sql), 0, 0);
		}
		if (!$type)
			$type=1;
		return new $this->connector_out_set_types_classes[$type]($id);
	}
	
	protected function get_object_instance($row) {
		
		return $this->new_connector_out_set_typed($row->connector_out_set_id, $row->connector_out_set_type);
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('type');
	}
	
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'type');
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'caption' => 'admin_connecteurs_sets_setcaption',
				'type' => 'admin_connecteurs_sets_settype',
				'additionalinfo' => 'admin_connecteurs_sets_setadditionalinfo',
				'latestcacheupdate' => 'admin_connecteurs_setcateg_latestcacheupdate',
		);
	}
	
	protected function init_default_columns() {
		parent::init_default_columns();
		$this->add_column_manualupdate();
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'manualupdate',
		);
	}
	
	protected function add_column_manualupdate() {
		global $msg, $charset;
		$this->columns[] = array(
				'property' => 'manualupdate',
				'label' => $msg['admin_connecteurs_setcateg_manualupdate'],
				'html' => "<input type='button' class='bouton_small' value='".htmlentities($msg["admin_connecteurs_setcateg_updatemanually"] ,ENT_QUOTES, $charset)."' onClick='document.location=\"".static::get_controller_url_base()."&action=manual_update&id=!!id!!\"'/>",
				'exportable' => false
		);
	}
	
	protected function get_display_content_object_list($object, $indice) {
		$this->is_editable_object_list = false;
		return parent::get_display_content_object_list($object, $indice);
	}
	
	protected function _get_object_property_type($object) {
		global $msg;
		return $msg[$this->connector_out_set_types_msgs[$object->type]];
	}
	
	protected function _get_object_property_additionalinfo($object) {
		return $object->get_third_column_info();
	}
	
	protected function _get_object_property_latestcacheupdate($object) {
		global $msg;
		return strtotime($object->cache->last_updated_date) ? formatdate($object->cache->last_updated_date, 1) : $msg["admin_connecteurs_setcateg_latestcacheupdate_never"];
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=edit&id='.$object->id;
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array(
				'onclick' => "document.location=\"".$this->get_edition_link($object)."\""
		);
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	public function get_error_message_empty_list() {
		global $msg, $charset;
		return htmlentities($msg["admin_connecteurs_sets_nosets"], ENT_QUOTES, $charset);
	}
	
	protected function get_label_button_add() {
		global $msg;
		
		return $msg['admin_connecteurs_set_add'];
	}
}