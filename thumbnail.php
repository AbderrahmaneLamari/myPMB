<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: thumbnail.php,v 1.1.4.1 2023/10/27 13:47:17 dgoron Exp $

use Pmb\Thumbnail\Models\ThumbnailSourcesHandler;

global $class_path, $base_path, $base_auth, $base_title, $base_noheader, $base_nocheck, $base_nobody;
global $type, $id;
global $img_cache_type;

$base_path     = ".";
$base_auth     = ""; //"CIRCULATION_AUTH";
$base_title    = "";
$base_noheader = 1;
//$base_nocheck  = 1;
$base_nobody   = 1;

require_once ($base_path."/includes/init.inc.php");
require_once($class_path."/curl.class.php");
require_once("$base_path/includes/isbn.inc.php");
require_once($base_path."/admin/connecteurs/in/amazon/amazon.class.php");

session_write_close();

if(!empty($img_cache_type) && in_array($img_cache_type, ['png', 'webp'])) {
    global $pmb_img_cache_type;
    $pmb_img_cache_type = $img_cache_type;
}
if (ThumbnailSourcesHandler::checkType($type)) {
    $id = intval($id);
    $handler = new ThumbnailSourcesHandler();
    $handler->printImage($type, $id);
}