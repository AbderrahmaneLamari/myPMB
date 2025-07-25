<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: subcollections.inc.php,v 1.17 2022/02/28 13:44:49 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset;
global $start, $v_state, $spec, $current_module;

require_once("$class_path/subcollection.class.php");

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["nettoyage_suppr_subcollections"], ENT_QUOTES, $charset)."</h2>";

$query = pmb_mysql_query("SELECT sub_coll_id from sub_collections left join notices on sub_coll_id=subcoll_id where subcoll_id is null ");
$affected=0;
if(pmb_mysql_num_rows($query)){
	while ($ligne = pmb_mysql_fetch_object($query)) {
		$subcoll = new subcollection($ligne->sub_coll_id);
		$deleted = $subcoll->delete();
		if(!$deleted) {
			$affected++;
		}
	}
}

//Nettoyage des informations d'autorit�s pour les sous collections
subcollection::delete_autority_sources();

$query = pmb_mysql_query("update notices left join sub_collections ON sub_coll_id=subcoll_id SET subcoll_id=0 WHERE sub_coll_id is null");

$spec = $spec - CLEAN_SUBCOLLECTIONS;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["nettoyage_suppr_subcollections"], ENT_QUOTES, $charset)." : ";
$v_state .= $affected." ".htmlentities($msg["nettoyage_res_suppr_subcollections"], ENT_QUOTES, $charset);
pmb_mysql_query('OPTIMIZE TABLE sub_collections');
// mise � jour de l'affichage de la jauge
print netbase::get_display_final_progress();
print "
	<form class='form-$current_module' name='process_state' action='./clean.php?spec=$spec&start=0' method='post'>
		<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
		<input type='hidden' name='spec' value=\"$spec\">
		</form>
	<script type=\"text/javascript\"><!--
		document.forms['process_state'].submit();
		-->
	</script>";
