<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_tabs_account_ui.class.php,v 1.2 2022/09/27 09:41:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_tabs_account_ui extends list_tabs_ui {
	
	protected function _init_tabs() {
		global $PMBuseremail;
		
		$this->add_tab('33', '', '933');
		if($PMBuseremail) {
			$this->add_tab('33', 'mails', 'mail_configuration', 'configuration', '&action=edit&name='.$PMBuseremail);
		}
	}
}