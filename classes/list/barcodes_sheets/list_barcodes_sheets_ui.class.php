<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_barcodes_sheets_ui.class.php,v 1.1.2.4 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/barcodes_sheets/barcodes_sheet.class.php");

class list_barcodes_sheets_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT id_barcodes_sheet FROM barcodes_sheets';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new barcodes_sheet($row->id_barcodes_sheet);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters = array('main_fields' => array());
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'label' => 'barcodes_sheet_label',
					'page_format' => 'barcodes_sheet_page_format',
					'page_orientation_label' => 'barcodes_sheet_page_orientation',
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('label');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'id':
					$order .= 'id_barcodes_sheet';
					break;
				case 'label' :
					$order .= 'barcodes_sheet_'.$sort_by;
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}	
	}
		
	protected function get_button_add() {
		global $msg;
		
		return $this->get_button('add', $msg['ajouter']);
	}
	
	protected function get_display_left_actions() {
		return $this->get_button_add();
	}
	
	protected function init_default_columns() {
		$this->add_column('label');
		$this->add_column('page_format');
		$this->add_column('page_orientation_label');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('label', 'align', 'left');
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
		$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->get_id()."\"";
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
}