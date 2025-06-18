<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_procs_classements_ui.class.php,v 1.6.2.1 2021/09/21 16:43:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_procs_classements_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT idproc_classement,libproc_classement FROM procs_classements';
		return $query;
	}
	
	protected function _get_query_order() {
	    if ($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'label':
					$order .= 'libproc_classement';
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
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('label');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'label' => 'proc_clas_lib',
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column('label');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('label', 'align', 'left');
		$this->set_setting_column('label', 'text', array('strong' => true));
	}
	
	protected function get_button_add() {
		global $msg;
	
		return $this->get_button('add', $msg['proc_clas_bt_add']);
	}
	
	protected function _get_object_property_label($object) {
		return $object->libproc_classement;
	}

	protected function get_display_cell($object, $property) {
		switch ($property) {
			default:
				$attributes = array(
				'onclick' => "document.location=\"".static::get_controller_url_base()."&action=modif&id=".$object->idproc_classement."\""
				);
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_display_left_actions() {
		return $this->get_button_add();
	}
}