<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pclass.tpl.php,v 1.11 2021/03/15 09:02:52 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

// templates pour la gestion des plans de classements 

global $browser_pclassement;
global $pclassement_content_form;
global $pclassement_location_form, $msg;

//Template du browser
$browser_pclassement = "
<div class='row'>
	<h3>&nbsp;".$msg['pclassement_liste']."</h3>
</div>
<br />
<br />
<div class='row'>
	!!browser_content!!
</div>
";

//Template du formulaire
$pclassement_content_form = "
<!-- identifiant -->
!!identifiant!!

<!-- libelle -->
<div class='row'>
	<label class='etiquette' >".$msg[103]."</label><label class='etiquette'></label>
</div>
<div class='row'>
	<input type='text' class='saisie-80em' id='libelle' name='libelle' value=\"!!libelle!!\" />
</div>

<!-- langue defaut -->
<div class='row'>
	<label class='etiquette' >".$msg['pclassement_type_doc_titre']."</label><label class='etiquette'></label>
</div>
<div class='row'>
	!!type_doc!! 
</div>
!!locations!!
";

$pclassement_locations_form = "
<!-- localisations -->
<div class='row'>
	<label class='etiquette' >".$msg['pclassement_locations']."</label>
</div>
<div class='row'>
	!!locations!!
</div>		
";
		