<?php
// +-------------------------------------------------+
// Â© 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: docwatch_datasource_rss.class.php,v 1.24.2.6 2021/11/26 12:52:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $msg;

require_once $class_path."/docwatch/datasources/docwatch_datasource.class.php";

class docwatch_datasource_rss extends docwatch_datasource{

	/**
	 * @return void
	 * @access public
	 */
	public function __construct($id=0) {
		
		parent::__construct($id);
	}
	
	protected function get_items_datas($link_datas){
		$link = (!empty($link_datas['rss_link']) ? $link_datas['rss_link'] : '');
		if( !filter_var($link, FILTER_VALIDATE_URL)) {
			return false;
		}
		
		$datas = array();
		$old_errors_value = false;
		$informations = array();
		$loaded=false;
		
		$aCurl = new Curl();
		//partage de résultat de recherche OPAC - on augmente à 5 secondes
		//l'appel s.php peut faire appel à une recherche multi-critères
		if(strpos($link, "/s.php?h=") !== false) {
		    $aCurl->timeout=15;
		} else {
		    $aCurl->timeout=2;
		}
		//surcharge du timeout s'il a été précisé dans la définition du sélecteur
		$timeout = (!empty($link_datas['curl_timeout']) ? intval($link_datas['curl_timeout']) : '');
		if(!empty($timeout) && is_int($timeout)) {
			$aCurl->timeout=$timeout;
		}
		$content = $aCurl->get($link);
		if(!$content || ($content->headers['Status-Code'] != 200) ) {
			return false;
		}
		$flux = $content->body;
		if(!$flux) {
			return false;
		}
		
		if(!isset($this->parameters['nb_max_elements'])) {
			$this->parameters['nb_max_elements']=0;
		}
		
		$rss = new domDocument();
		if(libxml_use_internal_errors(true)){
			$old_errors_value = true;
		}
		$loaded=$rss->loadXML($flux);
		if(!$loaded) {
			$libxml_errors = libxml_get_errors();
			foreach ($libxml_errors as $error) {
				$uniqid = cURL_log::prepare_error('curl_wrong_rss_format', 'docwatch');
				$uniqid = cURL_log::set_url_from($uniqid, $link);
				cURL_log::register($uniqid, cURL_log::get_formatted_message_libxml($error));
			}
			libxml_clear_errors();
			libxml_use_internal_errors($old_errors_value);
			return false;
		}
		$libxml_errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors($old_errors_value);
		
		//on ne prend en compte que les erreurs de type FATAL
		if(count($libxml_errors)) {
			foreach($libxml_errors as $k=>$error) {
				if($error->level !== LIBXML_ERR_FATAL) {
					unset($libxml_errors[$k]);
				}
			}
		}
		if(count($libxml_errors)) {
			foreach ($libxml_errors as $error) {
				$uniqid = cURL_log::prepare_error('curl_wrong_rss_format', 'docwatch');
				$uniqid = cURL_log::set_url_from($uniqid, $link);
				cURL_log::register($uniqid, cURL_log::get_formatted_message_libxml($error));
			}
			return false;
		}
		
		//les infos sur le flux...
		$sxe = new SimpleXMLElement($flux);
		$ns=$sxe->getNamespaces(true);
		if(!isset($ns["dc"])) {
			$ns["dc"] = '';
		}
		$informations['items'] =array();
		
		//Flux RSS
		if ($rss->getElementsByTagName("channel")->length > 0) {
			$channel = $rss->getElementsByTagName("channel")->item(0);
			$elements = array(
					'url'
			);
			$informations = $this->get_informations($channel,$elements,1);
			//on va lire les infos des items...
			$rss_items = $rss->getElementsByTagName("item");
			$elements = array(
					'title',
					'description',
					'link',
					'pubDate',
					'category',
    			    'content',
    			    'enclosure'
			);
			$count=0;
			for($i=0 ; $i<$rss_items->length ; $i++){
				if($this->parameters['nb_max_elements']==0 || $i < $this->parameters['nb_max_elements']){
					$informations['items'][$count]=$this->get_informations($rss_items->item($i),$elements,false);
					if($ns["dc"]){
					    if(is_object($rss->getElementsByTagNameNS($ns["dc"], 'date')->item($i))) {
					        $namespace_dc_date = $rss->getElementsByTagNameNS($ns["dc"], 'date')->item($i)->nodeValue;
					        if($namespace_dc_date){
					            $informations['items'][$count]['pubDate'] = str_replace(array("T","Z"), " ", $namespace_dc_date);
					        }
					    }
					    if(is_object($rss->getElementsByTagNameNS($ns["dc"], 'subject')->item($i))) {
							$namespace_dc_subject = $rss->getElementsByTagNameNS($ns["dc"], 'subject')->item($i)->nodeValue;
							if($namespace_dc_subject){
								$informations['items'][$count]['subject'] = $namespace_dc_subject;
							}
					    }
					}
					$count++;
				}
			}
			
		//Flux ATOM
		} elseif($rss->getElementsByTagName("feed")->length > 0) {
								
			$feed = $rss->getElementsByTagName("feed")->item(0);
			$atom_elements = array(
					'url',
			);
			$informations = $this->get_atom_informations($feed,$atom_elements,1);
			//on va lire les infos des entries...
			$informations['items'] =array();
			$entries = $rss->getElementsByTagName("entry");
			$atom_elements = array(
					'title',
					'link',
					'published',
					'content',
					'updated',
			        'enclosure'
			);
			for($i=0 ; $i<$entries->length ; $i++){
				if($this->parameters['nb_max_elements']==0 || $i < $this->parameters['nb_max_elements']){
					$informations['items'][]=$this->get_atom_informations($entries->item($i),$atom_elements,false);
				}
			}
		}
		foreach ($informations['items'] as $rss_item) {
			$data = array();
			$data["type"] = "rss";
			$data["title"] = $rss_item["title"];
			if(is_array($rss_item["description"])) {
				if(isset($rss_item["description"][0])) {
					$data["summary"] = $rss_item["description"][0];
				}
			} else {
				$data["summary"] = $rss_item["description"];
			}
			if(!isset($rss_item["content"])) $rss_item["content"] = '';
			$data["content"] = $rss_item["content"];
			$data["url"] = $rss_item["link"];
			//traitement de la date
			$date = '';
			if(!empty($rss_item['pubDate'])){
				$date = $rss_item['pubDate'];
			}else if(!empty($rss_item["updated"])){
				$date = $rss_item['updated'];
			}
			$data["publication_date"] = date('Y-m-d H:i:s',strtotime($date));
			$data["logo_url"] = $informations["url"];
			
//          $data["logo_url"] = get_url_icon("no_image_rss.png", true);
// 			if (!empty($rss_item["media"])) {
//     		    $headers = get_headers($rss_item["media"], true);
// 			    if (isset($headers["Content-Type"]) && substr($headers["Content-Type"], 0, 5) == "image") {
// 			        $data["logo_url"] = $rss_item["media"];
// 			    }
// 			} elseif(!empty($informations["url"])){
// 			    $data["logo_url"] = $informations["url"];
// 			}
			
			$data["descriptors"] = "";
			if (isset($rss_item["category"]) && is_array($rss_item["category"])) {
				$data["tags"] = array_map("strip_tags", $rss_item["category"]);
			}elseif (isset($rss_item["category"])) {
				$data["tags"] = strip_tags($rss_item["category"]);
			}
			$datas[] = $data;
		}
		return $datas;
	}
	
	protected function delete_media_tags($node) {
		
		//Cas particulier nom d'espace media (yahoo) : les balises sont doublées dans un nom d'espace "media"
		try {
			$items = $node->getElementsByTagNameNS('http://search.yahoo.com/mrss/','*');
			if($items->length>0){
				$node->removeChild($items->item(0));
			}
		} catch(Exception $e) {
		}
		
		return $node;
	}

	protected function get_informations($node,$elements,$first_only=false){

		$informations = array();
		
		$node = $this->delete_media_tags($node);
		
		foreach($elements as $element){
			$items = $node->getElementsByTagName($element);
			
			switch ($element) {
			    case "enclosure" :
		              $element = "media";
			        break;
			}
			
			if(isset($items->item(0)->nodeValue) && ($items->length == 1 || $first_only)){
			    if(!empty($items->item(0)->getAttribute('url'))){
    			    $informations[$element] = $this->charset_normalize($items->item(0)->getAttribute('url'),"utf-8");
			    } else {
			        $informations[$element] = $this->charset_normalize(preg_replace('/\s+/',' ',$items->item(0)->nodeValue),"utf-8");
			    }
			} else {
			    $informations[$element] = array();
				for($i=0 ; $i<$items->length ; $i++){
				    $informations[$element][] = $this->charset_normalize(preg_replace('/\s+/',' ',$items->item($i)->nodeValue),"utf-8");
				}
				$informations[$element] = array_unique($informations[$element]);
				if(count($informations[$element]) === 1){
				    $informations[$element] = $informations[$element][0];
				}
			}
		}
		return $informations;
	}
	
	protected function get_atom_informations($node,$atom_elements,$first_only=false){

		$informations = array();
		
		$node = $this->delete_media_tags($node);
		
		foreach($atom_elements as $atom_element){
			$items = $node->getElementsByTagName($atom_element);
			switch ($atom_element) {
				case "published" :
					$element = "pubDate";
					break;
				case "content" :
					$element = "description";
					break;
				default:
					$element = $atom_element;
					break;
			}
				
			if($items->length == 1 || $first_only){
				if ($element == "link") {
					$informations[$element] = $this->charset_normalize($items->item(0)->getAttribute('href'),"utf-8");
				} else {
					$informations[$element] = $this->charset_normalize($items->item(0)->nodeValue,"utf-8");
				}
			}else{
				if ($element == "link") {
					for($i=0 ; $i<$items->length ; $i++){
						$informations[$element][] = $this->charset_normalize($items->item(0)->getAttribute('href'),"utf-8");
					}
				} else {
					for($i=0 ; $i<$items->length ; $i++){
						$informations[$element][] = $this->charset_normalize($items->item($i)->nodeValue,"utf-8");
					}
				}
			}
		}
		return $informations;
	}
	
	public function get_available_selectors(){
		
		global $msg;
		return array(
				"docwatch_selector_rss" => $msg['dsi_docwatch_selector_rss_select']
		);
	}


}

