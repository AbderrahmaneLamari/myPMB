<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: thumbnail.php,v 1.3.2.2 2023/12/05 10:08:24 qvarin Exp $

use Pmb\Thumbnail\Models\ThumbnailSourcesHandler;

global $opac_opac_view_activate, $opac_view, $pmb_opac_view_class, $opac_default_style;
global $type, $id;

require_once("./includes/apache_functions.inc.php");

//on ajoute des ent�tes qui autorisent le navigateur � faire du cache...
$headers = getallheaders();
//une journ�e
$offset = 60 * 60 * 24 ;
if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) <= time())) {
	header('Last-Modified: '.$headers['If-Modified-Since'], true, 304);
	return;
} else {
	header('Expired: '.gmdate("D, d M Y H:i:s", time() + $offset).' GMT', true);
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT', true, 200);
}

$base_path=".";
require_once($base_path."/includes/init.inc.php");
require_once($base_path."/includes/error_report.inc.php") ;
require_once($base_path."/includes/global_vars.inc.php");
require_once($base_path.'/includes/opac_config.inc.php');

// r�cup�ration param�tres MySQL et connection � la base
require_once($base_path.'/includes/opac_db_param.inc.php');

// On vient de charger, le db_param, on regarde s'il y a une page de maintenace avant de faire la connexion � la BDD
// On le fait dans le sens la car on a besoin de la d�finition du charset pour pousser la page de maintenance dans le bon charset...
if (file_exists($base_path.'/temp/.'.DATA_BASE.'_maintenance')) {
	session_start();
	if (!($cms_build_activate || $_SESSION['cms_build_activate'])) {
		header("Content-Type: text/html; charset=$charset");
		print file_get_contents($base_path.'/temp/'.DATA_BASE.'_maintenance.html');
		exit;
	}
}

require_once($base_path.'/includes/opac_mysql_connect.inc.php');
$dbh = connection_mysql();

require_once($base_path."/includes/misc.inc.php");

//Sessions !! Attention, ce doit �tre imp�rativement le premier include (� cause des cookies)
require_once($base_path."/includes/session.inc.php");
require_once($base_path.'/includes/start.inc.php');
// r�cup�ration localisation
require_once($base_path.'/includes/localisation.inc.php');

//si les vues sont activ�es (� laisser apr�s le calcul des mots vides)
// Il n'est pas possible de chagner de vue � ce niveau
if($opac_opac_view_activate){
    $current_opac_view=(isset($_SESSION["opac_view"]) ? $_SESSION["opac_view"] : '');
    if($opac_view==-1){
        $_SESSION["opac_view"]="default_opac";
    }else if($opac_view)	{
        $_SESSION["opac_view"]=$opac_view*1;
    }
    $_SESSION['opac_view_query']=0;
    if(!$pmb_opac_view_class) $pmb_opac_view_class= "opac_view";
    require_once($base_path."/classes/".$pmb_opac_view_class.".class.php");

    $opac_view_class= new $pmb_opac_view_class((isset($_SESSION["opac_view"]) ? $_SESSION["opac_view"] : ''),$_SESSION["id_empr_session"]);
    if($opac_view_class->id){
        $opac_view_class->set_parameters();
        $opac_view_filter_class=$opac_view_class->opac_filters;
        $_SESSION["opac_view"]=$opac_view_class->id;
        if(!$opac_view_class->opac_view_wo_query) {
            $_SESSION['opac_view_query']=1;
        }
    } else {
        $_SESSION["opac_view"]=0;
    }
    $css=$_SESSION["css"]=$opac_default_style;
}

session_write_close();

if(!empty($img_cache_type) && in_array($img_cache_type, ['png', 'webp'])) {
    global $opac_img_cache_type;
    $opac_img_cache_type = $img_cache_type;
}
if (ThumbnailSourcesHandler::checkType($type)) {
    $id = intval($id);
    $handler = new ThumbnailSourcesHandler();
    $handler->printImage($type, $id);
}