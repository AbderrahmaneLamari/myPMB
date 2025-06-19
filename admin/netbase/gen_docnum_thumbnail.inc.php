<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: gen_docnum_thumbnail.inc.php,v 1.1.2.1 2023/09/15 10:01:35 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $msg, $charset, $v_state, $spec;

require_once ($class_path."/netbase/netbase_explnum.class.php");

$v_state=urldecode($v_state);

print "<br /><br /><h2 class='center'>".htmlentities($msg["gen_docnum_thumbnail_in_progress"], ENT_QUOTES, $charset)."</h2>";
$nb_thumbnail = netbase_explnum::gen_docnum_thumbnail();
$spec = $spec - GEN_DOCNUM_THUMBNAIL;
$v_state .= "<br /><img src='".get_url_icon('d.gif')."' hspace=3>".htmlentities($msg["gen_docnum_thumbnail_in_progress"], ENT_QUOTES, $charset)." : ";
$v_state .= $nb_thumbnail." ".htmlentities($msg["gen_docnum_thumbnail_end"], ENT_QUOTES, $charset);
// mise à jour de l'affichage de la jauge
print netbase::get_display_final_progress();
print netbase::get_process_state_form($v_state, $spec);

