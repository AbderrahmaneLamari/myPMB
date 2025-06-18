<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: camera_upload.php,v 1.5 2021/05/28 14:19:40 arenou Exp $

$base_path     = ".";
$base_noheader = 1;
$base_nobody   = 1;
require_once ($base_path."/includes/init.inc.php");
require_once('./classes/encoding_normalize.class.php');

if(!isset($upload_url)) $upload_url = '';
$manag_cache=getimage_cache('', '', '', $upload_url, '', $pmb_book_pics_url, 1);
if($manag_cache["location"]){
	unlink($manag_cache["location"]);
}

$status = 0;
$upload_filename = '';
if(isset($_POST['upload_filename']) && isset($_POST['imgBase64'])) {
	$upload_filename = $_POST['upload_filename'];
	$ext = pathinfo($upload_filename, PATHINFO_EXTENSION);
	if($ext == 'php'){
	    print encoding_normalize::json_encode(array('status' => 0, 'message' => "not an image"));
	    //si c'est une fichier PHP, on arret de suite...
	    exit;
	}
	
	$rawData = $_POST['imgBase64'];
	
	$filteredData = explode(',', $rawData);
	$unencoded = base64_decode($filteredData[1]);
	// on s'assure que l'upload est bien une image !
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$infos = $finfo->buffer($unencoded);
	if(substr($infos,0,strpos($infos,'/')) !== "image"){
	    print encoding_normalize::json_encode(array('status' => 0, 'message' => "not an image"));
	    //si ce n'est pas le cas, on ne va plus loin !
	    exit;
	}
	
	$fp = fopen($upload_filename, 'w');
	if($fp) {
		fwrite($fp, $unencoded);
		fclose($fp);
		$status = 1;
	}
}
print encoding_normalize::json_encode(array('status' => $status, 'message' => $upload_filename));