<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_autorites_form.class.php,v 1.1 2021/03/15 09:02:52 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/interface/interface_form.class.php');

class interface_autorites_form extends interface_form {
	
	protected function get_submit_action() {
		switch ($this->table_name) {
			case 'pclassement':
				return "./autorites.php?categ=indexint&sub=pclass&action=update&id_pclass=".$this->object_id;
			default:
				return $this->get_url_base()."&action=update&id=".$this->object_id;
		}
	}
	
	protected function get_delete_action() {
		switch ($this->table_name) {
			case 'pclassement':
				return "./autorites.php?categ=indexint&sub=pclass&action=delete&id_pclass=".$this->object_id;
			default:
				return $this->get_url_base()."&action=delete&id=".$this->object_id;
		}
	}
}