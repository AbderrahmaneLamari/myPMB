<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_logs_ui.class.php,v 1.1.2.9 2021/11/26 12:52:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/log.class.php');

class list_logs_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = "SELECT * FROM logs";
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new log($row->uniqid_log);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'services' => 'log_services',
						'types' => 'log_types',
						'modules' => 'log_modules',
						'date' => 'log_date',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'services' => array(),
				'types' => array(),
				'modules' => array(),
				'date_start' => '',
				'date_end' => '',
		);
		parent::init_filters($filters);
	}
	
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'service');
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('services');
		$this->add_selected_filter('types');
		$this->add_selected_filter('modules');
		$this->add_selected_filter('date');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'service' => 'log_service',
					'type' => 'log_type',
					'module' => 'log_module',
					'label' => 'log_label',
					'message' => 'log_message',
					'date' => 'log_date',
					'url' => 'log_url',
					'username' => 'log_username',
					'data' => 'log_data',
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date', 'desc');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'service' :
				case 'type' :
				case 'label' :
				case 'message' :
				case 'date' :
				case 'url' :
					$order .= 'log_'.$sort_by;
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
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('services');
		$this->set_filter_from_form('types');
		$this->set_filter_from_form('modules');
		$this->set_filter_from_form('date_start');
		$this->set_filter_from_form('date_end');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('type');
		$this->add_column('module');
		$this->add_column('label');
		$this->add_column('message');
		$this->add_column('date');
		$this->add_column('url');
		$this->add_column('username');
		$this->add_column('data');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('default', 'align', 'left');
		$this->set_setting_column('date', 'align', 'center');
		$this->set_setting_column('date', 'datatype', 'datetime');
	}
	
	protected function get_selection_query($type) {
		$query = '';
		switch ($type) {
			case 'services':
				$query = "SELECT DISTINCT log_service as id, log_service as label FROM logs";
				break;
			case 'types':
				$query = "SELECT DISTINCT log_type as id, log_type as label FROM logs";
				break;
			case 'modules':
				$query = "SELECT DISTINCT log_module as id, log_module as label FROM logs";
				break;
		}
		return $query;
	}
	
	protected function get_search_filter_services() {
		global $msg;
		return $this->get_search_filter_multiple_selection($this->get_selection_query('services'), 'services', $msg["all"]);
	}
	
	protected function get_search_filter_types() {
		global $msg;
		return $this->get_search_filter_multiple_selection($this->get_selection_query('types'), 'types', $msg["all"]);
	}
	
	protected function get_search_filter_modules() {
		global $msg;
		return $this->get_search_filter_multiple_selection($this->get_selection_query('modules'), 'modules', $msg["all"]);
	}
	
	protected function get_search_filter_users() {
		global $msg;
		return $this->get_search_filter_multiple_selection($this->get_selection_query('users'), 'users', $msg["all"]);
	}
	
	protected function get_search_filter_date() {
		return $this->get_search_filter_interval_date('date');
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(is_array($this->filters['services']) && count($this->filters['services'])) {
			$filters [] = 'log_service IN ("'.implode('","', $this->filters['services']).'")';
		}
		if(is_array($this->filters['types']) && count($this->filters['types'])) {
			$filters [] = 'log_type IN ("'.implode('","', $this->filters['types']).'")';
		}
		if(is_array($this->filters['modules']) && count($this->filters['modules'])) {
			$filters [] = 'log_module IN ("'.implode('","', $this->filters['modules']).'")';
		}
		if($this->filters['date_start']) {
			$filters [] = 'log_date >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'log_date <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function _get_object_property_type($object) {
		global $msg;
		if(!empty($msg['log_type_'.$object->get_type()])) {
			return $msg['log_type_'.$object->get_type()];
		} else {
			return $object->get_type();
		}
	}
	
	protected function _get_object_property_module($object) {
		global $msg;
		
		switch ($object->get_module()) {
			case 'docwatch':
				return $msg['dsi_menu_docwatch'];
			default:
				$list_modules_ui = list_modules_ui::get_instance();
				foreach ($list_modules_ui->get_objects() as $module) {
					if($module->get_name() == $object->get_module()) {
						return $module->get_label();
					}
				}
				return $object->get_module();
		}
	}
	
	protected function _get_object_property_username($object) {
		if($object->get_type_user() == 1) {
			return emprunteur::get_name($object->get_num_user(), 1);
		} else {
			return user::get_name($object->get_num_user());
		}
	}
	
	protected function _get_object_property_data($object) {
		$display = "";
		$data=$object->get_data();
		if(is_object($data)){
			if(!empty($data->backtrace->object_name)) {
				$display .= "<b>Object</b> : ".$data->backtrace->object_name;
			} elseif(!empty($data->backtrace->class)) {
				$display .= "<b>Class</b> : ".$data->backtrace->class;
			}
			if(!empty($data->backtrace->function)) {
				if(!empty($display)) $display .= "<br />";
				$display .= "<b>Function</b> : ".$data->backtrace->function;
			}
		}elseif(is_array($data)) {
			if(!empty($data['backtrace']['object_name'])) {
				$display .= "<b>Object</b> : ".$data['backtrace']['object_name'];
			} elseif(!empty($data['backtrace']['class'])) {
				$display .= "<b>Class</b> : ".$data['backtrace']['class'];
			}
			if(!empty($data['backtrace']['function'])) {
				if(!empty($display)) $display .= "<br />";
				$display .= "<b>Function</b> : ".$data['backtrace']['function'];
			}
		}
		return $display;
	}
	
	protected function _get_query_human_date() {
		return $this->_get_query_human_interval_date('date');
	}
	
	protected function init_default_selection_actions() {
		global $msg;
		
		parent::init_default_selection_actions();
		$delete_link = array(
				'href' => static::get_controller_url_base()."&action=list_delete"
		);
		$this->add_selection_action('delete', $msg['63'], 'interdit.gif', $delete_link);
	}
	
	public static function delete_object($id) {
		log::delete($id);
	}
}