<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: view_sugg.inc.php,v 1.23.4.1 2023/08/02 09:07:47 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $base_path, $msg, $id_sug, $id_empr;

require_once($base_path.'/classes/suggestions.class.php');

if(!empty($id_sug)) {
    $id_sug = intval($id_sug);
    $sug = new suggestions($id_sug);
    print "
    <div id='view_sugg'>
	   <h3><span>".htmlentities($msg['empr_view_sugg_detail'], ENT_QUOTES, $charset)."</span></h3>
	   <div id='empr_view-container'>";
    print $sug->get_table();
    print "</div>
    </div>";
} else {
    $list_opac_suggestions_ui = list_opac_suggestions_ui::get_instance(array('user_id' => array($id_empr), 'user_status' => array(1)));
    
    $sug_form = "
<div id='view_sugg'>
	<h3><span>".htmlentities($msg['empr_view_sugg'], ENT_QUOTES, $charset)."</span></h3>
	<div id='empr_view-container'>
	<!-- affichage liste des suggestions -->";
    $sug_form .= $list_opac_suggestions_ui->get_display_list();
    $sug_form.= "
    </div>
</div>";
    print $sug_form;
}
