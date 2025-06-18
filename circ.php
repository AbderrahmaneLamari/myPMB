<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: circ.php,v 1.31 2021/05/03 07:59:40 dgoron Exp $

// définition du minimum nécéssaire 
$base_path=".";                            
$base_auth = "CIRCULATION_AUTH";  
$base_title = "\$msg[5]";
$base_use_dojo = 1;
require_once ("$base_path/includes/init.inc.php");  

if ((SESSrights & RESTRICTCIRC_AUTH) && ($categ!="pret") && ($categ!="pretrestrict") ) {
	$sub="";
	$categ="";
}
// modules propres à circ.php ou à ses sous-modules
require_once($class_path."/modules/module_circ.class.php");
require_once("$include_path/templates/circ.tpl.php");
require_once("$include_path/templates/empr.tpl.php");
require_once("$include_path/templates/expl.tpl.php");

module_circ::get_instance()->proceed_header();

include("./circ/main.inc.php");
print alert_sound_script();
	
module_circ::get_instance()->proceed_footer();

pmb_mysql_close();
