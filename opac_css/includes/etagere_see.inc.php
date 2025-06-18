<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: etagere_see.inc.php,v 1.71.2.2 2021/12/16 08:47:23 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $base_path, $include_path, $msg, $begin_result_liste, $page, $add_cart_link, $alert_see_mc_values;
global $opac_nb_aut_rec_per_page, $opac_search_allow_refinement, $opac_notices_depliable;
global $id;

require_once($class_path."/etagere.class.php");
require_once($class_path.'/etagere_caddies.class.php');
require_once($class_path."/suggest.class.php");
require_once($class_path."/sort.class.php");

// affichage du contenu d'une étagère

print "<div id='aut_details'>\n";

if ($id) {
	//enregistrement de l'endroit actuel dans la session
	rec_last_authorities();
	//Récupération des infos de l'étagère
	$id = intval($id);
	$etagere = new etagere($id);
	
	print pmb_bidi(($etagere->thumbnail_url?"<img src='".$etagere->thumbnail_url."' border='0' class='thumbnail_etagere' alt='".$etagere->get_translated_name()."'>":"")."<h3><span>".$etagere->get_translated_name()."</span></h3>\n");
	print "<div id='aut_details_container'>\n";
	if ($etagere->get_translated_comment()){
		print "<div id='aut_see'>\n";
		print pmb_bidi("<strong>".$etagere->get_translated_comment()."</strong><br /><br />");
		print "	</div><!-- fermeture #aut_see -->\n";			
	}

	print "<div id='aut_details_liste'>\n";

	$etagere_caddies = new etagere_caddies($id);
	
	$nbr_lignes=$etagere_caddies->get_notices_count();
	
	//Recherche des types doc
	$t_typdoc = $etagere_caddies->get_typdocs();
	$l_typdoc=implode(",",$t_typdoc);

	// Ouverture du div resultatrech_liste
	print "<div id='resultatrech_liste'>";
	
	/**
	 * 19/10/2021 : Désactivation de la création/modification d'alerte à partir de l'étagère
	 * Il faudrait que ce soit le résultat d'une recherche pour que cela fonctionne..
	 */
	// pour la DSI - création d'une alerte
	/*if ($nbr_lignes && $opac_allow_bannette_priv && $allow_dsi_priv && ((isset($_SESSION['abon_cree_bannette_priv']) && $_SESSION['abon_cree_bannette_priv']==1) || $opac_allow_bannette_priv==2)) {
		print "<input type='button' class='bouton' name='dsi_priv' value=\"$msg[dsi_bt_bannette_priv]\" onClick=\"document.mc_values.action='./empr.php?lvl=bannette_creer'; document.mc_values.submit();\"><span class=\"espaceResultSearch\">&nbsp;</span>";
	}
	
	// pour la DSI - Modification d'une alerte
	if(!empty($_SESSION['abon_edit_bannette_priv']) && !empty($_SESSION['abon_edit_bannette_priv_visibility_until']) && $_SESSION['abon_edit_bannette_priv_visibility_until'] < time()) {
		unset($_SESSION['abon_edit_bannette_priv']);
	}
	if ($nbr_lignes && $opac_allow_bannette_priv && $allow_dsi_priv && (isset($_SESSION['abon_edit_bannette_priv']) && $_SESSION['abon_edit_bannette_priv']==1)) {
		print "<input type='button' class='bouton' name='dsi_priv' value=\"".$msg['dsi_bannette_edit']."\" onClick=\"document.mc_values.action='./empr.php?lvl=bannette_edit&id_bannette=".$_SESSION['abon_edit_bannette_id']."'; document.mc_values.submit();\"><span class=\"espaceResultSearch\">&nbsp;</span>";
	}*/
	
	if(!$page) $page=1;
	$debut =($page-1)*$opac_nb_aut_rec_per_page;
		
	if($nbr_lignes) {
		$notices = $etagere_caddies->get_notices($debut, $opac_nb_aut_rec_per_page);
		
		if ($opac_notices_depliable) print $begin_result_liste;

		print "<span class=\"printEtagere\">
				<a href='#' onClick=\"openPopUp('".$base_path."/print.php?lvl=etagere&id_etagere=".$id."','print'); w.focus(); return false;\" title=\"".$msg["etagere_print"]."\">
					<img src='".get_url_icon('print.gif')."' border='0' class='align_bottom' alt=\"".$msg["etagere_print"]."\"/>
				</a>
			</span>";
		
		//gestion du tri
		//est géré dans index_includes.inc.php car il faut le gérer avant l'affichage du sélecteur de tri
		print $etagere_caddies->show_tris();
		
		print $add_cart_link;
		
		//affinage
		//enregistrement de l'endroit actuel dans la session
		if(empty($_SESSION["last_module_search"])) {
		    $_SESSION["last_module_search"] = array();
		}
		$_SESSION["last_module_search"]["search_mod"]="etagere_see";
		$_SESSION["last_module_search"]["search_id"]=$id;
		$_SESSION["last_module_search"]["search_page"]=$page;
		
		// Gestion des alertes à partir de la recherche simple
 		include_once($include_path."/alert_see.inc.php");
 		print $alert_see_mc_values;
			
		//affichage
 		if($opac_search_allow_refinement){
			print "<span class=\"espaceResultSearch\">&nbsp;&nbsp;</span><span class=\"affiner_recherche\"><a href='$base_path/index.php?search_type_asked=extended_search&mode_aff=aff_module' title='".$msg["affiner_recherche"]."'>".$msg["affiner_recherche"]."</a></span>";
 		}	
		//fin affinage
		
		print "<blockquote>\n";
		print aff_notice(-1);
		
		$nb=0;
		$recherche_ajax_mode=0;
		foreach ($notices as $notice_id) {
			if($nb>4)$recherche_ajax_mode=1;
			$nb++;
			print pmb_bidi(aff_notice($notice_id, 0, 1, 0, "", "", 0, 1, $recherche_ajax_mode));
		}
		print aff_notice(-2);
		print "	</blockquote>\n";
		print "</div><!-- fermeture #resultatrech_liste -->\n";
		print "</div><!-- fermeture #aut_details_liste -->\n";
		print "<div id='navbar'><hr /><div style='text-align:center'>".printnavbar($page, $nbr_lignes, $opac_nb_aut_rec_per_page, "./index.php?lvl=etagere_see&id=".$id."&page=!!page!!&nbr_lignes=".$nbr_lignes.($nb_per_page_custom ? "&nb_per_page_custom=".$nb_per_page_custom : ''))."</div></div>\n";
	} else {
			print $msg['no_document_found'];
			print "</div><!-- fermeture #resultatrech_liste -->\n";
			print "</div><!-- fermeture #aut_details_liste -->\n";
	}
	print "</div><!-- fermeture #aut_details_container -->\n";
}

print "</div><!-- fermeture #aut_details -->\n";	

//FACETTES
$facettes_tpl = '';
//comparateur de facettes : on ré-initialise
$_SESSION['facette']=array();
if($nbr_lignes){
	require_once($base_path.'/classes/facette_search.class.php');
	$query = "select distinct notice_id from caddie_content, etagere_caddie, notices $acces_j $statut_j ";
	$query .= "where etagere_id=$id and caddie_content.caddie_id=etagere_caddie.caddie_id and notice_id=object_id $statut_r ";
	$facettes_tpl .= facettes::get_display_list_from_query($query);
}
?>