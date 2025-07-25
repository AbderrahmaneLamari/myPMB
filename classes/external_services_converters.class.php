<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: external_services_converters.class.php,v 1.36 2022/01/03 15:46:57 dgoron Exp $

//
//Convertisseurs et cacheur de formats des r�sultats des services externes
//

global $base_path, $class_path, $include_path;

require_once("$base_path/admin/convert/export.class.php");
require_once("$base_path/admin/convert/convert.class.php");
require_once("$class_path/external_services_caches.class.php");
require_once("$class_path/mono_display.class.php");

if (version_compare(PHP_VERSION,'5','>=') && extension_loaded('xsl')) {
	require_once($include_path.'/xslt-php4-to-php5.inc.php');
}

class external_services_converter {
	public $object_type=0; //Type d'objet
	public $life_duration=600; //Dur�e de vie de l'objet converti, en secondes
	public $results=array();
	public $cache=NULL;
	public $params=array();
	
	public function __construct($object_type, $life_duration) {
		$this->object_type = intval($object_type);
		$this->life_duration = intval($life_duration);
		$this->cache = new external_services_cache('es_cache_blob', $life_duration);
	}
	
	public function set_params($new_params) {
		$this->params = $new_params;
	}
	
	public function convert_batch($objects, $format, $target_charset='iso-8859-1') {
		//Cette fonction va chercher les valeurs dans le cache si elle existent.
		//Si aucun r�sultat, pas de traitement
		if (!is_array($objects)) {
			$this->results = array();
			return;
		}
		array_walk($objects, function(&$a) {$a = intval($a);});//Soyons s�r de ne stocker que des entiers dans le tableau.
		$objects = array_unique($objects);
		
		if (!$objects) {
			$this->results = array();
			return;
		}

		//Initialisons tous avec des z�ros
		$this->results = array_combine($objects, array_fill(0, count($objects), 0));
		
		//Allons chercher dans le cache ce qui est encore bon
		$in_cache = $this->cache->get_objectref_contents($this->object_type, '', $format, $objects);
		$rawed = substr($format, 0, 9) == "raw_array";
		foreach ($in_cache as $object_ref => $object_content) {
			if ($rawed) {
				$this->results[$object_ref] = unserialize($object_content);
			} else {
				$this->results[$object_ref] = $object_content;
			}
		}
		
	}
	
	public function encache_value($object_id, $value, $format) {
		//Mise en cache d'une valeur
		$rawed = substr($format, 0, 9) == "raw_array";
		if ($rawed)
			$value = serialize($value);
		$this->cache->encache_objectref_contents($this->object_type, '', $format, array($object_id => $value));
	}
	
}

class external_services_converter_notices extends external_services_converter {
	
	public function convert_batch($objects, $format, $target_charset='iso-8859-1',$xslt="") {
		if (!$objects)
			return array();
		//Va chercher dans le cache les notices encore bonnes
		$format_ref = $format.'_C_'.$target_charset;
		if ($this->params["include_links"])
			$format_ref .= "_withlinks";
		if ($this->params["include_items"])
			$format_ref .= "_withitems";
		parent::convert_batch($objects, $format_ref, $target_charset);
		//Converti les notices qui 
		$this->convert_uncachednotices($format, $format_ref, $target_charset,$xslt);
		return $this->results;
	}

	public function convert_batch_to_pmb_xml($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		$xmlexport = new export($notices_to_convert);
		$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
		$parametres = array();

		if (is_array($this->params['include_links'])) {
			$parametres=$this->params['include_links'];
		} else if ($this->params["include_links"]) {
			$parametres["genere_lien"]=1;//Notices li�es, relations entre notices
	
			$parametres["mere"]=1; //Exporter les liens vers les notices m�res
			$parametres["notice_mere"]=0;//Exporter aussi les notices m�res li�es 
			
			$parametres["fille"]=1; //Exporter les liens vers les notices filles
			$parametres["notice_fille"]=0;//Exporter aussi les notices filles li�es 
			
			$parametres["art_link"]=1;//Exporter les liens vers les articles pour les notices de p�rio
			$parametres["notice_art"]=0;//Exporter aussi les articles pour les notices de p�rio
			$parametres["bulletinage"]=0;//Exporter le bulletinage pour les notices de p�rio
		
			$parametres["bull_link"]=1;//Exporter les liens vers les bulletins pour les notices d'article
			$parametres["perio_link"]=1; //Exporter les liens vers les p�riodiques pour les notices d'articles
			$parametres["notice_perio"]=0;//Exporter aussi les p�riodiques pour les notices d'articles
		}
		if ($this->params["include_authorite_ids"]) {
			$parametres["include_authorite_ids"] = true;
		}
		if (!empty($this->params["map"])) {
		    $parametres["map"] = true;
		}
		if (!empty($this->params["clean_html"])) {
			$parametres["clean_html"] = true;
		}
		$parametres["docnum"]=1;
		$keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
		while($xmlexport->get_next_notice("", array(), array(), $keep_expl, $parametres)) {
			$xmlexport->toxml();
			if ($current_notice_id != -1) {
				$this->results[$current_notice_id] = $xmlexport->notice;
				//La classe export exporte ses donn�es dans la charset de la base.
				//Convertissons si besoin
				if ($charset!='utf-8' && $target_charset == 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"UTF-8","Windows-1252");
					}else{
						$this->results[$current_notice_id] = utf8_encode($this->results[$current_notice_id]);
					}
				}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"Windows-1252","UTF-8");
					}else{
						$this->results[$current_notice_id] = utf8_decode($this->results[$current_notice_id]);
					}
				}
				$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
			}
		}
	}

	public function convert_batch_to_json($notices_to_convert, $target_charset='iso-8859-1') {
			global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		$xmlexport = new export($notices_to_convert);
		$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
		$parametres = array();
		if (is_array($this->params['include_links'])) {
			$parametres=$this->params['include_links'];
		} else if ($this->params["include_links"]) {
			$parametres["genere_lien"]=1;//Notices li�es, relations entre notices
	
			$parametres["mere"]=1; //Exporter les liens vers les notices m�res
			$parametres["notice_mere"]=0;//Exporter aussi les notices m�res li�es 
			
			$parametres["fille"]=1; //Exporter les liens vers les notices filles
			$parametres["notice_fille"]=0;//Exporter aussi les notices filles li�es 
			
			$parametres["art_link"]=1;//Exporter les liens vers les articles pour les notices de p�rio
			$parametres["notice_art"]=0;//Exporter aussi les articles pour les notices de p�rio
			$parametres["bulletinage"]=0;//Exporter le bulletinage pour les notices de p�rio
		
			$parametres["bull_link"]=1;//Exporter les liens vers les bulletins pour les notices d'article
			$parametres["perio_link"]=1; //Exporter les liens vers les p�riodiques pour les notices d'articles
			$parametres["notice_perio"]=0;//Exporter aussi les p�riodiques pour les notices d'articles
		}
		if ($this->params["include_authorite_ids"]) {
			$parametres["include_authorite_ids"] = true;
		}
		if (!empty($this->params["map"])) {
		    $parametres["map"] = true;
		}
		if (!empty($this->params["clean_html"])) {
			$parametres["clean_html"] = true;
		}
		$parametres["docnum"]=1;
		$keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
		while($xmlexport->get_next_notice("", array(), array(), $keep_expl, $parametres)) {
			$xmlexport->tojson();
			if ($current_notice_id != -1) {
				$this->results[$current_notice_id] = $xmlexport->notice;
				//La classe export exporte ses donn�es dans la charset de la base.
				//Convertissons si besoin
				if ($charset!='utf-8' && $target_charset == 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"UTF-8","Windows-1252");
					}else{
						$this->results[$current_notice_id] = utf8_encode($this->results[$current_notice_id]);
					}
				}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"Windows-1252","UTF-8");
					}else{
						$this->results[$current_notice_id] = utf8_decode($this->results[$current_notice_id]);
					}
				}
				$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
			}
		}
	}

	public function convert_batch_to_json_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id)
			$this->results[$anotice_id] = json_encode($this->results[$anotice_id]);
	}
	
	public function convert_batch_to_serialized($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		$xmlexport = new export($notices_to_convert);
		$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
		$parametres = array();
		if (is_array($this->params['include_links'])) {
			$parametres=$this->params['include_links'];
		} else if ($this->params["include_links"]) {
			$parametres["genere_lien"]=1;//Notices li�es, relations entre notices
	
			$parametres["mere"]=1; //Exporter les liens vers les notices m�res
			$parametres["notice_mere"]=0;//Exporter aussi les notices m�res li�es 
			
			$parametres["fille"]=1; //Exporter les liens vers les notices filles
			$parametres["notice_fille"]=0;//Exporter aussi les notices filles li�es 
			
			$parametres["art_link"]=1;//Exporter les liens vers les articles pour les notices de p�rio
			$parametres["notice_art"]=0;//Exporter aussi les articles pour les notices de p�rio
			$parametres["bulletinage"]=0;//Exporter le bulletinage pour les notices de p�rio
		
			$parametres["bull_link"]=1;//Exporter les liens vers les bulletins pour les notices d'article
			$parametres["perio_link"]=1; //Exporter les liens vers les p�riodiques pour les notices d'articles
			$parametres["notice_perio"]=0;//Exporter aussi les p�riodiques pour les notices d'articles
		}
		if ($this->params["include_authorite_ids"]) {
			$parametres["include_authorite_ids"] = true;
		}
		if (!empty($this->params["map"])) {
		    $parametres["map"] = true;
		}
		if (!empty($this->params["clean_html"])) {
			$parametres["clean_html"] = true;
		}
		$parametres["docnum"]=1;
		$keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
		while($xmlexport->get_next_notice("", array(), array(), $keep_expl, $parametres)) {
			$xmlexport->toserialized();
			if ($current_notice_id != -1) {
				$this->results[$current_notice_id] = $xmlexport->notice;
				//La classe export exporte ses donn�es dans la charset de la base.
				//Convertissons si besoin
				if ($charset!='utf-8' && $target_charset == 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"UTF-8","Windows-1252");
					}else{
						$this->results[$current_notice_id] = utf8_encode($this->results[$current_notice_id]);
					}
				}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
					if(function_exists("mb_convert_encoding")){
						$this->results[$current_notice_id] = mb_convert_encoding($this->results[$current_notice_id],"Windows-1252","UTF-8");
					}else{
						$this->results[$current_notice_id] = utf8_decode($this->results[$current_notice_id]);
					}
				}
				$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
			}
		}
	}
	
	public function convert_batch_to_serialized_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id) {
			$this->results[$anotice_id] = serialize($this->results[$anotice_id]);
		}
	}	
	
	public function convert_batch_to_php_array($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		$xmlexport = new export($notices_to_convert);
		$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
		$parametres = array();
		if (is_array($this->params['include_links'])) {
			$parametres=$this->params['include_links'];
		} else if ($this->params["include_links"]) {
			$parametres["genere_lien"]=1;//Notices li�es, relations entre notices
	
			$parametres["mere"]=1; //Exporter les liens vers les notices m�res
			$parametres["notice_mere"]=0;//Exporter aussi les notices m�res li�es 
			
			$parametres["fille"]=1; //Exporter les liens vers les notices filles
			$parametres["notice_fille"]=0;//Exporter aussi les notices filles li�es 
			
			$parametres["art_link"]=1;//Exporter les liens vers les articles pour les notices de p�rio
			$parametres["notice_art"]=0;//Exporter aussi les articles pour les notices de p�rio
			$parametres["bulletinage"]=0;//Exporter le bulletinage pour les notices de p�rio
		
			$parametres["bull_link"]=1;//Exporter les liens vers les bulletins pour les notices d'article
			$parametres["perio_link"]=1; //Exporter les liens vers les p�riodiques pour les notices d'articles
			$parametres["notice_perio"]=0;//Exporter aussi les p�riodiques pour les notices d'articles
		}
		if ($this->params["include_authorite_ids"]) {
			$parametres["include_authorite_ids"] = true;
		}
		if (!empty($this->params["map"])) {
		    $parametres["map"] = true;
		}
		if (!empty($this->params["clean_html"])) {
			$parametres["clean_html"] = true;
		}
		$parametres["docnum"]=1;
		$keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
		while($xmlexport->get_next_notice("", array(), array(), $keep_expl, $parametres)) {
			$xmlexport->to_raw_array();
			if ($current_notice_id != -1) {
				$xmlexport_notice = $xmlexport->notice;
				$aresult = $xmlexport_notice;
				$aresult = array();
				$headers = array();
				if (isset($xmlexport_notice['rs']["value"]))
					$headers[] = array("name" => "rs", "value" => $xmlexport_notice['rs']["value"]);
				if (isset($xmlexport_notice['dt']["value"]))
					$headers[] = array("name" => "dt", "value" => $xmlexport_notice['dt']["value"]);
				if (isset($xmlexport_notice['bl']["value"]))
					$headers[] = array("name" => "bl", "value" => $xmlexport_notice['bl']["value"]);
				if (isset($xmlexport_notice['hl']["value"]))
					$headers[] = array("name" => "hl", "value" => $xmlexport_notice['hl']["value"]);
				if (isset($xmlexport_notice['el']["value"]))
					$headers[] = array("name" => "el", "value" => $xmlexport_notice['el']["value"]);
				if (isset($xmlexport_notice['ru']["value"]))
					$headers[] = array("name" => "ru", "value" => $xmlexport_notice['ru']["value"]);
				$aresult["id"] = $current_notice_id;
				$aresult["header"] = $headers;
				$aresult["f"] = $xmlexport_notice['f'];
				foreach ($aresult["f"] as &$af) {
					$af["ind"] = isset($af["ind"]) ? $af["ind"] : "";
					$af["id"] = isset($af["id"]) ? $af["id"] : "";
					$af["value"] = isset($af["value"]) ? $af["value"] : "";
					//La classe export exporte ses donn�es dans la charset de la base.
					//Convertissons si besoin
					if($af["value"]){
    					if ($charset!='utf-8' && $target_charset == 'utf-8'){
    					    if(function_exists("mb_convert_encoding")){
    					        $af["value"] = mb_convert_encoding($af["value"],"UTF-8","Windows-1252");
    					    }else{
    					        $af["value"] = utf8_encode($af["value"]);
    					    }
    					}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
    					    if(function_exists("mb_convert_encoding")){
    					        $af["value"] = mb_convert_encoding($af["value"],"Windows-1252","UTF-8");
    					    }else{
    					        $af["value"] = utf8_decode($af["value"]);
    					    }
    					}
					}
					$af["s"] = isset($af["s"]) ? $af["s"] : array();
					foreach ($af["s"] as &$as) {
						$as["value"] = isset($as["value"]) ? $as["value"] : "";
						$as["c"] = isset($as["c"]) ? $as["c"] : "";
						//La classe export exporte ses donn�es dans la charset de la base.
						//Convertissons si besoin
						if ($charset!='utf-8' && $target_charset == 'utf-8'){
							if(function_exists("mb_convert_encoding")){
								$as["value"] = mb_convert_encoding($as["value"],"UTF-8","Windows-1252");
							}else{
								$as["value"] = utf8_encode($as["value"]);
							}
						}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
							if(function_exists("mb_convert_encoding")){
								$as["value"] = mb_convert_encoding($as["value"],"Windows-1252","UTF-8");
							}else{
								$as["value"] = utf8_decode($as["value"]);
							}
						}
					}

				}
				$this->results[$current_notice_id] = $aresult;
				$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
			}
		}
	}
	
	public function convert_batch_to_php_array_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		$xmlexport = new export($notices_to_convert);
		$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
		$parametres = array();
		if (is_array($this->params['include_links'])) {
			$parametres=$this->params['include_links'];
		} else if ($this->params["include_links"]) {
			$parametres["genere_lien"]=1;//Notices li�es, relations entre notices
	
			$parametres["mere"]=1; //Exporter les liens vers les notices m�res
			$parametres["notice_mere"]=0;//Exporter aussi les notices m�res li�es 
			
			$parametres["fille"]=1; //Exporter les liens vers les notices filles
			$parametres["notice_fille"]=0;//Exporter aussi les notices filles li�es 
			
			$parametres["art_link"]=1;//Exporter les liens vers les articles pour les notices de p�rio
			$parametres["notice_art"]=0;//Exporter aussi les articles pour les notices de p�rio
			$parametres["bulletinage"]=0;//Exporter le bulletinage pour les notices de p�rio
		
			$parametres["bull_link"]=1;//Exporter les liens vers les bulletins pour les notices d'article
			$parametres["perio_link"]=1; //Exporter les liens vers les p�riodiques pour les notices d'articles
			$parametres["notice_perio"]=0;//Exporter aussi les p�riodiques pour les notices d'articles
		}
		if ($this->params["include_authorite_ids"]) {
			$parametres["include_authorite_ids"] = true;
		}
		if (!empty($this->params["map"])) {
		    $parametres["map"] = true;
		}
		if (!empty($this->params["clean_html"])) {
			$parametres["clean_html"] = true;
		}
		$parametres["docnum"]=1;
		$keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
		while($xmlexport->get_next_notice("", array(), array(), $keep_expl, $parametres)) {
			$xmlexport->to_raw_array();
			if ($current_notice_id != -1) {
				$xmlexport_notice = $xmlexport->notice;
				$aresult = array();
				$headers = array();
				if (isset($xmlexport_notice['rs']["value"]))
					$headers["rs"] = $xmlexport_notice['rs']["value"];
				if (isset($xmlexport_notice['dt']["value"]))
					$headers["dt"] = $xmlexport_notice['dt']["value"];
				if (isset($xmlexport_notice['bl']["value"]))
					$headers["bl"] = $xmlexport_notice['bl']["value"];
				if (isset($xmlexport_notice['hl']["value"]))
					$headers["hl"] = $xmlexport_notice['hl']["value"];
				if (isset($xmlexport_notice['el']["value"]))
					$headers["el"] = $xmlexport_notice['el']["value"];
				if (isset($xmlexport_notice['ru']["value"]))
					$headers["ru"] = $xmlexport_notice['ru']["value"];
				$aresult["id"] = $current_notice_id;
				$aresult["header"] = $headers;
				$aresult["f"] = array();
				foreach ($xmlexport_notice['f'] as &$af) {
					if (!isset($af["c"]))
						continue;
					if (!isset($aresult["f"][$af["c"]]))
						$aresult["f"][$af["c"]] = array();
					$arf = array();
					$arf["ind"] = isset($af["ind"]) ? $af["ind"] : "";
					$arf["id"] = isset($af["id"]) ? $af["id"] : "";
					if (isset($af["s"])) {
						foreach ($af["s"] as &$as) {
							//La classe export exporte ses donn�es dans la charset de la base.
							//Convertissons si besoin
							$value = $as["value"];
							if ($charset!='utf-8' && $target_charset == 'utf-8'){
								if(function_exists("mb_convert_encoding")){
									$value = mb_convert_encoding($value,"UTF-8","Windows-1252");
								}else{
									$value = utf8_encode($value);
								}
							}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
								if(function_exists("mb_convert_encoding")){
									$value = mb_convert_encoding($value,"Windows-1252","UTF-8");
								}else{
									$value = utf8_decode($value);
								}
							}
							if (isset($arf[$as["c"]]) && !is_array($arf[$as["c"]]))
								$arf[$as["c"]] = array($arf[$as["c"]]);
							if (isset($arf[$as["c"]]) && is_array($arf[$as["c"]]))
								$arf[$as["c"]][] = $value;
							else
								$arf[$as["c"]] = $value;
						}
					}
					else if (isset($af["value"])) {
						$arf["value"] = $af["value"];
					}

					$aresult["f"][$af["c"]][] = $arf;
				}
				$this->results[$current_notice_id] = $aresult;
				$current_notice_id = $xmlexport->notice_list[$xmlexport->current_notice];
			}
		}
	}
	
	
	public function apply_xsl_to_xml($xml, $xsl, $params) {
		global $charset;
		$xh = xslt_create();
		xslt_set_encoding($xh, $charset);
		$arguments = array(
	   	  '/_xml' => $xml,
	   	  '/_xsl' => $xsl
		);
		$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments, $params);
		xslt_free($xh);
		return $result;		
	}

	public function convert_batch_to_dublin_core($notices_to_convert, $target_charset,$xsl_pmbxmlunimarc_to_dc = "") {
		global $base_path, $opac_url_base;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		//Un petit tour en xml en utf-8 et apr�s on convertit par xsl
		$this->convert_batch_to_pmb_xml($notices_to_convert,'utf-8');
		
		//Allons chercher la feuille de style
		if(!$xsl_pmbxmlunimarc_to_dc){
			$xsl_pmbxmlunimarc_to_dc = file_get_contents($base_path."/admin/convert/imports/pmbxml2dc/pmbxmlunimarc2dc.xsl");
		}
	
		foreach ($notices_to_convert as $anotice_id) {
			if (!$this->results[$anotice_id])
				continue;
			$pmbxmlunimarc_version = '<?xml version="1.0" encoding="'.$target_charset.'"?><unimarc>'.$this->results[$anotice_id]."</unimarc>";
			$converted_version = $this->apply_xsl_to_xml($pmbxmlunimarc_version, $xsl_pmbxmlunimarc_to_dc, array("notice_url_base" => $opac_url_base));
			$converted_version = preg_replace('/^<\?xml[^>]*\?>/', "", $converted_version);

			
			//Cette conversion sort de l'utf-8
			if ($target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$converted_version = mb_convert_encoding($converted_version,"Windows-1252","UTF-8");
				}else{
					$converted_version = utf8_decode($converted_version);
				}
			}
			$this->results[$anotice_id] = $converted_version;
		}
	}
	
	//Utilise les fonctions de admin/convert pour faire une conversion perso
	public function convert_batch_to_adminconvert_script($notices_to_convert, $the_conversion, $target_charset) {
	    global $base_path, $class_path, $charset, $opac_url_base;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		//Un petit tour en xml dans le charset de la base et apr�s on invoque la classe de conversion
			$special_export = false;
			if($the_conversion['special_export'] =='yes') {
			    //L'export est special et utilise la fonction _export_ dans le fichier export.inc.php du repertoire de conversion
			    try {
			        $export_file = ("$base_path/admin/convert/imports/{$the_conversion['path']}/export.inc.php");
			        if(file_exists($export_file) && !function_exists('_export_')) {
			            require_once($export_file);
			        }
			        if(function_exists('_export_')) {
			            $special_export = true;
			        }
			    } catch (Exception $e) {}
			    
			}
			
			if($special_export==true) {
			    
			    $keep_expl = isset($this->params["include_items"]) && $this->params["include_items"];
			    foreach($notices_to_convert as $v) {
			        $this->results[$v] = _export_($v, $keep_expl);
			    }
			    
			}else {
			    //Si erreur, on utilise la fonction d'export standard
			    $this->convert_batch_to_pmb_xml($notices_to_convert, $charset);
			}
			
			$conv = new convert("", $the_conversion["position"], true);

		foreach ($notices_to_convert as $anotice_id) {
			if (!$this->results[$anotice_id])
				continue;
			$conv->prepared_notice = $this->results[$anotice_id];
			$converted_version = $conv->transform(true);
			
			if ($the_conversion["output_charset"] == 'utf-8' && $target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$converted_version = mb_convert_encoding($converted_version,"Windows-1252","UTF-8");
				}else{
					$converted_version = utf8_decode($converted_version);
				}
			} else if ($the_conversion["output_charset"] != 'utf-8' && $target_charset == 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$converted_version = mb_convert_encoding($converted_version,"UTF-8","Windows-1252");
				}else{
					$converted_version = utf8_encode($converted_version);
				}
			}
			$this->results[$anotice_id] = $converted_version;
		}
	}

	public function convert_batch_to_header($notices_to_convert, $target_charset) {
		global $charset,$include_path,$base_path;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		foreach ($notices_to_convert as $anotice_id) {
			$monod = new mono_display($anotice_id, 0, '', 0, '', '', '', 0, 1, 0, 0, '', 0, true, false, 0);
			if (!empty($this->params["clean_html"])) {
				$this->results[$anotice_id] = strip_tags($monod->header);
			} else {
				$this->results[$anotice_id] = $monod->header;
			}
			
			if ($charset!='utf-8' && $target_charset == 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"UTF-8","Windows-1252");
				}else{
					$this->results[$anotice_id] = utf8_encode($this->results[$anotice_id]);
				}
			}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"Windows-1252","UTF-8");
				}else{
					$this->results[$anotice_id] = utf8_decode($this->results[$anotice_id]);
				}
			}
		}
	}
	
	public function convert_batch_to_isbd($notices_to_convert, $target_charset) {
		global $charset,$include_path,$base_path;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		foreach ($notices_to_convert as $anotice_id) {
			$monod = new mono_display($anotice_id, 1, '', 0, '', '', '', 0, 1, 0, 0, '', 0, true, false, 0);
			if (!empty($this->params["clean_html"])) {
				$this->results[$anotice_id] = strip_tags($monod->isbd);
			} else {
				$this->results[$anotice_id] = $monod->isbd;
			}
			
			if ($charset!='utf-8' && $target_charset == 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"UTF-8","Windows-1252");
				}else{
					$this->results[$anotice_id] = utf8_encode($this->results[$anotice_id]);
				}
			}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"Windows-1252","UTF-8");
				}else{
					$this->results[$anotice_id] = utf8_decode($this->results[$anotice_id]);
				}
			}
		}
	}
	
	public function convert_batch_to_isbd_suite($notices_to_convert, $target_charset) {
		global $charset,$include_path,$base_path;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		foreach ($notices_to_convert as $anotice_id) {
			$monod = new mono_display($anotice_id, 6, '', 0, '', '', '', 0, 1, 0, 0, '', 0, true, false, 0);
			if (!empty($this->params["clean_html"])) {
				$this->results[$anotice_id] = strip_tags($monod->isbd);
			} else {
				$this->results[$anotice_id] = $monod->isbd;
			}
			
			if ($charset!='utf-8' && $target_charset == 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"UTF-8","Windows-1252");
				}else{
					$this->results[$anotice_id] = utf8_encode($this->results[$anotice_id]);
				}
			}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$this->results[$anotice_id] = mb_convert_encoding($this->results[$anotice_id],"Windows-1252","UTF-8");
				}else{
					$this->results[$anotice_id] = utf8_decode($this->results[$anotice_id]);
				}
			}
		}
	}
	
	public function convert_batch_to_personnal_xslt($notices_to_convert, $target_charset,$xslt) {
		global $base_path, $charset, $opac_url_base;
		if (!$notices_to_convert || !$xslt) //Rien � faire? On fait rien
			return;
		//Un petit tour en xml et apr�s on converti par xsl
		$this->convert_batch_to_pmb_xml($notices_to_convert);
		
		foreach ($notices_to_convert as $anotice_id) {
			if (!$this->results[$anotice_id])
				continue;
			$pmbxmlunimarc_version = '<?xml version="1.0" encoding="'.$charset.'"?><unimarc>'.$this->results[$anotice_id]."</unimarc>";
			$converted_version = $this->apply_xsl_to_xml($pmbxmlunimarc_version, $xslt, array("notice_url_base" => $opac_url_base));
			$converted_version = preg_replace('/^<\?xml[^>]*\?>/', "", $converted_version);

			//Cette conversion sort de l'utf-8
			if ($target_charset != 'utf-8'){
				if(function_exists("mb_convert_encoding")){
					$converted_version = mb_convert_encoding($converted_version,"Windows-1252","UTF-8");
				}else{
					$converted_version = utf8_decode($converted_version);
				}
			}

			$this->results[$anotice_id] = $converted_version;
		}
	}
	
	public function convert_uncachednotices($format, $format_ref, $target_charset='iso-8859-1',$xslt="") {
		$notices_to_convert=array();

		foreach ($this->results as $notice_id => $aresult) {
			if (!$aresult && $notice_id) {
				$notices_to_convert[] = $notice_id;
			}
		}	

		if ((substr($format, 0, 8) == "convert:")||(substr($format, 0, 8) == "convert_")) {
			//C'est une conversion par script admin/convert
			$convert_path = substr($format, 8);
			
			//Trouvons la position de la conversion pour invoquer la classe de conversion
			$the_conversion = NULL;
			$catalog = $this->get_export_possibilities(false);
			foreach ($catalog as $aconvert) {
				if ($aconvert["path"] == $convert_path) {
					$the_conversion = $aconvert;
				}
			}
			
			if (!$the_conversion) {
				//Oups! pas trouv�
				//Renvoyons des strings vides.
				foreach($notices_to_convert as $anotice_id) {
					$this->results[$anotice_id] = "";
				}
			}
			else {
				//C'est parti!
				$this->convert_batch_to_adminconvert_script($notices_to_convert, $the_conversion, $target_charset);
			}
			
		}
		else {
			//Conversion builtin
			switch ($format) {
				case "pmb_xml_unimarc":
					$this->convert_batch_to_pmb_xml($notices_to_convert, $target_charset);
					break;
				case "json_unimarc":
					$this->convert_batch_to_json($notices_to_convert, $target_charset);
					break;
				case "json_unimarc_assoc":
					$this->convert_batch_to_json_assoc($notices_to_convert, $target_charset);
					break;
				case "serialized_unimarc":
					$this->convert_batch_to_serialized($notices_to_convert, $target_charset);
					break;
				case "serialized_unimarc_assoc":
					$this->convert_batch_to_serialized_assoc($notices_to_convert, $target_charset);
					break;
				case "raw_array":
					$this->convert_batch_to_php_array($notices_to_convert, $target_charset);
					break;
				case "raw_array_assoc":
					$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
					break;
				case "header":
					$this->convert_batch_to_header($notices_to_convert, $target_charset);
					break;
				case "isbd":
					$this->convert_batch_to_isbd($notices_to_convert, $target_charset);
					break;
				case "isbd_suite":
					$this->convert_batch_to_isbd_suite($notices_to_convert, $target_charset);
					break;
				case "xslt_transform":
					$this->convert_batch_to_personnal_xslt($notices_to_convert, $target_charset,$xslt);
					break;
				case "dc":
				case "oai_dc":
					$this->convert_batch_to_dublin_core($notices_to_convert, $target_charset,$xslt);
					break;
				default:
					//Par d�faut on renvoi juste le notice_id
					foreach($notices_to_convert as $anotice_id) {
						$this->results[$anotice_id] = $anotice_id;
					}
					break;
			}
		}

		// Calcule des notices � mettre en cache
		$record_put_in_cache = [];
		$record_put_in_cache = $notices_to_convert;
		
		$ids_notice = array_keys($this->results);
		$index = count($ids_notice);
		for ($i = 0; $i < $index; $i++) {
		    if (!in_array($ids_notice[$i], $record_put_in_cache)) {
		        $record_put_in_cache[] = $ids_notice[$i];
		    }
		}
		
		// Cachons les notices converties maintenant.
		$index = count($record_put_in_cache);
		for ($i = 0; $i < $index; $i++) {
		    $this->encache_value($record_put_in_cache[$i], $this->results[$record_put_in_cache[$i]], $format_ref);
		}
	}
	
	//Cette fonction parse les diff�rents catalogues de admin/convert et liste les conversions qui exportent en xml
	public static function get_export_possibilities($only_xml=true) {
		global $base_path;
		$result = array();
		if (file_exists($base_path."/admin/convert/imports/catalog_subst.xml")) {
			$catalog_xml = file_get_contents($base_path."/admin/convert/imports/catalog_subst.xml");
		} else {
			$catalog_xml = file_get_contents($base_path."/admin/convert/imports/catalog.xml");
		}
		$catalog = _parser_text_no_function_($catalog_xml);
		$count = 0;
		//Parsons le catalogue
		if (isset($catalog["CATALOG"][0]["ITEM"]))
			foreach ($catalog["CATALOG"][0]["ITEM"] as $aconverttype) {
				if (isset($aconverttype["EXPORT"]) && $aconverttype["EXPORT"] == "yes") {
					$path = $aconverttype["PATH"];
					if ($path) {
						//Regardons si cette conversion sort du xml
						$export_xml = file_get_contents($base_path."/admin/convert/imports/$path/params.xml");
						$params = _parser_text_no_function_($export_xml);
						if (isset($params["PARAMS"][0]["OUTPUT"][0]["TYPE"])) {
							$output_type = $params["PARAMS"][0]["OUTPUT"][0]["TYPE"];
							if (!$only_xml || (strtolower($output_type) == 'xml')) {
								//Oui? on l'ajoute au resultat
								$conv_charset = isset($params["PARAMS"][0]["OUTPUT"][0]["CHARSET"]) ? $params["PARAMS"][0]["OUTPUT"][0]["CHARSET"] : 'iso-8859-1';
								$special_export = isset($params["PARAMS"][0]["INPUT"][0]["SPECIALEXPORT"]) ? $params["PARAMS"][0]["INPUT"][0]["SPECIALEXPORT"]  : '';
								$result[] = array(
									"position" => $count,
									"caption" => $aconverttype["EXPORTNAME"],
									"path" => $path,
									"output_charset" => $conv_charset,
								    	"special_export" => $special_export,
								);
							}
						}
					}
				}
				$count++;
			}
		return $result;
	}
}

class external_services_converter_external_notices extends external_services_converter {
	
	public function convert_batch($objects, $format, $target_charset='iso-8859-1') {
		if (!$objects)
			return array();
		//Va chercher dans le cache les notices encore bonnes
		$format_ref = $format.'_C_'.$target_charset;
		parent::convert_batch($objects, $format_ref, $target_charset);
		//Converti les notices qui 
		$this->convert_uncachednotices($format, $format_ref, $target_charset);
		return $this->results;
	}

	public function get_notice_unimarc_array($notice_id) {
		$requete = "SELECT source_id FROM external_count WHERE rid=".addslashes($notice_id);
		$myQuery = pmb_mysql_query($requete);
		if (!pmb_mysql_num_rows($myQuery))
			return FALSE;
		$source_id = pmb_mysql_result($myQuery, 0, 0);
		if (!$source_id)
			return FALSE;

		$requete="select * from entrepot_source_".$source_id." where recid='".addslashes($notice_id)."' order by ufield,field_order,usubfield,subfield_order,value";
		$myQuery = pmb_mysql_query($requete);
		$unimarc = array('f' => array());
		if(pmb_mysql_num_rows($myQuery)) {
			$field_order = $subfield_order = 0;
			while ($l=pmb_mysql_fetch_object($myQuery)) {
				if (in_array($l->ufield, array('bl', 'rs', 'dt', 'el', 'hl', 'ru'))) {
					$unimarc[$l->ufield]['value'] = $l->value;
					continue;
				}
				$unimarc['f'][$l->field_order]['c'] = $l->ufield;
				$unimarc['f'][$l->field_order]['ind'] = '';
				$unimarc['f'][$l->field_order]['id'] = '';
				$unimarc['f'][$l->field_order]['s'][$l->subfield_order] = array('c' => $l->usubfield, 'value' => $l->value);
				if($l->field_order > $field_order)
				$field_order = $l->field_order;
				if($l->ufield == "801"){
					if($l->subfield_order > $subfield_order)
						$subfield_order= $l->subfield_order;
				}
			}
			//on ajoute le nom de source en 801$9
			$rqt = "select name from connectors_sources where source_id ='".$source_id."'";
			$res = pmb_mysql_query($rqt);
			if(pmb_mysql_num_rows($res)){
				$unimarc['f'][$field_order+1]['c'] = "801";
				$unimarc['f'][$field_order+1]['ind'] = '';
				$unimarc['f'][$field_order+1]['id'] = '';
				$unimarc['f'][$field_order+1]['s'][$subfield_order+1] = array('c' => "9", 'value' => pmb_mysql_result($res,0,0));				
			}
		}
		$unimarc['f'] = array_values($unimarc['f']);
		foreach($unimarc['f'] as &$afield) {
			$afield['s'] = array_values($afield['s']);
			unset($afield);
		}
		
		return $unimarc;
	}
		
	public function convert_batch_to_php_array($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		foreach($notices_to_convert as $anotice_id)  {
			$xmlexport_notice = $this->get_notice_unimarc_array($anotice_id);
			if (!$xmlexport_notice)
				continue;
			$aresult = $xmlexport_notice;
			$aresult = array();
			$headers = array();
			if (isset($xmlexport_notice['rs']["value"]))
				$headers[] = array("name" => "rs", "value" => $xmlexport_notice['rs']["value"]);
			if (isset($xmlexport_notice['dt']["value"]))
				$headers[] = array("name" => "dt", "value" => $xmlexport_notice['dt']["value"]);
			if (isset($xmlexport_notice['bl']["value"]))
				$headers[] = array("name" => "bl", "value" => $xmlexport_notice['bl']["value"]);
			if (isset($xmlexport_notice['hl']["value"]))
				$headers[] = array("name" => "hl", "value" => $xmlexport_notice['hl']["value"]);
			if (isset($xmlexport_notice['el']["value"]))
				$headers[] = array("name" => "el", "value" => $xmlexport_notice['el']["value"]);
			if (isset($xmlexport_notice['ru']["value"]))
				$headers[] = array("name" => "ru", "value" => $xmlexport_notice['ru']["value"]);
			$aresult["id"] = $anotice_id;
			$aresult["header"] = $headers;
			$aresult["f"] = $xmlexport_notice['f'];
			foreach ($aresult["f"] as &$af) {
				$af["ind"] = isset($af["ind"]) ? $af["ind"] : "";
				$af["id"] = isset($af["id"]) ? $af["id"] : "";
				$af["s"] = isset($af["s"]) ? $af["s"] : array();
				foreach ($af["s"] as &$as) {
					$as["value"] = isset($as["value"]) ? $as["value"] : "";
					$as["c"] = isset($as["c"]) ? $as["c"] : "";
					//La classe export exporte ses donn�es dans la charset de la base.
					//Convertissons si besoin
					if ($charset!='utf-8' && $target_charset == 'utf-8'){
						if(function_exists("mb_convert_encoding")){
							$as["value"] = mb_convert_encoding($as["value"],"UTF-8","Windows-1252");
						}else{
							$as["value"] = utf8_encode($as["value"]);
						}
					}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
						if(function_exists("mb_convert_encoding")){
							$as["value"] = mb_convert_encoding($as["value"],"Windows-1252","UTF-8");
						}else{
							$as["value"] = utf8_decode($as["value"]);
						}
					}
				}

			}
			$this->results[$anotice_id] = $aresult;
		}
	}
	
	public function convert_batch_to_php_array_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		global $charset;
		if (!$notices_to_convert) //Rien � faire? On fait rien
			return;

		foreach($notices_to_convert as $anotice_id)  {
			$xmlexport_notice = $this->get_notice_unimarc_array($anotice_id);
			if (!$xmlexport_notice)
				continue;
			$aresult = array();
			$headers = array();
			if (isset($xmlexport_notice['rs']["value"]))
				$headers["rs"] = $xmlexport_notice['rs']["value"];
			if (isset($xmlexport_notice['dt']["value"]))
				$headers["dt"] = $xmlexport_notice['dt']["value"];
			if (isset($xmlexport_notice['bl']["value"]))
				$headers["bl"] = $xmlexport_notice['bl']["value"];
			if (isset($xmlexport_notice['hl']["value"]))
				$headers["hl"] = $xmlexport_notice['hl']["value"];
			if (isset($xmlexport_notice['el']["value"]))
				$headers["el"] = $xmlexport_notice['el']["value"];
			if (isset($xmlexport_notice['ru']["value"]))
				$headers["ru"] = $xmlexport_notice['ru']["value"];
			$aresult["id"] = $anotice_id;
			$aresult["header"] = $headers;
			$aresult["f"] = array();
			foreach ($xmlexport_notice['f'] as &$af) {
				if (!isset($af["c"]))
					continue;
				if (!isset($aresult["f"][$af["c"]]))
					$aresult["f"][$af["c"]] = array();
				$arf = array();
				$arf["ind"] = isset($af["ind"]) ? $af["ind"] : "";
				$arf["id"] = isset($af["id"]) ? $af["id"] : "";
				if (isset($af["s"])) {
					foreach ($af["s"] as &$as) {
						//La classe export exporte ses donn�es dans la charset de la base.
						//Convertissons si besoin
						$value = $as["value"];
						if ($charset!='utf-8' && $target_charset == 'utf-8'){
							if(function_exists("mb_convert_encoding")){
								$value = mb_convert_encoding($value,"UTF-8","Windows-1252");
							}else{
								$value = utf8_encode($value);
							}
						}else if ($charset=='utf-8' && $target_charset != 'utf-8'){
							if(function_exists("mb_convert_encoding")){
								$value = mb_convert_encoding($value,"Windows-1252","UTF-8");
							}else{
								$value = utf8_decode($value);
							}
						}
						if (isset($arf[$as["c"]]) && !is_array($arf[$as["c"]]))
							$arf[$as["c"]] = array($arf[$as["c"]]);
						if (isset($arf[$as["c"]]) && is_array($arf[$as["c"]]))
							$arf[$as["c"]][] = $value;
						else
							$arf[$as["c"]] = $value;
					}
				}
				else if (isset($af["value"])) {
					$arf["value"] = $af["value"];
				}

				$aresult["f"][$af["c"]][] = $arf;
			}
			$this->results[$anotice_id] = $aresult;
		}
	}
	
	public function convert_batch_to_serialized($notices_to_convert, $target_charset='iso-8859-1') {
			$this->convert_batch_to_php_array($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id) {
			$this->results[$anotice_id] = serialize($this->results[$anotice_id]);
		}
	}
	
	public function convert_batch_to_serialized_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id) {
			$this->results[$anotice_id] = serialize($this->results[$anotice_id]);
		}
	}	
	
	public function convert_batch_to_json($notices_to_convert, $target_charset='iso-8859-1') {
		$this->convert_batch_to_php_array($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id)
			$this->results[$anotice_id] = json_encode($this->results[$anotice_id]);
	}

	public function convert_batch_to_json_assoc($notices_to_convert, $target_charset='iso-8859-1') {
		$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
		foreach ($notices_to_convert as $anotice_id)
			$this->results[$anotice_id] = json_encode($this->results[$anotice_id]);
	}
	
	public function convert_uncachednotices($format, $format_ref, $target_charset='iso-8859-1') {
		$notices_to_convert=array();
		foreach ($this->results as $notice_id => $aresult) {
			if (!$aresult && $notice_id) {
				$notices_to_convert[] = $notice_id;
			}
		}
		
		//Conversion builtin
		switch ($format) {
			case "json_unimarc":
				$this->convert_batch_to_json($notices_to_convert, $target_charset);
				break;
			case "json_unimarc_assoc":
				$this->convert_batch_to_json_assoc($notices_to_convert, $target_charset);
				break;
			case "serialized_unimarc":
				$this->convert_batch_to_serialized($notices_to_convert, $target_charset);
				break;
			case "serialized_unimarc_assoc":
				$this->convert_batch_to_serialized_assoc($notices_to_convert, $target_charset);
				break;
			case "raw_array":
				$this->convert_batch_to_php_array($notices_to_convert, $target_charset);
				break;
			case "raw_array_assoc":
				$this->convert_batch_to_php_array_assoc($notices_to_convert, $target_charset);
				break;
			default:
				//Par d�faut on renvoie juste le notice_id
				foreach($notices_to_convert as $anotice_id) {
					$this->results[$anotice_id] = $anotice_id;
				}
				break;
		}

		//Cachons les notices converties maintenant.
		foreach ($notices_to_convert as $anotice_id) {
			if ($this->results[$anotice_id])
				$this->encache_value($anotice_id, $this->results[$anotice_id], $format_ref);
		}
	}
	
}


?>