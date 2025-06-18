<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mail.php,v 1.9.2.1 2021/12/08 12:59:07 dgoron Exp $

// définition du minimum nécéssaire 
$base_path=".";                            
$base_auth = "";  
$base_title = "Mail";

require_once ("$base_path/includes/init.inc.php");  

global $class_path, $type_mail;

require_once($class_path."/modules/module_mail.class.php");

switch($type_mail) {
	case 'mail_relance_adhesion':
		if(checkUser('PhpMyBibli', EDIT_AUTH) || checkUser('PhpMyBibli', CIRCULATION_AUTH)) {
			$module_mail = module_mail::get_instance();
			$module_mail->proceed_mail_relance_adhesion();
		}
		break;
	case 'mail_retard':
		if(checkUser('PhpMyBibli', EDIT_AUTH) || checkUser('PhpMyBibli', CIRCULATION_AUTH)) {
			$module_mail = module_mail::get_instance();
			$module_mail->proceed_mail_retard();
		}
		break;
	case 'mail_prets':
		if(checkUser('PhpMyBibli', EDIT_AUTH) || checkUser('PhpMyBibli', CIRCULATION_AUTH)) {
			$module_mail = module_mail::get_instance();
			$module_mail->proceed_mail_prets();
		}
		break;
	case 'mail_retard_groupe':
		if(checkUser('PhpMyBibli', EDIT_AUTH)) {
			$module_mail = module_mail::get_instance();
			$module_mail->proceed_mail_retard_groupe();
		}
		break;
	default:
		break;
}

pmb_mysql_close();
