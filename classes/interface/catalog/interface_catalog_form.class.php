<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_catalog_form.class.php,v 1.2 2021/03/24 08:33:56 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/interface/interface_form.class.php');

class interface_catalog_form extends interface_form {
	
	protected function get_submit_action() {
		switch ($this->table_name) {
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
			case 'etagere':
				return $this->get_url_base()."&action=duplicate_etagere&idetagere=".$this->object_id;
			default:
				return $this->get_url_base()."&action=duplicate&id=".$this->object_id;
		}
	}
	
	protected function get_delete_action() {
		switch ($this->table_name) {
			case 'etagere':
				return $this->get_url_base()."&action=del_etagere&idetagere=".$this->object_id;
			default:
				return $this->get_url_base()."&action=delete&id=".$this->object_id;
		}
	}
}