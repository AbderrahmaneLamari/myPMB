<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr.php,v 1.4 2021/04/29 13:10:22 dgoron Exp $


// définition du minimum nécessaire 
$base_path=".";                            
$base_auth = "CMS_AUTH";  
$base_title = "\$msg[cms_onglet_title]";  
                            
$base_use_dojo=1; 

require_once ("$base_path/includes/init.inc.php");
require_once($class_path."/modules/module_frbr.class.php");
require_once($class_path."/autoloader.class.php");
$autoloader = new autoloader();
if($cms_active && (SESSrights & CMS_BUILD_AUTH)) {
	$autoloader->add_register("cms_modules",true);
}

print " <script type='text/javascript' src='javascript/ajax.js'></script>";

$module_frbr = new module_frbr();

$module_frbr->proceed_header();

$id = intval($id);
$module_frbr->set_object_id($id);
$module_frbr->set_url_base($base_path.'/frbr.php?categ='.$categ);
$module_frbr->proceed();

// pied de page
$module_frbr->proceed_footer();

// deconnection MYSql
pmb_mysql_close();
