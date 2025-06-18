<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: editeurs.tpl.php,v 1.55 2021/05/26 23:12:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $publisher_content_form, $collections_list_tpl, $publisher_replace_content_form, $msg;
global $charset;

// $publisher_content_form : form saisie éditeur
$publisher_content_form = "
<!-- nom -->
<div id='el0Child_0' movable='yes' class='row' title=\"".htmlentities($msg['editeur_nom'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_nom'>".$msg["editeur_nom"]."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' name='ed_nom' value=\"!!ed_nom!!\" data-pmb-deb-rech='1'/>
	</div>
</div>
<!-- adr1 -->
<div id='el0Child_1' movable='yes' class='row' title=\"".htmlentities($msg['editeur_adr1'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_adr1'>".$msg["editeur_adr1"]."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' name='ed_adr1' value=\"!!ed_adr1!!\" />
	</div>
</div>
<!-- adr2 -->
<div id='el0Child_2' movable='yes' class='row' title=\"".htmlentities($msg['editeur_adr2'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_adr2'>".$msg["editeur_adr2"]."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' name='ed_adr2' value=\"!!ed_adr2!!\" />
	</div>
</div>

<div id='el0Child_3' class='row'>
	<!-- cp -->
	<div id='el0Child_3_a' movable='yes' class='colonne2' title=\"".htmlentities($msg['editeur_cp'], ENT_QUOTES, $charset)."\">
		<div class='row'>
			<label class='etiquette' for='form_cp'>".$msg["editeur_cp"]."</label>
		</div>
		<div class='row'>
			<input type='text' class='saisie-10em' name='ed_cp' value=\"!!ed_cp!!\" maxlength='10' />
		</div>
	</div>
	<!-- ville -->
	<div id='el0Child_3_b' movable='yes' class='colonne2' title=\"".htmlentities($msg['editeur_ville'], ENT_QUOTES, $charset)."\">
		<div class='row'>
			<label class='etiquette' for='form_ville'>".$msg["editeur_ville"]."</label>
		</div>
		<div class='row'>
			<input type='text' class='saisie-20em' name='ed_ville' value=\"!!ed_ville!!\" />
		</div>
	</div>
</div>

<!-- pays -->
<div id='el0Child_4' movable='yes' class='row' title=\"".htmlentities($msg[146], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_pays'>$msg[146]</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-20em' name='ed_pays' value=\"!!ed_pays!!\" />
	</div>
</div>
<!-- web -->
<div id='el0Child_5' movable='yes' class='row' title=\"".htmlentities($msg['editeur_web'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette' for='form_web'>".$msg["editeur_web"]."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-80em' name='ed_web' id='ed_web' value=\"!!ed_web!!\" />
		<input class='bouton' type='button' onClick=\"check_link('ed_web')\" title='".$msg["CheckLink"]."' value='".$msg["CheckButton"]."' />
	</div>
</div>
<div id='el0Child_7' movable='yes' class='row' title=\"".htmlentities($msg['acquisition_ach_fou2'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette'>".htmlentities($msg['acquisition_ach_fou2'], ENT_QUOTES, $charset)."</label>
	</div>
	<div class='row'>
		<input type='text' id='lib_fou' name='lib_fou' tabindex='1' value='!!lib_fou!!' completion='fournisseur' autfield='id_fou' autocomplete='off' class='saisie-30emr' />
		<input type='button' class='bouton' tabindex='1' value='".$msg['parcourir']."' onclick=\"openPopUp('./select.php?what=fournisseur&caller=saisie_editeur&param1=id_fou&param2=lib_fou&param3=adr_fou&id_bibli=&deb_rech='+".pmb_escape()."(this.form.lib_fou.value), 'selector'); \" />
		<input type='button' class='bouton' value='".$msg['raz']."' onclick=\"this.form.lib_fou.value=''; this.form.id_fou.value='0'; \" />
		<input type='hidden' id='id_fou' name='id_fou' value='!!id_fou!!' />
	</div>
</div>
<!-- Commentaire -->
<div id='el0Child_6' movable='yes' class='row' title=\"".htmlentities($msg['ed_comment'], ENT_QUOTES, $charset)."\">
	<div class='row'>
		<label class='etiquette'>".$msg["ed_comment"]."</label>
	</div>
	<div class='row'>
		<textarea class='saisie-80em' name='ed_comment' cols='62' rows='4' wrap='virtual'>!!ed_comment!!</textarea>
	</div>
</div>
!!concept_form!!
!!thumbnail_url_form!!
!!aut_pperso!!
<!-- aut_link -->
<div id='el0Child_8' movable='yes' class='row' title=\"".htmlentities($msg['136'], ENT_QUOTES, $charset)."\">
	!!liaisons_collections!!
</div>
";

$collections_list_tpl = "
<div id='el_0Parent' class='parent' >
	<h3>
    	<img src='".get_url_icon('plus.gif')."' class='img_plus align_bottom' name='imEx' id='el_0Img' title='$msg[categ_links]' border='0' onClick=\"expandBase('el_0', true); return false;\" />
    	".$msg['136']."
    </h3>
</div>
<div id='el_0Child' class='child'>
    <!-- collections_list -->
</div>";

// $publisher_replace_content_form : form remplacement éditeur
$publisher_replace_content_form = "
<div class='row'>
	<label class='etiquette' for='par'>$msg[160]</label>
</div>
<div class='row'>
	<input type='text' class='saisie-50emr' id='ed_libelle' name='ed_libelle' value=\"\" completion=\"publishers\" autfield=\"ed_id\" autexclude=\"!!id!!\"
   	onkeypress=\"if (window.event) { e=window.event; } else e=event; if (e.keyCode==9) { openPopUp('./select.php?what=editeur&caller=publisher_replace&p1=ed_id&p2=ed_libelle&no_display=!!id!!', 'selector'); }\" />
	<input class='bouton' type='button' onclick=\"openPopUp('./select.php?what=editeur&caller=publisher_replace&p1=ed_id&p2=ed_libelle&no_display=!!id!!', 'selector')\" title='$msg[157]' value='$msg[parcourir]' />
	<input type='button' class='bouton' value='$msg[raz]' onclick=\"this.form.ed_libelle.value=''; this.form.ed_id.value='0'; \" />
	<input type='hidden' name='ed_id' id='ed_id'>
</div>
<div class='row'>
	<input id='aut_link_save' name='aut_link_save' type='checkbox' checked='checked' value='1'>".$msg["aut_replace_link_save"]."
</div>
";

