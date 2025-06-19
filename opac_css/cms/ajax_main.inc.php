<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.10 2022/11/14 10:30:47 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

switch($categ){
	case "document" :
		//Mise en cache des images
		//on ajoute des entêtes qui autorisent le navigateur à faire du cache...
		$headers = getallheaders();
		//une journée
		$offset = 60 * 60 * 24 ;
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) <= time())) {
			header('Last-Modified: '.$headers['If-Modified-Since'], true, 304);
			return;
		}else{
			header('Expired: '.gmdate("D, d M Y H:i:s", time() + $offset).' GMT', true);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT', true, 200);
		}
		$doc = new cms_document($id);
		switch($action){
			case "thumbnail" :
				$doc->render_thumbnail();
				break;	
			case "render" :
				global $mode;
				
				if($doc->get_num_storage()) {
					if($pmb_logs_activate) {
						generate_log();
					}
					session_write_close();
					$doc->render_doc($mode);
				}
				break;
		}
		break;
	case "module" :
		switch($action){
			case "ajax" :
				$element = new $elem($id);
				$response = $element->execute_ajax();
				ajax_http_send_response($response['content'],$response['content-type']);
				break;
			case "css" :
			case "js" :
				session_write_close();
				$element = new $elem($id);
				$response = $element->execute_ajax();
				ajax_http_send_response($response['content'],$response['content-type']);
				break;
		}
		break;	
	case "build" :
		switch($action){
			case "set_version" :
				$_SESSION["build_id_version"]=$value;
				ajax_http_send_response("ok ".$_SESSION["build_id_version"]);
			break;
		}
	break;
	
}