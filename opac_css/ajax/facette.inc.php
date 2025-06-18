<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: facette.inc.php,v 1.20 2021/02/16 08:41:57 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $sub, $action, $charset;
global $opac_facettes_ajax, $facet_type, $num_segment, $segment_search, $sended_datas;
global $pmb_compare_notice_nb, $pmb_compare_notice_template;

require_once($class_path.'/facette_search.class.php');
require_once($class_path.'/facette_search_compare.class.php');
require_once($class_path.'/encoding_normalize.class.php');
require_once($class_path.'/search_universes/search_segment_facets.class.php');

switch($sub){
	case 'get_data':
		session_write_close();
		if($opac_facettes_ajax){
		    if (!empty($facet_type)) {
		        facettes::set_facet_type($facet_type);
		    }
			$opac_parse_html=0; // pb du parse html et des couleurs des clusters. #ea8787. Solution temporaire...
			if(!empty($num_segment)) {
			    $segment_facets = search_segment_facets::get_instance($_SESSION['segment_result'][$num_segment], $num_segment);
				if (!empty($segment_search)) {
				    $segment_facets->set_segment_search(stripslashes($segment_search));
				}
				ajax_http_send_response(encoding_normalize::json_encode($segment_facets->get_ajax_facette()),'application/json');
			} else {
				ajax_http_send_response(encoding_normalize::json_encode(facettes::make_ajax_facette($_SESSION['tab_result'])),'application/json');
			}
		}
		break;
	case 'see_more':
	    if($charset != "utf-8") $sended_datas=utf8_encode($sended_datas);
		$sended_datas=pmb_utf8_array_decode(json_decode(stripslashes($sended_datas),true));
		switch ($action) {
		    case 'segment_results':
		    	ajax_http_send_response(search_segment_facets::see_more($sended_datas['json_facette_plus']),'application/json');
		    break;
		    default:
		    	ajax_http_send_response(facettes::see_more($sended_datas['json_facette_plus']),'application/json');
		    break;
		}
		break;
	case 'compare_see_more':
		if($charset != "utf-8") $sended_datas=utf8_encode($sended_datas);
		$sended_datas=pmb_utf8_array_decode(json_decode(stripslashes($sended_datas),true));
		$sended_datas['json_notices_ids']=implode(',',$sended_datas['json_notices_ids']);
		
		$tab_return=array();
		$tab_return['notices'] = encoding_normalize::utf8_normalize(facette_search_compare::call_notice_display($sended_datas['json_notices_ids'], $pmb_compare_notice_nb, $pmb_compare_notice_template));
		if($sended_datas['json_notices_ids']){
			$tab_return['see_more'] = encoding_normalize::utf8_normalize(facette_search_compare::get_compare_see_more($sended_datas['json_notices_ids']));
		}
		ajax_http_send_response(json_encode($tab_return),'application/json');
		break;
}
