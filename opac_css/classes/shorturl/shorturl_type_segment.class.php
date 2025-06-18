<?php
// +-------------------------------------------------+
// Â© 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: shorturl_type_segment.class.php,v 1.5.2.3 2021/11/08 10:14:43 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/shorturl/shorturl_type.class.php");

require_once($include_path."/search_queries/specials/combine/search.class.php");
require_once($include_path."/search_queries/specials/permalink/search.class.php");
require_once($include_path."/rec_history.inc.php");

class shorturl_type_segment extends shorturl_type{
	
	protected function rss()
	{
		global $opac_url_base,$dbh, $charset;
		global $opac_short_url_mode;
		global $opac_search_results_per_page;
		global $search,$op_0_s_10,$field_0_s_10;
		global $opac_short_url_rss_records_format;
		global $type_search;
		
		$this->notices_list=array();
		$context = unserialize($this->context);

		if ($type_search == TYPE_EXTERNAL){
    		$es = new search('search_fields_unimarc');
		} else {
    		$es = new search();
		}
		
		$es->unserialize_search(serialize($context['serialized_search']));
		$table = $es->make_search();
		$query = "SELECT * FROM $table";
		$res = pmb_mysql_query($query);

		if(pmb_mysql_num_rows($res)){
		    while ($row = pmb_mysql_fetch_object($res)){
		        $this->notices_list[]= $row->notice_id;
		    }
		}
		if($opac_short_url_mode){
			$flux = new records_flux(0);
			$rssRecordsFormat = substr($opac_short_url_rss_records_format,0,1);
			$flux->setRssRecordsFormat($rssRecordsFormat);
			if($rssRecordsFormat == 'H') {
			    $flux->setIdTpl(substr($opac_short_url_rss_records_format,2));
			}
			$flux->set_limit($opac_search_results_per_page);
			$params = explode(',',$opac_short_url_mode);
			if(is_array($params) && count($params) > 1){ //Une limite est définie
				$flux->set_limit($params[1]);
			}	
		}else{
			$flux = new newrecords_flux(0) ;
		}
		
		$flux->setRecords($this->notices_list);
		
		$flux->setLink($opac_url_base."s.php?h=$this->hash") ;
		
		$flux->setDescription(strip_tags(html_entity_decode($context['history_search']['human_query'])));
		$flux->xmlfile() ;

		if(!$flux->envoi )return;
		@header('Content-type: text/xml; charset='.$charset);
		print $flux->envoi;
	}
	
	protected function permalink()
	{
	    global $search;
	    
	    $context = unserialize($this->context);
        $id_segment = $context['id_segment'];

        $perso = null;
		if(isset($context['other_search_values'])){
			$perso = $context['other_search_values'];
			unset($context['other_search_values']);
		}	
		
	    $suite = "";
		if(!empty($context['opac_view'])){
			$suite = "&opac_view=".$context['opac_view'];
		}
		if (!empty($context["user_rmc"])) {
		    $suite .= "&user_rmc=".urlencode(stripslashes($context["user_rmc"]));
		}
		if (!empty($context["user_query"])) {
		    $suite .= "&user_query=".urlencode(stripslashes($context["user_query"]));
		}
// 		if (!empty($context["segment_json_search"])) {
// 		    $suite .= "&segment_json_search=".urlencode(stripslashes($context["segment_json_search"]));
// 		}
		
		$es = new search();
		$html = '
			<html><head></head><body><img src="'.get_url_icon('patience.gif').'"/>';
		$_SESSION["search_type"]='search_segment';
		$html.= $es->make_hidden_search_form('index.php?lvl=search_segment&action=segment_results&id='.$id_segment.$suite,"form_values","",true);
		if($perso !== null){
			$html.= $perso;
		}
		//Si autolevel2==0, la recherche n'est pas stockée en session
		//on ajoute un flag "from_permalink" pour forcer l'enregistrement en session de la recherche dans navigator.inc.php, afin de pouvoir appliquer des facettes
		$html.= '
				<input type=\'hidden\' name=\'from_permalink\' value=\'1\'>
			</form>
			<script type="text/javascript">
				document.forms["form_values"].submit();
			</script>
			<body></html>';
		print $html;
	}

	public function generate_hash($action,$context=array()) {
	    global $search_index,$id, $search, $opac_search_other_function, $universe_query;
		$hash = '';
		
		$context =array();
		$context["search_type"] = 'search_segment';
		$context['id_segment'] = $id;
		$context['user_query'] = (search_universe::$start_search["type"] == "simple" ? search_universe::$start_search["query"] : "");
		$context['user_rmc'] = (search_universe::$start_search["type"] == "extended" ? search_universe::$start_search["query"] : "");
		//$context['segment_json_search'] = (search_universe::$start_search["segment_json_search"] ? search_universe::$start_search["segment_json_search"] : "");
		//on essaye de conserver la vue!
		if(isset($_SESSION['opac_view']) && $_SESSION['opac_view']){
			$context['opac_view'] = $_SESSION['opac_view'];
		}
 			
		if(method_exists($this, $action)){
			$hash = self::create_hash('segment',$action,$context);
		}
		return $hash;
	}
}