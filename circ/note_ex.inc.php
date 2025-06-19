<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: note_ex.inc.php,v 1.11 2021/12/09 09:00:50 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// ajout message sur un exemplaire
global $class_path, $include_path, $action, $charset;
global $expl_msg_form, $cb, $message_content, $f_ex_comment, $id;

$id = intval($id);

require_once("$class_path/expl.class.php");
require_once("$include_path/templates/expl.tpl.php");
require_once("$class_path/mono_display.class.php");
require_once("$class_path/serial_display.class.php");

if(!$action) {
	// si l'action n'est pas définie, afficher le form de saisie message
	$expl = new exemplaire($cb);
	if($expl->id_notice) {
		$notice = new mono_display($expl->id_notice, 0);
		$expl_msg_form = str_replace('!!notice!!', $notice->header, $expl_msg_form);
	} elseif ($expl->id_bulletin) {
		$notice = new bulletinage_display($expl->id_bulletin);
		$expl_msg_form = str_replace('!!notice!!', $notice->display, $expl_msg_form);		
	}
	$expl_msg_form = str_replace('!!message!!', htmlentities($expl->note	,ENT_QUOTES, $charset), $expl_msg_form);
	$expl_msg_form = str_replace('!!comment!!', htmlentities($expl->expl_comment	,ENT_QUOTES, $charset), $expl_msg_form);
	print pmb_bidi($expl_msg_form);
} else {
	// action définie : mettre à jour le message pour l'exemplaire
	$query = "UPDATE exemplaires SET expl_note='$message_content', expl_comment='$f_ex_comment' WHERE expl_id=$id ";
	pmb_mysql_query($query); 
	$form_cb_expl=$cb;
	include('./circ/visu_ex.inc.php');
}
