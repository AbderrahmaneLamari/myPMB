<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: selector_model.tpl.php,v 1.4.4.1 2023/07/04 09:16:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $msg, $charset, $selector_display_modes_content_form;

$selector_display_modes_content_form = "
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette' for='selector_display_modes_record'>".htmlentities($msg['type_mono'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' id='selector_display_modes_record' name='selector_display_modes[record]' value='popup' !!display_mode_record!! />
		".htmlentities($msg['selector_display_mode_force_popup'], ENT_QUOTES, $charset)."
	</div>
</div>
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette' for='selector_display_modes_serial'>".htmlentities($msg['type_serial'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' id='selector_display_modes_serial' name='selector_display_modes[serial]' value='popup' !!display_mode_serial!! />
		".htmlentities($msg['selector_display_mode_force_popup'], ENT_QUOTES, $charset)."
	</div>
</div>
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette' for='selector_display_modes_bulletin'>".htmlentities($msg['type_bull'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' id='selector_display_modes_bulletin' name='selector_display_modes[bulletin]' value='popup' !!display_mode_bulletin!! />
		".htmlentities($msg['selector_display_mode_force_popup'], ENT_QUOTES, $charset)."
	</div>
</div>
<div class='row'>
	<div class='colonne4'>
		<label class='etiquette' for='selector_display_modes_analysis'>".htmlentities($msg['type_art'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='colonne_suite'>
		<input type='checkbox' id='selector_display_modes_analysis' name='selector_display_modes[analysis]' value='popup' !!display_mode_analysis!! />
		".htmlentities($msg['selector_display_mode_force_popup'], ENT_QUOTES, $charset)."
	</div>
</div>";

?>