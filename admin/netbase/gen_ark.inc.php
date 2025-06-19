<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: gen_ark.inc.php,v 1.2 2022/09/09 10:34:39 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $msg, $charset;
global $start, $v_state, $spec, $count;

use Pmb\Ark\Models\ArkModel;
// la taille d'un paquet d'entites
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php

//on réduit le nombre d'entites, sinon le serveur est a genou
$lot = 10;

// initialisation de la borne de départ
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

if(!$count) {
	$count = ArkModel::getNbEntitiesWithoutArk();
}

print "<br /><br /><h2 class='center'>".htmlentities($msg["ark_manage_generate"], ENT_QUOTES, $charset)."</h2>";

if(ArkModel::getNbEntitiesWithoutArk()) {
	print netbase::get_display_progress($start, $count);
	ArkModel::generateMassArk($lot);
	$next = $start + $lot;
 	print netbase::get_current_state_form($v_state, $spec, '', $next, $count);
} else {
    $spec = $spec - GEN_ARK;
    $v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["ark_netbase_generate"], ENT_QUOTES, $charset)." : ";
    $v_state.= "OK";
	
	print netbase::get_display_final_progress();
	print netbase::get_process_state_form($v_state, $spec);
}