<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: attachments.inc.php,v 1.1 2020/04/02 08:03:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $action;
global $from, $filename;
require_once($class_path."/files_gestion.class.php");

$img=new files_gestion('attachments');	

switch($action) {
	case 'upload':
		$img->upload($from);
	break;	
	case 'delete':
		$img->delete(urldecode(stripslashes($filename)));
	break;
	default:
	break;
}

print $img->get_list("admin.php?categ=mailtpl&sub=attachments");
print $img->get_error();