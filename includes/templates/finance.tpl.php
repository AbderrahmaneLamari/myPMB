<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: finance.tpl.php,v 1.16 2021/03/12 13:21:14 dgoron Exp $
// Formulaires gestion financière

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $finance_abts_content_form, $finance_amende_content_form, $finance_amende_relance_content_form, $msg;

//Abonnements
$finance_abts_content_form="
<div class='row'>
	<label class='etiquette' for='typ_abt_libelle'>$msg[103]</label>
</div>
<div class='row'>
	<input type=text name='typ_abt_libelle' id='typ_abt_libelle' value='!!libelle!!' maxlength='255' class='saisie-50em' />
</div>
<div class='row'>
	<label class='etiquette' for='commentaire'>".$msg["type_abts_commentaire"]."</label>
</div>
<div class='row'>
	<textarea name='commentaire' id='commentaire' rows='3' cols='80' wrap='virtual'>!!commentaire!!</textarea>
</div>
<div style='display:none'>
<div class='row'>
	<label class='etiquette' for='prepay'>".$msg["type_abts_prepay"]."</label>
</div>
<div class='row'>
	<input type='checkbox' name='prepay' id='prepay' value='1' !!prepay_checked!! />
</div>
<div class='row'>
	<label class='etiquette' for='prepay_deflt_mnt'>".$msg["type_abts_prepay_dflt"]."</label>
</div>
<div class='row'>
	<input type=text name='prepay_deflt_mnt' id='prepay_deflt_mnt' value='!!prepay_deflt_mnt!!' maxlength='6' class='saisie-10em' />
</div>
</div>
<div class='row'>
	<label class='etiquette' for='tarif'>".$msg["type_abts_tarif"]."</label>
</div>
<div class='row'>
	<input type=text name='tarif' id='tarif' value='!!tarif!!' maxlength='6' class='saisie-10em' />
</div>
<div class='row'>
	<label class='etiquette' for='caution'>".$msg["type_abts_caution"]."</label>
</div>
<div class='row'>
	<input type=text name='caution' id='caution' value='!!caution!!' maxlength='6' class='saisie-10em' />
</div>
<div class='row'>
	<label class='etiquette'>".$msg["type_abts_use_localisations"]."</label>
</div>
!!localisations!!
";

$finance_amende_content_form="
<div class='row'>
	<label for='amende_jour' class='etiquette'>".$msg["finance_amende_mnt"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_jour' id='amende_jour' value='!!amende_jour!!'/>
</div>
<div class='row'>
	<label for='amende_delai' class='etiquette'>".$msg["finance_amende_delai"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_delai' id='amende_delai' value='!!amende_delai!!'/>
</div>
<div class='row'>
	<label for='amende_1_2' class='etiquette'>".$msg["finance_amende_delai_1_2"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_1_2' id='amende_1_2' value='!!amende_1_2!!'/>
</div>
<div class='row'>
	<label for='amende_2_3' class='etiquette'>".$msg["finance_amende_delai_2_3"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_2_3' id='amende_2_3' value='!!amende_2_3!!'/>
</div>
<div class='row'>
	<label for='amende_delai_recouvrement' class='etiquette'>".$msg["finance_amende_delai_recouvrement"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_delai_recouvrement' id='amende_delai_recouvrement' value='!!amende_delai_recouvrement!!'/>
</div>
<div class='row'>
	<label for='amende_frais_recouvrement' class='etiquette'>".$msg["finance_amende_frais_recouvrement"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_frais_recouvrement' id='amende_frais_recouvrement' value='!!amende_frais_recouvrement!!'/>
</div>
<div class='row'>
	<label for='amende_max' class='etiquette'>".$msg["finance_amende_max"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='amende_max' id='amende_max' value='!!amende_max!!'/>
</div>
";

$finance_amende_relance_content_form="
<div class='row'>
	<label for='relance_1' class='etiquette'>".$msg["finance_relance_1"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='relance_1' id='relance_1' value='!!relance_1!!'/>
</div>
<div class='row'>
	<label for='relance_2' class='etiquette'>".$msg["finance_relance_2"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='relance_2' id='relance_2' value='!!relance_2!!'/>
</div>
<div class='row'>
	<label for='relance_3' class='etiquette'>".$msg["finance_relance_3"]."</label>
</div>
<div class='row'>
	<input type='text class='saisie-10em' name='relance_3' id='relance_3' value='!!relance_3!!'/>
</div>
<div class='row'>
	<label for='statut_perdu' class='etiquette'>".$msg["finance_statut_perdu"]."</label>
</div>
<div class='row'>
	!!statut_perdu!!
</div>
";
?>