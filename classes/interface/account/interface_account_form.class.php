<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: interface_account_form.class.php,v 1.2.4.1 2023/03/24 09:28:09 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/interface/interface_form.class.php');

class interface_account_form extends interface_form {
	
	protected function get_action_delete_label() {
		global $msg;
		switch ($this->table_name) {
			case 'lists':
			case 'modules':
			case 'selectors':
			case 'tabs':
			case 'forms':
				return $msg['initialize'];
			default:
				return parent::get_action_delete_label();
		}
	}
	
	protected function get_display_cancel_action() {
		switch ($this->table_name) {
			case 'mails_configuration':
				return '';
			default:
				return parent::get_display_cancel_action();
		}
	}
}