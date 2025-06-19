<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_catalog_form.class.php,v 1.3 2022/05/12 06:53:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/interface/interface_form.class.php');

class interface_catalog_form extends interface_form {
	
	protected function get_display_cancel_action() {
		global $action;
		
		switch ($this->table_name) {
			case 'caddie':
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
	
	protected function get_submit_action() {
		switch ($this->table_name) {
			case 'caddie':
				if($this->object_id) {
					return $this->get_url_base()."&action=save_cart&idcaddie=".$this->object_id;
				} else {
					return $this->get_url_base()."&action=valid_new_cart";
				}
			case 'etagere':
				if($this->object_id) {
					return $this->get_url_base()."&action=save_etagere&idetagere=".$this->object_id;
				} else {
					return $this->get_url_base()."&action=valid_new_etagere";
				}
			case 'etagere_caddie':
				return $this->get_url_base()."&action=save_etagere";
			default:
				return $this->get_url_base()."&action=update&id=".$this->object_id;
		}
	}
	
	protected function get_duplicate_action() {
		switch ($this->table_name) {
			case 'caddie':
				return $this->get_url_base()."&action=duplicate_cart&idcaddie=".$this->object_id;
			case 'etagere':
				return $this->get_url_base()."&action=duplicate_etagere&idetagere=".$this->object_id;
			default:
				return parent::get_duplicate_action();
		}
	}
	
	protected function get_delete_action() {
		switch ($this->table_name) {
			case 'caddie':
				return $this->get_url_base()."&action=del_cart&idcaddie=".$this->object_id;
			case 'etagere':
				return $this->get_url_base()."&action=del_etagere&idetagere=".$this->object_id;
			default:
				return $this->get_url_base()."&action=delete&id=".$this->object_id;
		}
	}
}