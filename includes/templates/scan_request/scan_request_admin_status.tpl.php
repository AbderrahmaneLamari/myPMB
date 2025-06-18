<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_request_admin_status.tpl.php,v 1.4 2021/01/20 07:27:20 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $scan_request_status_content_form, $msg;

$scan_request_status_content_form ="
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_status_label'>".$msg['editorial_content_publication_state_label']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='text' name='scan_request_status_label' id='scan_request_status_label' value='!!label!!'/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label>".$msg['editorial_content_publication_state_class_html']."</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_status_visible'>".$msg['editorial_content_publication_state_visible']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='scan_request_status_visible' id='scan_request_status_visible' value='1' !!visible!!/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_cancelable'>".$msg['scan_request_cancelable']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='scan_request_cancelable' id='scan_request_cancelable' value='1' !!cancelable!!/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_infos_editable'>".$msg['scan_request_infos_editable']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='scan_request_infos_editable' id='scan_request_infos_editable' value='1' !!infos_editable!!/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_is_closed'>".$msg['scan_request_is_closed']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='scan_request_is_closed' id='scan_request_is_closed' value='1' !!is_closed!!/>
	</div>
</div>
";
