<?php
// +-------------------------------------------------+
// ï¿½ 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_universes_controller.class.php,v 1.21.2.5 2021/12/07 07:40:41 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/search_universes/search_universe.class.php');
require_once($class_path.'/search_universes/search_segment.class.php');
require_once($class_path.'/search_universes/search_segment_search_result.class.php');

class search_universes_controller {
    
	protected $object_id;
    
    public function __construct($id=0) {
        $this->object_id = intval($id);
        $this->init_start_search();
    }

    public function proceed() {
        global $lvl;
        
        switch($lvl) {
        	case 'search_universe':
        	    $this->proceed_universe();
        		break;
        	case 'search_segment':
        	    $this->proceed_segment();
        		break;
        	default:
        		break;
        }
    }
    
    public function proceed_universe() {
        global $action;
        
        switch($action) {
            case 'search':
                $search_universe = new search_universe($this->object_id);
                $search_universe->get_result_from_segments();
                break;
            case 'simple_search':
                $search_universe = new search_universe($this->object_id);
                print $search_universe->get_form();
                break;
        	default:
        		$search_universe = new search_universe($this->object_id);
        		print $search_universe->get_form();
        		break;
        }
    }
    
    public function proceed_segment() {
        global $action;
        global $mode;
        global $search_type, $user_query;
        global $active_facette, $tab, $active_facettes_external;
        global $facettes_tpl;
        global $facettes_lvl1;
        
        $search_segment = search_segment::get_instance($this->object_id);
        $search_universe = new search_universe($search_segment->get_num_universe());
        $display_results = $search_segment->get_display_results();
        print $search_segment->get_display_search();
        print $search_universe->get_segments_list($search_segment->get_id());
        print $search_segment->get_universe_associate_list();
        print $display_results;
    }
    
    public function proceed_ajax(){
        global $sub;
        switch($sub) {
        	case 'search_universe':
        	    $this->proceed_universe_ajax();
        		break;
        	case 'search_segment':
        	    $this->proceed_segment_ajax();
        		break;
        	default:
        		break;
        }
    }
    
    public function proceed_universe_ajax(){
        global $action;
        global $segment_id;
        
        $search_universe = new search_universe($this->object_id);
        switch($action){
            case 'simple_search':
                $search_segment = search_segment::get_instance($segment_id);
                $nb_results = 0;
                if (is_object($search_segment)) {
                    $nb_results = $search_segment->get_nb_results(true);
                }
                print encoding_normalize::json_encode(array(
                    'segment_id' => $segment_id,
                    'nb_result' => $nb_results, 
                    'order' => $search_segment->get_order(),
                    'label' => $search_segment->get_label() 
                ));
                break;
            case 'extended_search':
                $search_segment = search_segment::get_instance($segment_id);
                $nb_results = 0;
                if (is_object($search_segment)) {
                    $nb_results = $search_segment->get_nb_results(true);
                }
                print encoding_normalize::json_encode(array(
                    'segment_id' => $segment_id,
                    'nb_result' => $nb_results, 
                    'order' => $search_segment->get_order(),
                    'label' => $search_segment->get_label() 
                ));
                break;
            case 'rec_history' :
                print encoding_normalize::json_encode(array(
                    'search_index' => $search_universe->rec_history()
                ));
                break;
        	default:
        		break;
        } 
    }
    
    public function proceed_segment_ajax(){
        global $action;
        $search_segment = search_segment::get_instance($this->object_id);
        
        switch($action){
            case 'get_nb_result':
                $nb_results = $search_segment->get_nb_results();
                print encoding_normalize::json_encode(array(
                    'segment_id' => $this->object_id,
                    'nb_result' => $nb_results,
                ));                
                break;
        } 
    }
    
    private function init_start_search() {
        global $user_rmc;
        global $user_query;
        
        /**
        $cas = [
            $search_index => "provient de l'univers ou de l'historique",
            $segment_json_search => "affinage ou pagination",
            $user_rmc => "recherche multi criteres",
            $user_query => "recherche simple",
        ];
        **/
        //on fait appel à l'historique
        if (empty($user_rmc) && empty($user_query)) {
            $start_search = search_universes_history::get_start_search();
            search_universe::$start_search = [
                "type" => $start_search["type"],
                "query" => $start_search["query"],
                "launch_search" => $start_search["launch_search"],
                "segment_json_search" => $start_search["segment_json_search"],
                "search_index" => $start_search["search_index"],
            ];
        } else {
            search_universe::$start_search = [
                "type" => (!empty($user_rmc) ? "extended" : "simple"),
                "query" => (!empty($user_rmc) ? $user_rmc : $user_query),
                "launch_search" => true,
                "segment_json_search" => "",
                "search_index" => "",
            ];
        }
        return;
    }
}
