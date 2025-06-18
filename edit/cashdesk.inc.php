<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cashdesk.inc.php,v 1.3 2021/04/21 08:10:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $dest;

require_once($class_path."/cashdesk/cashdesk_list.class.php");

$cashdesk_list=new cashdesk_list();

switch($dest) {
	case "TABLEAU":
		$cashdesk_list->get_excel_summarize();
		break;
	case "TABLEAUHTML":
		//le titre de la page		
		echo "<h1>".$msg["1120"].": ".$msg["cashdesk_edition_menu"]."</h1>";
		print $cashdesk_list->get_html_summarize();  
		print"</body></html>";
		exit;
		break;
	default:
		//le titre de la page
		print $cashdesk_list->get_form_summarize();
		break;
}
