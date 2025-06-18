<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: series.tpl.php,v 1.45 2021/05/26 22:46:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $serie_content_form;
global $serie_replace_content_form;
global $msg, $charset;

$serie_content_form = "
<div id='el0Child_0' class='row' movable='yes' title=\"".htmlentities($msg['233'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_nom'>$msg[233]</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' name='serie_nom' value=\"!!serie_nom!!\" data-pmb-deb-rech='1'/>
	</div>
</div>
!!concept_form!!
!!thumbnail_url_form!!
<!-- aut_link -->	
!!aut_pperso!!
";

// $serie_replace_content_form : form remplacement titre de série
$serie_replace_content_form = "
<div class='row'>
	<label class='etiquette' for='par'>$msg[160]</label>
</div>
<div class='row'>
	<input type='text' class='saisie-80emr' id='serie_libelle' name='serie_libelle' value=\"\" completion=\"serie\" autfield=\"n_serie_id\" autexclude=\"!!id!!\"
    	onkeypress=\"if (window.event) { e=window.event; } else e=event; if (e.keyCode==9) { openPopUp('./select.php?what=serie&caller=serie_replace&param1=n_serie_id&param2=serie_libelle&no_display=!!id!!', 'selector'); }\" />

	<input class='bouton' type='button' onclick=\"openPopUp('./select.php?what=serie&caller=serie_replace&param1=n_serie_id&param2=serie_libelle&no_display=!!id!!', 'selector')\" title='$msg[157]' value='$msg[parcourir]' />
	<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.serie_libelle.value=''; this.form.n_serie_id.value='0'; \" />
	<input type='hidden' name='n_serie_id' id='n_serie_id' value='0' />
</div>
<div class='row'>		
	<input id='aut_link_save' name='aut_link_save' type='checkbox' checked='checked' value='1'>".$msg["aut_replace_link_save"]."
</div>
";

