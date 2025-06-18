<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_subtabs_account_ui.class.php,v 1.1.4.3 2021/11/16 13:53:08 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_subtabs_account_ui extends list_subtabs_ui {
	
	public function get_title() {
		global $msg;
		
		$title = "";
		switch (static::$categ) {
			case 'lists':
				$title .= $msg['lists'];
				break;
			case 'tabs':
				$title .= $msg['tabs'];
				break;
			case 'modules':
				$title .= $msg['admin_menu_modules'];
				break;
			case 'selectors':
				$title .= $msg['selectors'];
				break;
			case 'pdf':
				$title .= $msg['letters'];
				break;
			case 'mail':
				$title .= $msg['mails'];
				break;
			case 'translations':
				$title .= $msg['translations'];
				break;
			case 'logs':
				$title .= $msg['logs'];
				break;
			case 'mails':
				$title .= $msg['mails'];
				break;
			case 'mails_waiting':
				$title .= $msg['mails_waiting'];
				break;
			case 'favorites':
			default:
				$title .= $msg['934']." ".SESSlogin;
				break;
		}
		return $title;
	}
	
	public function get_sub_title() {
		global $sub;
		switch (static::$categ) {
			case 'tabs':
				if(empty($sub)) $sub ='admin';
				$list_modules_ui = new list_modules_ui();
				$objects = $list_modules_ui->get_objects();
				foreach ($objects as $object) {
					if($sub == $object->get_name()) {
						return $object->get_label();
					}
				}
				break;
			default:
				return '';
		}
	}
	
	protected function _init_subtabs() {
		switch (static::$categ) {
			case 'tabs':
				$list_modules_ui = new list_modules_ui();
				$objects = $list_modules_ui->get_objects();
				foreach ($objects as $object) {
					$this->add_subtab($object->get_name(), $object->get_label());
				}
				break;
		}
	}
}