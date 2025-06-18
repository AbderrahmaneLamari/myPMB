<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_request_priorities.tpl.php,v 1.3 2021/01/20 07:27:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $scan_request_priority_content_form, $current_module, $msg;

$scan_request_priority_content_form ="
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_priority_label'>".$msg['editorial_content_publication_state_label']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='text' name='scan_request_priority_label' value='!!label!!'/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='scan_request_priority_weight'>".$msg['scan_request_priority_weight']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='text' name='scan_request_priority_weight' value='!!weight!!'/>
	</div>
</div>";
