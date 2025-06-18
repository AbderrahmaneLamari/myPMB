<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_caddie_content_root_ui.tpl.php,v 1.1 2020/10/21 11:14:35 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $list_caddie_content_root_ui_search_filters_form_tpl, $msg;

$list_caddie_content_root_ui_search_filters_form_tpl = "
<div class='row'>
	<div class='colonne3'>
		<div class='row'>
			<input type='checkbox' name='!!objects_type!!_elt_flag' id='!!objects_type!!_elt_flag' value='1' !!elt_flag!!><label for='!!objects_type!!_elt_flag'>".$msg['caddie_item_marque']."</label>
			<input type='hidden' name='!!objects_type!!_applied_elt_flag' id='!!objects_type!!_applied_elt_flag' value='1' />
		</div>
		<div class='row'>
			<input type='checkbox' name='!!objects_type!!_elt_no_flag' id='!!objects_type!!_elt_no_flag' value='1' !!elt_no_flag!!><label for='!!objects_type!!_elt_no_flag'>".$msg['caddie_item_NonMarque']."</label>
			<input type='hidden' name='!!objects_type!!_applied_elt_no_flag' id='!!objects_type!!_applied_elt_no_flag' value='1' />
		</div>		
	</div>
</div>";
