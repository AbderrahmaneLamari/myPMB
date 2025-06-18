<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: origin.tpl.php,v 1.4 2021/01/20 12:55:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $origin_content_form, $msg;

$origin_content_form="
<div class='row'>
	<label class='etiquette' for='origin_name'>".$msg['origin_name']."</label>
</div>
<div class='row'>
	<input type='text' name='origin_name' id='origin_name' value='!!origin_name!!' />
</div>
<div class='row'>
	<label class='etiquette' for='origin_name'>".$msg['origin_country']."</label>
</div>
<div class='row'>
	<input type='text' name='origin_country' id='origin_country' value='!!origin_country!!' />
</div>
<div class='row'>
	<label class='etiquette' >".$msg['origin_diffusible']."</label>&nbsp;
	<input type='checkbox' name='origin_diffusible' id='origin_diffusible' value='1' !!checked!!/>
</div>
";