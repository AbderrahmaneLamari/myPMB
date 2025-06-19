<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_circ_form.class.php,v 1.2 2022/05/12 06:53:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/interface/interface_form.class.php');

class interface_circ_form extends interface_form {
	
	protected function get_display_cancel_action() {
		global $action;
		
		switch ($this->table_name) {
			case 'empr_caddie':
				switch ($action) {
					case 'new_cart':
					case 'duplicate_cart':
						return "<input type='button' class='bouton' name='cancel_button' id='cancel_button' value='".$this->get_action_cancel_label()."'  onclick=\"history.go(-1);\"  />";
				}
				return parent::get_display_cancel_action();
			default:
				return parent::get_display_cancel_action();
		}
	}
	
	protected function get_cancel_action() {
		switch ($this->table_name) {
			case 'groupe':
				if(!empty($this->object_id)) {
					return $this->get_url_base()."&action=showgroup&groupID=".$this->object_id;
				} else {
					return parent::get_cancel_action();
				}
			default:
				return parent::get_cancel_action();
		}
	}
	
	protected function get_submit_action() {
		switch ($this->table_name) {
			case 'groupe':
				return $this->get_url_base()."&action=update".(!empty($this->object_id) ? "&groupID=".$this->object_id : "");
			case 'empr_caddie':
				if($this->object_id) {
					return $this->get_url_base()."&action=save_cart&idemprcaddie=".$this->object_id;
				} else {
					return $this->get_url_base()."&action=valid_new_cart";
				}
			default:
				return parent::get_submit_action();
		}
	}
	
	protected function get_duplicate_action() {
		switch ($this->table_name) {
			case 'empr_caddie':
				return $this->get_url_base()."&action=duplicate_cart&idemprcaddie=".$this->object_id;
			default:
				return parent::get_duplicate_action();
		}
	}
	
	protected function get_delete_action() {
		switch ($this->table_name) {
			case 'groupe':
				return $this->get_url_base()."&action=delgroup&groupID=".$this->object_id;
			case 'empr_caddie':
				return $this->get_url_base()."&action=del_cart&idemprcaddie=".$this->object_id;
			default:
				return parent::get_delete_action();
		}
	}
	
	protected function get_js_script_error_label() {
		global $msg;
		
		switch ($this->table_name) {
			case 'groupe':
				return $msg['915'];
			default:
				return parent::get_js_script_error_label();
		}
	}
	
}