<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: publishers.inc.php,v 1.21 2022/02/28 13:44:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec;

require_once("$class_path/editor.class.php");

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_suppr_editeurs"], ENT_QUOTES, $charset)."</h2>";

$requete="SELECT DISTINCT ed_id FROM publishers LEFT JOIN notices n1 ON n1.ed1_id=ed_id LEFT JOIN notices n2 ON n2.ed2_id=ed_id LEFT JOIN collections ON ed_id=collection_parent WHERE n1.notice_id IS NULL AND  n2.notice_id IS NULL AND collection_id IS NULL";
$res=pmb_mysql_query($requete);
$affected=0;
if(pmb_mysql_num_rows($res)){
	while ($ligne = pmb_mysql_fetch_object($res)) {
		$editeur = new editeur($ligne->ed_id);
		$deleted = $editeur->delete();
		if(!$deleted) {
			$affected++;
		}
	}
}


$spec = $spec - CLEAN_PUBLISHERS;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_suppr_editeurs"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_editeurs"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE publishers');
// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();

print netbase::get_process_state_form($v_state, $spec);
