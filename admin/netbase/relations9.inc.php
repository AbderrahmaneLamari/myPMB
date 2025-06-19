<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: relations9.inc.php,v 1.6.4.1 2023/06/15 11:57:48 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec;

require_once($class_path."/notice_relations.class.php");

// initialisation de la borne de départ
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);
print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_update_relations"], ENT_QUOTES, $charset)."</h2>";

$affected = notice_relations::upgrade_notices_relations_table();

$spec = $spec - CLEAN_RELATIONS;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_suppr_relations"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_update_relations"], ENT_QUOTES, $charset);

// mise à jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec);
