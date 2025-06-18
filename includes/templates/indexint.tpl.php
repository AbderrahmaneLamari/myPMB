<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexint.tpl.php,v 1.46 2021/05/26 22:46:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $indexint_content_form;
global $indexint_replace_content_form;
global $msg, $charset;

$indexint_content_form = "
<div id='el0Child_2' class='row' movable='yes' title=\"".htmlentities($msg['menu_pclassement'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='indexint_pclassement'>".$msg['menu_pclassement']."</label>
	</div>
	<div class='row'>
		!!indexint_pclassement!!
	</div>
</div>
<div id='el0Child_0' class='row' movable='yes' title=\"".htmlentities($msg['indexint_nom'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='indexint_nom'>".$msg['indexint_nom']."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-50em' name='indexint_nom' value=\"!!indexint_nom!!\" data-pmb-deb-rech='1'/>
	</div>
</div>
<div id='el0Child_1' class='row' movable='yes' title=\"".htmlentities($msg['indexint_comment'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='indexint_comment'>".$msg['indexint_comment']."</label>
	</div>
	<div class='row'>
		<textarea id='indexint_comment' class='saisie-80em' name='indexint_comment' cols='62' rows='6' wrap='virtual'>!!indexint_comment!!</textarea>
	</div>
</div>
!!concept_form!!
!!thumbnail_url_form!!
!!aut_pperso!!
<!-- aut_link -->
";

// $indexint_replace_content_form : form remplacement Indexation interne
$indexint_replace_content_form = "
<div class='row'>
	<label class='etiquette' for='par'>$msg[160]</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50emr' id='indexint_libelle' name='indexint_libelle' value=\"\" completion=\"indexint\" autfield=\"n_indexint_id\" autexclude=\"!!id!!\"
    	onkeypress=\"if (window.event) { e=window.event; } else e=event; if (e.keyCode==9) { openPopUp('./select.php?what=indexint&caller=indexint_replace&param1=n_indexint_id&param2=indexint_libelle&no_display=!!id!!&id_pclass=!!id_pclass!!', 'selector'); }\" />

	<input class='bouton' type='button' onclick=\"openPopUp('./select.php?what=indexint&caller=indexint_replace&param1=n_indexint_id&param2=indexint_libelle&no_display=!!id!!&id_pclass=!!id_pclass!!', 'selector')\" title='$msg[157]' value='$msg[parcourir]' />
	<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.indexint_libelle.value=''; this.form.n_indexint_id.value='0'; \" />
	<input type='hidden' name='n_indexint_id' id='n_indexint_id' value='0' />
</div>
<div class='row'>		
	<input id='aut_link_save' name='aut_link_save' type='checkbox' checked='checked' value='1'>".$msg["aut_replace_link_save"]."
</div>
";

