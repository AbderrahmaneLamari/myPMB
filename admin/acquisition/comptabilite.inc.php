<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: comptabilite.inc.php,v 1.23.2.1 2021/12/22 10:43:26 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $include_path, $action, $ent, $id, $libelle, $msg, $date_deb, $date_fin, $def;

// gestion des exercices comptables
require_once("$class_path/entites.class.php");
require_once("$class_path/exercices.class.php");
require_once("$include_path/templates/comptabilite.tpl.php");

function show_list_biblio() {
	global $msg;
	global $charset;

	//Récupération de l'utilisateur
  	$requete_user = "SELECT userid FROM users where username='".SESSlogin."' limit 1 ";
	$res_user = pmb_mysql_query($requete_user);
	$row_user=pmb_mysql_fetch_row($res_user);
	$user_userid=$row_user[0];

	//Affichage de la liste des etablissements auxquels a acces l'utilisateur
	$aff = "<table>";
	$q = entites::list_biblio($user_userid);
	$res = pmb_mysql_query($q);
	$nbr = pmb_mysql_num_rows($res);

	$error = false;
	if(!$nbr) {
		//Pas d'etablissements définis pour l'utilisateur
		$error = true; 
		$error_msg.= htmlentities($msg["acquisition_err_coord"],ENT_QUOTES, $charset)."<div class='row'></div>";	
	}
	
	if ($error) {
		error_message($msg[321], $error_msg.htmlentities($msg["acquisition_err_par"],ENT_QUOTES, $charset), '1', './admin.php?categ=acquisition');
		die;
	}

	if ($nbr == '1') {
		$row = pmb_mysql_fetch_object($res);
		show_list_exer($row->id_entite);		
	} else {
		$parity=1;
		while($row=pmb_mysql_fetch_object($res)){
			if ($parity % 2) {
				$pair_impair = "even";
			} else {
				$pair_impair = "odd";
			}
			$parity += 1;
			$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./admin.php?categ=acquisition&sub=compta&action=list&ent=$row->id_entite';\" ";
	        $aff.= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'><td><i>$row->raison_sociale</i></td></tr>";
		}
		$aff.= "</table>";
		print $aff;
	}
}

function show_list_exer($id_entite) {
	global $msg;
	global $charset;

	$biblio = new entites($id_entite);
	print "<div class='row'><label class='etiquette'>".htmlentities($biblio->raison_sociale,ENT_QUOTES,$charset)."</label></div>";
	print list_configuration_acquisition_compta_ui::get_instance(array('num_entite' => $id_entite))->get_display_list();
}

function confirmation_cloture($url) {
	global $msg;
	
	return "<script type='text/javascript'>
		function confirmation_cloture(param,element) {
        	result = confirm(\"".$msg['acquisition_compta_confirm_clot']." '\"+element+\"' ?\");
        	if(result) document.location = \"$url\"+param ;
		}</script>";
}

function confirmation_suppression($url) {
	global $msg;
	
	return "<script type='text/javascript'>
		function confirmation_suppression(param,element) {
        	result = confirm(\"".$msg['acquisition_compta_confirm_suppr']." '\"+element+\"' ?\");
        	if(result) document.location = \"$url\"+param ;
		}</script>";
}
?>

<script type='text/javascript'>
function test_form(form)
{
	if(form.libelle.value.length == 0)
	{
		alert("<?php echo $msg[98]; ?>");
		document.forms['exerform'].elements['libelle'].focus();
		return false;	
	}
	return true;
}
</script>

<?php

switch($action) {
	case 'list':
		show_list_exer($ent);
		break;
	case 'add':
	    $exercice = new exercices();
	    print $exercice->get_form($ent);
		break;
	case 'modif':
		if (exercices::exists($id)) {
		    $exercice = new exercices($id);
		    print $exercice->get_form($ent);
		} else {
			show_list_exer($ent);
		}
		break;
	case 'update':
		// vérification validité des données fournies.
		//Pas deux libelles d'exercices identiques pour la même entité
		$nbr = exercices::existsLibelle($ent, $libelle, $id);		
		if ( $nbr > 0 ) {
			error_form_message($libelle.$msg["acquisition_compta_already_used"]);
			break;
		}
		if ($date_deb && $date_fin) {	//Vérification des dates			
			//Date fin > date début
			if($date_deb > $date_fin) {
				error_form_message($libelle.$msg["acquisition_compta_date_inf"]);
				break;
			}
		}			
		$ex = new exercices($id);
		$ex->set_properties_from_form();
		$ex->save();
		if (isset($def) && $def) $ex->setDefault();
		show_list_exer($ent);
		break;
	case 'del':
		if($id) {
			$total1 = exercices::hasBudgetsActifs($id);
			$total2 = exercices::hasActesACtifs($id);
			if (($total1+$total2)==0) {
				exercices::delete($id);
				show_list_exer($ent);
			} else {
				$msg_suppr_err = $msg['acquisition_compta_used'] ;
				if ($total1) $msg_suppr_err .= "<br />- ".$msg['acquisition_compta_used_bud'] ;
				if ($total2) $msg_suppr_err .= "<br />- ".$msg['acquisition_compta_used_act'] ;
			
				error_message($msg[321], $msg_suppr_err, 1, 'admin.php?categ=acquisition&sub=compta&action=list&ent='.$ent);
			}
		
		} else {
			show_list_exer($ent);
		}
		break;
	case 'clot':
		//On vérifie que tous les budgets sont cloturés et toutes les commandes archivées
		if($id) {
			$total1 = exercices::hasBudgetsActifs($id);
			$total2 = exercices::hasActesActifs($id);
			if (($total1+$total2)==0) {
				$ex = new exercices($id);
				$ex->statut='0';
				$ex->save();
				show_list_exer($ent);
			} else {
				$msg_suppr_err = $msg['acquisition_compta_actif'] ;
				if ($total1) $msg_suppr_err .= "<br />- ".$msg['acquisition_compta_used_bud'] ;
				if ($total2) $msg_suppr_err .= "<br />- ".$msg['acquisition_compta_used_act'] ;
			
				error_message($msg[321], $msg_suppr_err, 1, 'admin.php?categ=acquisition&sub=compta&action=list&ent='.$ent);
			}
		} else {
			show_list_exer($ent);
		}
		break;
	default:
		show_list_biblio();
		break;
}
?>