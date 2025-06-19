<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: out_auth.inc.php,v 1.9.6.1 2023/07/11 06:47:31 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $action, $id, $authorized_sources;

require_once($class_path."/external_services_esusers.class.php");

function list_esgroups() {
	print list_configuration_connecteurs_out_auth_ui::get_instance()->get_display_list();
}

function show_auth_edit_content_form($group_id, $the_group) {
	$interface_content_form = new interface_content_form();
	
	//Nom du groupe
	$interface_content_form->add_element('esgroup_name', 'admin_connecteurs_outauth_groupname')
	->add_html_node($the_group->esgroup_name.'<br /><br />');
	//Nom complet du groupe
	$interface_content_form->add_element('esgroup_fullname', 'admin_connecteurs_outauth_groupfullname')
	->add_html_node($the_group->esgroup_fullname.'<br /><br />');
	
	$current_sources=array();
	$current_sql = "SELECT connectors_out_source_esgroup_sourcenum FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_esgroupnum = ".$group_id;
	$current_res = pmb_mysql_query($current_sql);
	while($row = pmb_mysql_fetch_assoc($current_res)) {
		$current_sources[] = $row["connectors_out_source_esgroup_sourcenum"];
	}
	$sources_display = '';
	$data_sql = "SELECT connectors_out_sources_connectornum, connectors_out_source_id, connectors_out_source_name, EXISTS(SELECT 1 FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_sourcenum = connectors_out_source_id AND connectors_out_source_esgroup_esgroupnum = ".$group_id.") AS authorized FROM connectors_out_sources ORDER BY connectors_out_sources_connectornum";
	$data_res = pmb_mysql_query($data_sql);
	$current_connid = 0;
	while($asource=pmb_mysql_fetch_assoc($data_res)) {
		if ($current_connid != $asource["connectors_out_sources_connectornum"]) {
			if ($current_connid)
				$sources_display .= '<br />';
				$current_connid = $asource["connectors_out_sources_connectornum"];
		}
		$sources_display .= '<input '.(in_array($asource["connectors_out_source_id"], $current_sources) ? 'checked' : '').' type="checkbox" name="authorized_sources[]" value="'.$asource["connectors_out_source_id"].'">';
		$sources_display .= $asource["connectors_out_source_name"];
		
		$sources_display .= '<br />';
	}
	$interface_content_form->add_element('authorized_sources', 'admin_connecteurs_outauth_usesource')
	->add_html_node($sources_display);
	return $interface_content_form->get_display();
}

function show_auth_edit_form($group_id) {
	global $msg;
	
	$the_group = new es_esgroup($group_id);
	if ($the_group->error) {
		exit();
	}
	$content_form = show_auth_edit_content_form($group_id, $the_group);
	$interface_form = new interface_admin_form('form_outauth');
	$interface_form->set_label($msg["admin_connecteurs_outauth_edit"]);
	$interface_form->set_object_id($group_id)
	->set_content_form($content_form);
	print $interface_form->get_display_parameters();
}

function show_auth_edit_form_anonymous() {
	global $msg;
	
	print '<form method="POST" action="admin.php?categ=connecteurs&sub=out_auth&action=updateanonymous" name="form_outauth">';
	print '<h3>'.$msg['admin_connecteurs_outauth_edit'].'</h3>';
		
	print '<div class="form-contenu">';
	
	//Nom du groupe
	print '<div class=row><label class="etiquette" for="set_caption">'.$msg["admin_connecteurs_outauth_groupname"].'</label><br />';
	print '&lt;'.$msg["admin_connecteurs_outauth_anonymgroupname"].'&gt;';
	print '</div><br />';
	
	//Nom complet du groupe
	print '<div class=row><label class="etiquette" for="set_caption">'.$msg["admin_connecteurs_outauth_groupfullname"].'</label><br />';
	print $msg["admin_connecteurs_outauth_anonymgroupfullname"];
	print '</div><br />';

	$current_sources=array();
	$current_sql = "SELECT connectors_out_source_esgroup_sourcenum FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_esgroupnum = -1";
	$current_res = pmb_mysql_query($current_sql);
	while($row = pmb_mysql_fetch_assoc($current_res)) {
		$current_sources[] = $row["connectors_out_source_esgroup_sourcenum"];
	}
	
	$data_sql = "SELECT connectors_out_sources_connectornum, connectors_out_source_id, connectors_out_source_name, EXISTS(SELECT 1 FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_sourcenum = connectors_out_source_id AND connectors_out_source_esgroup_esgroupnum = -1) AS authorized FROM connectors_out_sources ORDER BY connectors_out_sources_connectornum";
	$data_res = pmb_mysql_query($data_sql);
	$current_connid = 0;
	print '<div class=row><label class="etiquette">'.$msg["admin_connecteurs_outauth_usesource"].'</label><br />';
	while($asource=pmb_mysql_fetch_assoc($data_res)) {
		if ($current_connid != $asource["connectors_out_sources_connectornum"]) {
			if ($current_connid) 
				print '<br />';
			$current_connid = $asource["connectors_out_sources_connectornum"];
		}
		print '<input '.(in_array($asource["connectors_out_source_id"], $current_sources) ? 'checked' : '').' type="checkbox" name="authorized_sources[]" value="'.$asource["connectors_out_source_id"].'">';
		print $asource["connectors_out_source_name"];
		
		print '<br />';
	}
	print '</div>';
	
	//buttons
	print "<br /><div class='row'>
	<div class='left'>";
	print "<input class='bouton' type='button' value=' $msg[76] ' onClick=\"document.location='./admin.php?categ=connecteurs&sub=out_auth'\" />&nbsp";
	print '<input class="bouton" type="submit" value="'.$msg[77].'">';
	print "</div>
	<br /><br /></div>";
	
	print '</form>';
}

switch ($action) {
	case "edit":
		if (!isset($id) || !$id) {
			list_esgroups();
			exit();
		}
		show_auth_edit_form((int)$id);
		break;
	case "editanonymous":
		show_auth_edit_form_anonymous();
		break;
	case "update":
		if (isset($id) && $id) {
		    array_walk($authorized_sources, function(&$a) {$a = intval($a);}); //Virons de la liste ce qui n'est pas entier
			//Croisons ce que l'on nous propose avec ce qui existe vraiment dans la base
			//Vérifions que les sources existents
			$sql = "SELECT connectors_out_source_id FROM connectors_out_sources WHERE connectors_out_source_id IN (".implode(",", $authorized_sources).')';
			$res = pmb_mysql_query($sql);
			$final_authorized_sources = array();
			while ($row=pmb_mysql_fetch_assoc($res))
				$final_authorized_sources[] = $row["connectors_out_source_id"];

			//Vérifions que le groupe existe
			$esgroup = new es_esgroup($id);
			if ($esgroup->error) {
				exit();
			}
			
			//On vire ce qui existe déjà:
			$sql = "DELETE FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_esgroupnum = ".$id;
			pmb_mysql_query($sql);
			
			//Tout est bon? On insert
			$values = array();
			$insert_sql = "INSERT INTO connectors_out_sources_esgroups (connectors_out_source_esgroup_sourcenum, connectors_out_source_esgroup_esgroupnum) VALUES ";
			foreach ($final_authorized_sources as $an_authorized_source) {
				$values[] = '('.$an_authorized_source.','.$id.')';
			}
			$insert_sql .= implode(",", $values);
			pmb_mysql_query($insert_sql);
		}
		list_esgroups();
		break;
	case "updateanonymous":
		if (!$authorized_sources)
			$final_authorized_sources=array();
		else {
		    array_walk($authorized_sources, function(&$a) {$a = intval($a);}); //Virons de la liste ce qui n'est pas entier
			//Croisons ce que l'on nous propose avec ce qui existe vraiment dans la base
			//Vérifions que les sources existents
			$sql = "SELECT connectors_out_source_id FROM connectors_out_sources WHERE connectors_out_source_id IN (".implode(",", $authorized_sources).')';
			$res = pmb_mysql_query($sql);
			$final_authorized_sources = array();
			while ($row=pmb_mysql_fetch_assoc($res))
				$final_authorized_sources[] = $row["connectors_out_source_id"];
			
		}

		//On vire ce qui existe déjà:
		$sql = "DELETE FROM connectors_out_sources_esgroups WHERE connectors_out_source_esgroup_esgroupnum = -1";
		pmb_mysql_query($sql);
		
		//Tout est bon? On insert
		$values = array();
		$insert_sql = "INSERT INTO connectors_out_sources_esgroups (connectors_out_source_esgroup_sourcenum, connectors_out_source_esgroup_esgroupnum) VALUES ";
		foreach ($final_authorized_sources as $an_authorized_source) {
			$values[] = '('.$an_authorized_source.', -1)';
		}
		if(!empty($values)) {
			$insert_sql .= implode(",", $values);
			pmb_mysql_query($insert_sql);
		}
		list_esgroups();
		break;
	default:
		list_esgroups();
		break;
}


?>