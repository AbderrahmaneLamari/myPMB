<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_editorial_publications_states.tpl.php,v 1.6 2021/03/03 07:57:00 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $cms_editorial_publication_state_content_form, $msg;

$cms_editorial_publication_state_content_form ="
<div class='row'>
	<div class='colonne3'>
		<label for='cms_editorial_publication_state_label'>".$msg['editorial_content_publication_state_label']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='text' name='cms_editorial_publication_state_label' value='!!label!!'/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='cms_editorial_publication_state_class_html'>".$msg['editorial_content_publication_state_class_html']."</label>
	</div>
	<div class='colonne_suite'>
		!!class_html!!
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='cms_editorial_publication_state_visible'>".$msg['editorial_content_publication_state_visible']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='cms_editorial_publication_state_visible' value='1' !!visible!!/>
	</div>
</div>
<div class='row'>
	<div class='colonne3'>
		<label for='cms_editorial_publication_state_visible_abo'>".$msg['editorial_content_publication_state_visible_abo']."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' name='cms_editorial_publication_state_visible_abo' value='1' !!visible_abo!!/>
	</div>
</div>";
