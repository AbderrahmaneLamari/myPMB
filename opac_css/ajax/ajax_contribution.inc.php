<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_contribution.inc.php,v 1.9.2.1 2021/07/05 12:41:28 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if ($opac_contribution_area_activate && $allow_contribution) {
	if (!empty($iframe)) {
		print '<textarea>';
	}
	switch ($sub) {
		case 'ajax_check_values' :
			require_once($base_path.'/includes/contribution_area_check_values.inc.php');
			break;
		case 'computed_fields' :
			require_once($base_path.'/includes/contribution_area_computed_fields.inc.php');
			break;
		case 'get_resource_template':
		    require_once($base_path.'/includes/contribution_area_resource.inc.php');
		    break;
		case 'get_resource_display_label':
		    require_once($base_path.'/includes/contribution_area_resource.inc.php');
		    break;
		case 'get_author_function_options':
		    require_once "$base_path/classes/onto/contribution/onto_contribution_datatype_responsability_selector_ui.class.php";
		    print onto_contribution_datatype_responsability_selector_ui::get_author_function_options("");
		    break;
		default :
			require_once($base_path.'/includes/contribution_area.inc.php');
			break;
	}
	if (!empty($iframe)) {
		print '</textarea>';
	}
}