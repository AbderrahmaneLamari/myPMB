<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_segment_search_result.class.php,v 1.67.2.5 2023/10/02 13:43:57 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $base_path,$include_path,$class_path,$msg;
require_once($class_path."/search_universes/search_segment_facets.class.php");
require_once($class_path."/search_universes/search_universes_history.class.php");
require_once($class_path."/searcher/searcher_factory.class.php");
require_once($class_path."/more_results.class.php");
require_once($include_path.'/search_queries/specials/combine/search.class.php');
require_once($include_path.'/search_queries/specials/combine_extended_search/search.class.php');
require_once($class_path.'/cms/cms_editorial_searcher.class.php');
require_once($class_path.'/elements_list/elements_cms_editorial_articles_list_ui.class.php');
require_once($class_path.'/elements_list/elements_cms_editorial_sections_list_ui.class.php');
require_once($class_path.'/elements_list/elements_concepts_list_ui.class.php');
require_once($class_path.'/elements_list/elements_external_records_list_ui.class.php');
require_once($class_path.'/elements_list/elements_animations_list_ui.class.php');
require_once $class_path.'/entities.class.php';
require_once $class_path."/search_universes/search_segment_searcher_authorities.class.php";
require_once $class_path."/searcher/searcher_animations_extended.class.php";
require_once($include_path.'/search_queries/specials/dynamic_value/search.class.php');

class search_segment_search_result {
    
    /**
     * 
     * @var search_segment
     */
    protected $segment;
    
    protected $searcher;
    
    public const IS_SUB_RMC = true;
    public const IS_NOT_SUB_RMC = false;
    
    private $first_search_history = true;
	
    public function __construct($segment) {
        $this->segment = $segment;
    }
	
	public function get_display_facets() {
		global $es, $base_path;
		
		$facettes_tpl = '';
		$tab_result = $this->init_session_facets();
		$segment_facets = search_segment_facets::get_instance('', $this->segment->get_id());
// 		$segment_facets->set_num_segment($this->segment->get_id());
		$segment_facets->set_segment_search($es->json_encode_search());
		$content = $es->make_segment_search_form($base_path.'/index.php?lvl=search_segment&id='.$this->segment->get_id().'&action=segment_results'.search_universe::get_segments_dynamic_params(), 'form_values', "", true);
	    $facettes_tpl .= $segment_facets->call_facets($content);
		
		return $facettes_tpl;
	}
	
	public function get_searcher() {
	    global $user_query;

	    if (!isset($this->searcher)) {
	        switch (true) {
	            case $this->segment->get_type() == TYPE_NOTICE :
	                $this->searcher = searcher_factory::get_searcher('records', 'extended');
	                break;
	            case $this->segment->get_type() == TYPE_CMS_EDITORIAL :
	                $this->searcher = searcher_factory::get_searcher('cms', 'extended');
	                break;
	            case $this->segment->get_type() == TYPE_EXTERNAL :
	                $this->searcher = new searcher_external_extended();
	                break;
	            case $this->segment->get_type() == TYPE_ANIMATION :
	                $this->searcher = searcher_factory::get_searcher('animations', 'extended');
	                break;
	            case intval($this->segment->get_type()) > 10000 :
	                $class_id = $this->segment->get_type() - 10000;
	                $this->searcher = new search_segment_searcher_ontologies($class_id);
	                break;
	            default :
	                $this->searcher = new search_segment_searcher_authorities();
	                $this->searcher->init_authority_param(entities::get_aut_table_from_type($this->segment->get_type()));
	                break;
	        }
	    }
	    return $this->searcher;
	}

	public function get_nb_results($ajax_mode = false, $is_sub_rmc = self::IS_NOT_SUB_RMC) {
	    global $search_type;

	    $search_type="search_universes";

	    // a reprendre plus tard, on reinitialise le searcher pour jouer plusieurs recherches de suite. Merci les singletons !
	    $this->searcher = null;

	    $this->prepare_segment_search($is_sub_rmc);
	    //search_segment_facets::make_facette_search_env();
	    if (!$is_sub_rmc) {
            $this->checked_facette_search();
    	    rec_history();
	    }

	    if ($ajax_mode) {
	        // Afin de paralléliser les recherches AJAX, on ferme la session PHP
    	    session_write_close();
	    }
	    $this->get_searcher();
	    return $this->searcher->get_nb_results();
	}
	
	public function get_searcher_table() {
	    if (!isset($this->searcher)) {
	        $this->get_nb_results();
	    }
	    if (!empty($this->searcher->table)) {
	        return $this->searcher->table;
	    }
	    return "";
	}
	
	protected function checked_facette_search() {
	    if ($this->segment->get_type() == TYPE_EXTERNAL) {
	        search_segment_external_facets::checked_facette_search();
	        return;
	    }
	    search_segment_facets::checked_facette_search();
	}
	
	protected function prepare_segment_search($is_sub_rmc){
	    global $user_query;
	    global $user_rmc;
	    global $refine_user_rmc;
	    global $refine_user_query;
	    global $search;
	    global $deleted_search_nb;
	    global $es;
	    global $new_search;

	    if(!is_object($es)){
	    	if($this->get_type_from_segment() == TYPE_NOTICE){
            	$es = search::get_instance('search_fields_gestion');
	    	}elseif(($this->get_type_from_segment() == TYPE_CMS_EDITORIAL)){
	    	    $es = search::get_instance('search_fields_cms_editorial');
	    	} elseif($this->get_type_from_segment() == TYPE_EXTERNAL) {
	    	    $es = search::get_instance('search_fields_unimarc_gestion');
	    	} elseif($this->get_type_from_segment() == TYPE_ANIMATION) {
	    	    $es = search::get_instance('search_fields_animations');
	    	} elseif(intval($this->get_type_from_segment()) > 10000){
	    	    $class_id = $this->get_type_from_segment()-10000;
	    	    $ontology = new ontology(ontologies::get_ontology_id_from_class_uri(onto_common_uri::get_uri($class_id)));
	    	    $es=new search_ontology("search_fields_ontology_gestion",$ontology->get_handler()->get_ontology());  
	    	} else {
	    	    $es = search::get_instance('search_fields_authorities_gestion');
	    	}
	    }
	    
	    if (!is_array($search) || (!empty($new_search) && !$is_sub_rmc)) {
	    	$search = array();
	    }
	    
	    // On enleve les champs vides de la recherche
	    $es->reduct_search();
	    
	    //search_universes_history::update_json_search_with_history();
	    if (!empty(search_universe::$start_search["segment_json_search"]) && empty($new_search) && !$is_sub_rmc) {
	        $es->json_decode_search(stripslashes(search_universe::$start_search["segment_json_search"]));
	    }
	    //partage de recherche
	    if (!empty(search_universe::$start_search["shared_serialized_search"]) && empty($new_search) && !$is_sub_rmc) {
	        $es->unserialize_search(search_universe::$start_search["shared_serialized_search"]);
	    }
	    //on le reinitialise pour l'affinage, 
	    //cela evite de boucler a l'infini a cause du singleton de cette classe
	    if (search_universe::$start_search["launch_search"]) {
	        if (!in_array('s_10', $search)) {
                if ($this->segment->use_dynamic_field()) {
	                $this->explode_search();
	            } else {
	                $this->add_special_search_index(10, $this->segment->get_id());
	            }
    	    }
    	    //if (!empty($user_rmc) && empty($no_segment_search)) {
    	    if (search_universe::$start_search["query"]) {
        	    if (search_universe::$start_search["type"] == "extended") {
        	        $this->add_special_search_index(11, stripslashes(search_universe::$start_search["query"]));
        	    }
        	    if (search_universe::$start_search["type"] == "simple") {
        	        $user_query_mc = combine_search::simple_search_to_mc(stripslashes(search_universe::$start_search["query"]), true, $this->get_type_from_segment());
        	        $es->json_decode_search($user_query_mc);
        	    }
    	    }
	    }
	    //affinage
	    if (!empty($refine_user_rmc)) {
	        $this->add_special_search_index(11, stripslashes($refine_user_rmc));
	    } elseif (!empty($refine_user_query)) {
	        $user_query_mc = combine_search::simple_search_to_mc(stripslashes($refine_user_query), true, $this->get_type_from_segment());
	    	$es->json_decode_search($user_query_mc);
	    }
	    
	    if (isset($deleted_search_nb)) {
	    	$es->delete_search($deleted_search_nb);
	    }
	    $this->init_global_universe_id();
	}
	
	private function explode_search() {
	    global $es, $search;
	    $es->json_decode_search($this->segment->get_set()->get_data_set(), true);
	    
	    if (in_array("s_12", $search)) {
	        for ($i=0 ; $i < count($search) ; $i++) {
	            if ($search[$i] == "s_12") {
    	            $dynamic_value = new dynamic_value(12, $i, [], $es);
    	            $explode_search = $dynamic_value->get_serialize_search();
    	            $this->add_special_search_index(11, stripslashes($explode_search), "and", $i);
    	            unset($dynamic_value);
	            }
	        }
	    }
	}
	
	protected function add_special_search_index(int $search_id, $value, $inter = "and", $index = null) {
	    global $search;
	    
	    $new_index = count($search);
	    if (isset($index)) {
	        $new_index = $index;
	    }
	    $search[$new_index] = 's_'.$search_id;
	    
	    global ${'inter_'.$new_index.'_s_'.$search_id};
	    global ${'op_'.$new_index.'_s_'.$search_id};
	    global ${'field_'.$new_index.'_s_'.$search_id};
	    
	    ${'inter_'.$new_index.'_s_'.$search_id} = $inter;
	    ${'op_'.$new_index.'_s_'.$search_id} = 'EQ';
	    ${'field_'.$new_index.'_s_'.$search_id} = [$value];
	}
	
	public function get_display_results($display_navbar = true, $display_sort_selector = true) {
	    global $base_path;
	    global $debut,$opac_search_results_per_page;
	    global $count, $page, $es;
	    global $facettes_tpl;
	    global $charset;
	    global $msg;
	    global $opac_short_url;	 
	    global $add_cart_link_spe;
	    global $opac_visionneuse_allow,$link_to_visionneuse,$sendToVisionneuseSegmentSearch;
	    global $opac_show_suggest,$link_to_print_search_result_spe,$opac_resa_popup;
	    
	    $count = $this->get_nb_results();
	    $html = '<div id="search_universe_segment_result_list">';	    
	    //il faudrait revoir ce systï¿½me de globales
	    if($count > 0){
	        //Impression des resultats
	        if($this->get_type_from_segment() == TYPE_NOTICE || $this->get_type_from_segment() == TYPE_EXTERNAL){
	            $link_to_print_search_result_spe =  str_replace('!!spe!!', '&mode='.$this->get_type_from_segment(), $link_to_print_search_result_spe);
	            $html .= "<span class='print_search_result'>".$link_to_print_search_result_spe."</span>";
	        }
	        if ($display_sort_selector){
    	        //Selecteur de tri
    	        $search_segment_sort = $this->segment->get_sort();
    	        if(!empty($search_segment_sort->get_sort()) && !strpos($search_segment_sort->get_sort() ,"segment_sort_name_default")){
    	            $affich_tris_result_liste = $search_segment_sort->show_tris_selector_segment();
    	            $html.=  $affich_tris_result_liste;
    	        }
	        }
	        //Ajout au Panier
	        if($add_cart_link_spe && $this->get_type_from_segment() == TYPE_NOTICE || $this->get_type_from_segment() == TYPE_EXTERNAL){
	            $add_cart_link_spe =  str_replace('!!spe!!', '&mode='.$this->get_type_from_segment(), $add_cart_link_spe);
	            $html .= $add_cart_link_spe;
	            
	        }
	        
	        //Visionneuse        
	        if($opac_visionneuse_allow && $this->get_type_from_segment() == TYPE_NOTICE){   
	            $nbexplnum_to_photo = $this->get_searcher()->get_nb_explnums();
	        }
	        if($opac_visionneuse_allow && $this->get_type_from_segment() == TYPE_NOTICE && $nbexplnum_to_photo){
	            $html .= "<span class=\"espaceResultSearch\">&nbsp;&nbsp;&nbsp;</span>".$link_to_visionneuse;
	            $html .= $sendToVisionneuseSegmentSearch;
	        }
	        
	        // url courte       
	        if($opac_short_url) {
	            //On enregistre en session les resultats de la recherche
	            $_SESSION['search_segment_result'][$this->segment->get_id()] = $this->searcher->get_result();
	            
	            search_universe::$start_search["shared_serialized_search"] = $es->serialize_search();
	            $shorturl_search = new shorturl_type_segment();
	            //On propose le partage de flux RSS uniquement dans le cas de notices
                if ($this->get_type_from_segment() == TYPE_NOTICE || $this->get_type_from_segment() == TYPE_EXTERNAL){
                    $html .= $shorturl_search->get_display_shorturl_in_result("rss",$this->get_type_from_segment());
	            }
	            $html .= $shorturl_search->get_display_shorturl_in_result("permalink");
	        }
	        //Suggestion de resultats
	        if ($opac_show_suggest && $this->get_type_from_segment() == TYPE_NOTICE) {
	            $bt_sugg = "&nbsp;&nbsp;&nbsp;<span class='search_bt_sugg' ><a href=# ";
	            if ($opac_resa_popup) $bt_sugg .= " onClick=\"w=window.open('./do_resa.php?lvl=make_sugg&oresa=popup','doresa','scrollbars=yes,width=600,height=600,menubar=0,resizable=yes'); w.focus(); return false;\"";
	            else $bt_sugg .= "onClick=\"document.location='./do_resa.php?lvl=make_sugg&oresa=popup' \" ";
	            $bt_sugg.= " title='".$msg["empr_bt_make_sugg"]."' >".$msg['empr_bt_make_sugg']."</a></span>";
    	        $html .=$bt_sugg;
	        }
	        
	        // pour la DSI - création d'une alerte
	        global $opac_allow_bannette_priv;
	        if ($this->get_type_from_segment() == TYPE_NOTICE && 
	            $opac_allow_bannette_priv && 
	            (
	                (isset($_SESSION['abon_cree_bannette_priv']) && $_SESSION['abon_cree_bannette_priv'] == 1) || 
	                $opac_allow_bannette_priv == 2)
	            ) 
	        {
    	        $html .= "<input type='button' class='bouton' name='dsi_priv' value=\"$msg[dsi_bt_bannette_priv]\"
                    onClick=\"document.form_values.action='./empr.php?lvl=bannette_creer'; document.form_values.submit();\">
                    <span class=\"espaceResultSearch\">&nbsp;</span>";
	        }
	        
	        $html.= "<h4 class='segment_search_results'>".$count." ".htmlentities($msg['results'], ENT_QUOTES, $charset)."</h4>";
	        if(!$page) {
	            $debut = 0;
	        } else {
	            $debut = ($page-1)*$opac_search_results_per_page;
	        }

            $sorted_results = $this->get_sorted_result();

	        if(is_string($sorted_results)){
	        	$sorted_results = explode(',', $sorted_results);
	        }

	        if (count($sorted_results)) {
	            $_SESSION['tab_result_current_page'] = implode(",", $sorted_results);
	        } else {
	            $_SESSION['tab_result_current_page'] = "";
	        }
	        //TODO cartographie ?
	        //print searcher::get_current_search_map(0);
	    }else{
	        $html.= "<h4 class='segment_search_results'>".htmlentities($msg['no_result'], ENT_QUOTES, $charset)."</h4>";
	    }
	    switch($this->get_type_from_segment()){
	        case TYPE_NOTICE :
	            $html .= '<div id="search_universe_segment_result_list_content">'.aff_notice(-1);
	            $recherche_ajax_mode=0;
	            if (!empty($sorted_results)) {
	                for ($i =0 ; $i<count($sorted_results);$i++) {
	                    if($i>4) {
	                        $recherche_ajax_mode=1;
	                    }
	                    $html.= pmb_bidi(aff_notice($sorted_results[$i], 0, 1, 0, "", "", 0, 0, $recherche_ajax_mode));
	                }
	            }
	            $html.= aff_notice(-2);
	            break;
	        case TYPE_CMS_EDITORIAL :
	            $cms_list_ui = new elements_cms_editorial_list_ui($sorted_results, $count, true);
	            $cms_list_ui->set_link($this->segment->get_search_segment_data());
	            $html .= $cms_list_ui->get_elements_list();
    	        break;
	        case TYPE_EXTERNAL :
                if(!empty($sorted_results)){
	                $elements_list_ui = new elements_external_records_list_ui($sorted_results, $count, true);
	                $html .= $elements_list_ui->get_elements_list();
                }
                break;
	        case TYPE_ANIMATION :
                if(!empty($sorted_results)){
                    $elements_list_ui = new elements_animations_list_ui($sorted_results, $count, true);
                    $html .= $elements_list_ui->get_elements_list();
                }
                break;
	        default :
                if(!empty($sorted_results)){
                    if(intval($this->segment->get_type()) > 10000){
                        $elements_list_ui = new elements_onto_list_ui($sorted_results, $count,false);
                        $class_id = $this->segment->get_type() - 10000; 
                        $ontology = new ontology(ontologies::get_ontology_id_from_class_uri(onto_common_uri::get_uri($class_id)));
                        $elements_list_ui->set_ontology($ontology->get_handler()->get_ontology());
                    }else{
	                   $elements_list_ui = new elements_authorities_list_ui($sorted_results, $count, true);
                    }
	                $html .= $elements_list_ui->get_elements_list();
	            }
	            break;
	    }
	  
	    $html.= facette_search_compare::form_write_facette_compare();
	    if($display_navbar){
	        $html.= more_results::get_navbar();
	        $facettes_tpl = $this->get_display_facets();
	    }
	    $html.= "</div>";
	    return $html;
	}

	protected function init_session_facets() {
	    global $reinit_facette;
	    global $es;
	    global $search_type;
        
	    $tab_result = $this->get_searcher()->get_result();
	    $_SESSION['segment_result'][$this->segment->get_id()] = $this->searcher->get_result();
	    return $tab_result;
	}
	
	protected function get_type_from_segment(){
		return $this->segment->get_type();
	}
	
	protected function init_global_universe_id() {
	    global $universe_id;
	    global $search_index;
	    
	    //si on ne provient pas d'un univers, n'y d'un historique
	    if (empty($universe_id) && empty($search_index)) {
	        $universe_id = $this->segment->get_num_universe(); 
	    }
	}
	
	public function get_sorted_result($nb_result = 0) {
	    global $debut, $opac_search_results_per_page;
	    if (empty($nb_result)) {
	        $nb_result = $opac_search_results_per_page;
	    }
	    $debut = $debut ?? 0;
	    switch (true) {
	        case (!empty($this->segment->get_sort()->get_sort())) :
	            $object_ids = explode(",",$this->searcher->notices_ids ?? $this->searcher->objects_ids);
	            return $this->segment->get_sort()->sort_data($object_ids, $debut, $nb_result, $this->searcher->get_raw_query());
	        case (get_class($this->searcher) == 'searcher_extended') :
	        case (get_class($this->searcher) == 'searcher_external_extended') :
	        case (get_class($this->searcher) == 'search_segment_searcher_authorities') :
	            return $this->searcher->get_sorted_result("default",$debut,$nb_result);
	        default :
	            return explode(",",$this->searcher->notices_ids ?? $this->searcher->objects_ids);
	    }
	}
}