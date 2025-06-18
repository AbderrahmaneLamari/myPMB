<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes_liste.inc.php,v 1.12 2021/03/30 16:34:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $iddemande, $act, $class_path, $chk, $state;

$iddemande = intval($iddemande);

require_once "$class_path/demandes.class.php";

$demande = new demandes($iddemande);

switch ($act) {
	case 'new':
		$demande->show_modif_form();
		break;
	case 'save':
		$demande->set_properties_from_form();
		$demande->save();
		$demande->show_list_form();
		break;
	case 'search':
		$demande->show_list_form();
		break;
	case 'suppr':
		if (!empty($iddemande)) {
			demandes::delete($demande);
		} elseif(!empty($chk)) {
			$chk = explode(",", $chk);
			$nb_chk = count($chk);
			for ($i = 0; $i < $nb_chk; $i++) {
				$dmde = new demandes($chk[$i]);
				demandes::delete($dmde);
			}
		}		
		$demande->show_list_form();
		break;
	case 'suppr_noti':
		$requete = "SELECT num_notice FROM demandes WHERE id_demande IN (".implode(",", $chk).") AND num_notice!=0";
		$result = pmb_mysql_query($requete);
		if (pmb_mysql_num_rows($result) > 0) {
			$demande->suppr_notice_form();
		} else {
			if (!empty($iddemande)) {
				demandes::delete($demande);
			} elseif(!empty($chk)) {
				if (!is_array($chk)) {
					$chk = explode(",", $chk);
				}
				$nb_chk = count($chk);
				for ($i = 0; $i < $nb_chk; $i++) {
					$dmde = new demandes($chk[$i]);
					demandes::delete($dmde);
				}
			}
			$demande->show_list_form();
		}
		break;
	case 'change_state':
		if (!empty($chk)) {
		    $nb_chk = count($chk);
		    for ($i = 0; $i < $nb_chk; $i++) {
				$dde = new demandes($chk[$i]);
				$dde->change_state($state);
			}
		} else {
			$demande->change_state($state);
			$demande->fetch_data($iddemande);
		}		
		$demande->show_list_form();
		break;
	case 'affecter':
		$demande->attribuer();
		$demande->show_list_form();
		break;
	default:
		$demande->show_list_form();
		break;
}
?>