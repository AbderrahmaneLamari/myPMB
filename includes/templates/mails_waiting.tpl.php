<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mails_waiting.tpl.php,v 1.3 2021/03/12 13:21:14 dgoron Exp $

global $mails_waiting_content_form_tpl, $msg;

$mails_waiting_content_form_tpl="
<div class='row'>
	<label class='etiquette' for='mails_waiting_attachments'>".$msg['mails_waiting_attachments']."</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50em' name='mails_waiting_attachments' id='mails_waiting_attachments' value='!!attachments!!' />
</div>
<div class='row'>
	<label class='etiquette' for='mails_waiting_max_by_send'>".$msg['mails_waiting_max_by_send']."</label>
</div>
<div class='row'>
	<input type='number' class='saisie-5em' name='mails_waiting_max_by_send' id='mails_waiting_max_by_send' value='!!max_by_send!!' />
</div>
";