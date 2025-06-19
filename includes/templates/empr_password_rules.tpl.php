<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: empr_password_rules.tpl.php,v 1.2.4.2 2023/06/13 09:19:25 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $admin_empr_password_rules_tpl;



$admin_empr_password_rules_tpl['form'] = "
<form class='form-admin' name='admin_empr_password_rules' method='post' id='admin_empr_password_rules' action='./admin.php?categ=empr&sub=password_rules&action=save'>
	<h3>".$msg['admin_empr_password_rules_form_title']."</h3>
    <span style='!!admin_empr_password_no_rules_ext_auth!!' class='erreur'>".$msg['admin_empr_password_no_rules_ext_auth']."</span>
	<div class='form-contenu'>
		<table class='modern'>
			<thead id='empr_renewal_form_fixed_header'>
				<tr>
					<th>".$msg['admin_empr_password_rules_desc']."</th>
					<th>".$msg['admin_empr_password_rules_value']."</th>
					<th>".$msg['admin_empr_password_rules_enabled']."</th>
				</tr>
			</thead>
			<tbody>
				<!-- rows -->
			</tbody>
		</table>
	</div>
	<div class='row'>
		<input class='bouton' type='submit' value='".$msg['77']."' />
	</div>
</form>";

$admin_empr_password_rules_tpl['row'] = "
<tr>
	<td><!-- desc --></td>
	<td><!-- var --></td>
	<td><!-- enabled --></td>
</tr>
";

$admin_empr_password_rules_tpl['var']['integer'] = "
<input type='number' id='!!id!!' name='!!name!!' class='saisie-5em' value='!!value!!' min='1' step='1' autocomplete='off' />";
$admin_empr_password_rules_tpl['var']['string'] = "
<input type='text' id='!!id!!' name='!!name!!' class='saisie-50em' value='!!value!!' autocomplete='off' />
";
$admin_empr_password_rules_tpl['var']['textarea'] = "
<textarea rows='5' cols='90' id='!!id!!' name='!!name!!' >!!value!!</textarea>
";
$admin_empr_password_rules_tpl['checkbox'] = "
<input type='checkbox' id='!!id!!' name='!!name!!' value='1' !!checked!! />";