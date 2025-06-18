<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ticket-pret-electro.inc.php,v 1.3.2.1 2021/07/08 12:04:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $class_path, $msg, $id_empr, $id_groupe;

require_once("$base_path/circ/pret_func.inc.php");
require_once("$class_path/emprunteur.class.php");
// liste des prêts et réservations

if (isset($id_groupe)) {
	$res_envoi = electronic_ticket_groupe($id_groupe);
	
	$myGroup = new group($id_groupe);
	$to_mail = $myGroup->mail_resp;
	$mail_content = get_groupe_content_electronic_loan_ticket($id_groupe);
} else {
	$res_envoi = electronic_ticket($id_empr);
	
	$to_mail = emprunteur::get_mail_empr($id_empr);
	$mail_content = get_empr_content_electronic_loan_ticket($id_empr);
}
if ($res_envoi) {
	echo "<h3>".sprintf($msg["mail_retard_succeed"],$to_mail)."</h3><br /><a href=\"\" onClick=\"self.close(); return false;\">".$msg["mail_retard_close"]."</a><br /><br />".nl2br($mail_content);
} else {
	echo "<h3>".sprintf($msg["mail_retard_failed"],$to_mail)."</h3><br /><a href=\"\" onClick=\"self.close(); return false;\">".$msg["mail_retard_close"]."</a>";
}