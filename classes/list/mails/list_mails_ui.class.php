<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_mails_ui.class.php,v 1.2.2.5 2021/11/17 13:33:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/mail.class.php');

class list_mails_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'select id_mail from mails';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new mail($row->id_mail);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'from_uri' => 'mail_from_uri',
						'from_name' => 'mail_from_name',
						'date' => 'mail_date',
						'sended' => 'mail_sended',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'from_uri' => array(),
				'from_name' => array(),
				'date_start' => '',
				'date_end' => '',
				'sended' => 'all'
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('from_uri');
		$this->add_selected_filter('from_name');
		$this->add_selected_filter('date');
		$this->add_selected_filter('sended');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'to_name' => 'mail_to_name',
					'to_mail' => 'mail_to_mail',
					'object' => 'mail_object',
					'from_name' => 'mail_from_name',
					'from_mail' => 'mail_from_mail',
					'copy_cc' => 'mail_copy_cc',
					'copy_bcc' => 'mail_copy_bcc',
					'reply_name' => 'mail_reply_name',
					'reply_mail' => 'mail_reply_mail',
					'date' => 'mail_date',
					'sended' => 'mail_sended',
					'error' => 'mail_error',
					'from_uri' => 'mail_from_uri'
			)
		);
	}
	
	/**
	 * Initialisation du tri par défaut appliqué
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date', 'desc');
	}
	
	public function init_applied_group($applied_group=array()) {
		$this->applied_group = array(0 => 'from_uri');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'to_name' :
				case 'to_mail' :
				case 'object' :
				case 'from_name' :
				case 'from_mail' :
				case 'copy_cc' :
				case 'copy_bcc' :
				case 'reply_name' :
				case 'reply_mail' :
				case 'date' :
				case 'sended' :
				case 'error' :
				case 'from_uri' :
					$order .= 'mail_'.$sort_by;
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
		$this->set_filter_from_form('from_uri');
		$this->set_filter_from_form('from_name');
		$this->set_filter_from_form('date_start');
		$this->set_filter_from_form('date_end');
		$this->set_filter_from_form('sended');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('to_name');
		$this->add_column('to_mail');
		$this->add_column('object');
		$this->add_column('from_name');
		$this->add_column('from_mail');
		$this->add_column('copy_cc');
		$this->add_column('reply_name');
		$this->add_column('reply_mail');
		$this->add_column('date');
		$this->add_column('sended');
		$this->add_column('error');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('date', 'datatype', 'datetime');
	}
	
	protected function get_selection_query($type) {
		$query = '';
		switch ($type) {
			case 'from_name':
				$query = 'select distinct mail_from_name as id, mail_from_name as label from mails order by label';
				break;
		}
		return $query;
	}
	
	protected function get_search_filter_from_uri() {
		global $msg;
		$types_uri = mail::get_list_types_uri();
		return $this->get_search_filter_multiple_selection('', 'from_uri', $msg["all"], $types_uri);
	}
	
	protected function get_search_filter_from_name() {
		global $msg;
		return $this->get_search_filter_multiple_selection($this->get_selection_query('from_name'), 'from_name', $msg["all"]);
	}
	
	protected function get_search_filter_date() {
		return $this->get_search_filter_interval_date('date');
	}
	
	protected function get_search_filter_sended() {
		global $msg;
		return "
			<input type='radio' id='".$this->objects_type."_sended_all' name='".$this->objects_type."_sended' value='all' ".($this->filters['sended'] == 'all' ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_sended_all'>".$msg['all']."</label>
			<input type='radio' id='".$this->objects_type."_sended_no' name='".$this->objects_type."_sended' value='no' ".($this->filters['sended'] == 'no' ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_sended_no'>".$msg['mail_sended_no']."</label>
			<input type='radio' id='".$this->objects_type."_sended_yes' name='".$this->objects_type."_sended' value='yes' ".($this->filters['sended'] == 'yes' ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_sended_yes'>".$msg['mail_sended_yes']."</label>";
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(!empty($this->filters['from_uri'])) {
			foreach ($this->filters['from_uri'] as $uri) {
				$filters [] = 'mail_from_uri LIKE "%'.$uri.'%"';
			}
		}
		if(!empty($this->filters['from_name'])) {
			$filters [] = 'mail_from_name IN ("'.implode('","', $this->filters['from_name']).'")';
		}
		if($this->filters['date_start']) {
			$filters [] = 'mail_date >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'mail_date <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if($this->filters['sended'] == 'yes') {
			$filters [] = 'mail_sended = 1';
		} elseif($this->filters['sended'] == 'no') {
			$filters [] = 'mail_sended = 0';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function _get_object_property_to_mail($object) {
		return implode('; ', $object->get_to_mail());
	}
	
	protected function _get_object_property_copy_cc($object) {
		return implode('; ', $object->get_copy_cc());
	}
	
	protected function _get_object_property_copy_bcc($object) {
		return implode('; ', $object->get_copy_bcc());
	}
	
	protected function _get_object_property_sended($object) {
		global $msg;
		if($object->get_sended()) {
			return $msg['mail_sended_yes'];
		} else {
			return $msg['mail_sended_no'];
		}
	}
	
	protected function _get_object_property_from_uri($object) {
		$types_uri = mail::get_list_types_uri();
		foreach ($types_uri as $uri=>$label) {
			if(strpos($object->get_from_uri(), $uri) !== false) {
				return $label;
			}
		}
		return $object->get_from_uri();
	}
	
	protected function _get_query_human_date() {
		return $this->_get_query_human_interval_date('date');
	}
	
	protected function _get_query_human_sended() {
		global $msg;
		if($this->filters['sended'] == 'yes') {
			return $msg['mail_sended_yes'];
		} elseif($this->filters['sended'] == 'no') {
			return $msg['mail_sended_no'];
		}
		return '';
	}
	
	protected function _get_query_human_from_uri() {
		if(!empty($this->filters['from_uri'])) {
			$types_uri = mail::get_list_types_uri();
			foreach ($types_uri as $uri=>$label) {
				foreach ($this->filters['from_uri'] as $from_uri) {
					if(strpos($from_uri, $uri) !== false) {
						return $label;
					}
				}
			}
		}
		return '';
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
		$mail = new mail($id);
		$mail->delete();
	}
}