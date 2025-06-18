<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_mails_waiting_ui.class.php,v 1.1.4.3 2021/11/17 13:33:29 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/mail_waiting.class.php');

class list_mails_waiting_ui extends list_mails_ui {
	
	protected function _get_query_base() {
		$query = 'select id_mail from mails_waiting';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new mail_waiting($row->id_mail);
	}
	
	protected function init_default_columns() {
		$this->add_column('to_name');
		$this->add_column('to_mail');
		$this->add_column('object');
		$this->add_column('from_name');
		$this->add_column('from_mail');
		$this->add_column('copy_cc');
		$this->add_column('reply_name');
		$this->add_column('reply_mail');
		$this->add_column('date');
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
					$order .= 'mail_waiting_'.$sort_by;
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
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['date_start']) {
			$filters [] = 'mail_waiting_date >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'mail_waiting_date <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	public static function delete_object($id) {
		$mail = new mail_waiting($id);
		$mail->delete();
	}
}