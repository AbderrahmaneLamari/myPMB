<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_editions_states_ui.class.php,v 1.4.2.1 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/editions_state.class.php");

class list_editions_states_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT id_editions_state FROM editions_states LEFT JOIN procs_classements ON idproc_classement=editions_state_num_classement';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new editions_state($row->id_editions_state);
	}
	
	protected function _get_query_order() {
	    if ($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'libproc_classement':
					$order .= 'libproc_classement,editions_state_name';
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
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->settings['objects']['default']['display_mode'] = 'expandable_table';
		$this->settings['grouped_objects']['level_1']['display_mode'] = 'expandable_table';
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'execute', 'name'
		);
	}
	
	protected function init_default_pager() {
		parent::init_default_pager();
		$this->pager['all_on_page'] = true;
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('libproc_classement');
	    $this->add_applied_sort('name');
	}
	
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'libproc_classement');
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'name' => 'editions_state_form_name',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
				'name' => '',
		);
		parent::init_filters($filters);
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('name');
		parent::set_filters_from_form();
	}
	
	protected function get_search_filter_name() {
		global $msg;
		
		return "<input class='saisie-80em' id='".$this->objects_type."_name' type='text' name='".$this->objects_type."_name' value=\"".$this->filters['name']."\" title='$msg[3001]' />";
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'name' => 'editions_state_form_name',
						'libproc_classement' => 'proc_clas_lib'
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column_execute(); //TODO : width='10'
		$this->add_column('name');
	}
	
	protected function add_column_execute() {
		global $msg;
		
		$this->columns[] = array(
				'property' => 'execute',
				'label' => '',
				'html' => "<input class='bouton' type='button' value=' $msg[708] ' onClick=\"document.location='".static::get_controller_url_base()."&sub=tab&action=show&id=!!id!!'\" />",
				'exportable' => false
		);
	}
	
	protected function get_button_add() {
		global $msg;
	
		return $this->get_button('add', $msg['editions_state_add']);
	}
	
	protected function get_grouped_label($object, $property) {
		global $msg;
		
		$grouped_label = '';
		switch($property) {
			case 'libproc_classement':
				if(!empty($object->classement)) {
					$procs_classement = new procs_classement($object->classement);
					$grouped_label = $procs_classement->libelle;
				} else {
					$grouped_label = $msg['proc_clas_aucun'];
				}
				break;
			default:
				$grouped_label = parent::get_grouped_label($object, $property);
				break;
		}
		return $grouped_label;
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'name':
				$content .= "<strong>".$object->name."</strong><br />
					<small>".$object->comment."&nbsp;</small>";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}

	protected function get_display_cell($object, $property) {
		switch ($property) {
			case 'name':
				$attributes = array(
					'onclick' => "document.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->id."\""
				);
				break;
			default:
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_display_left_actions() {
		return $this->get_button_add();
	}
	
	public static function get_controller_url_base() {
		global $base_path;
	
		return $base_path.'/edit.php?categ=state';
	}
}