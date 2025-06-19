<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_universe.class.php,v 1.42.4.5 2023/12/04 12:50:39 pmallambic Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/translation.class.php');
require_once($include_path.'/templates/search_universes/search_universe.tpl.php');
require_once($class_path.'/search_view.class.php');
require_once($class_path."/more_results.class.php");
require_once ($class_path."/search_universes/search_universes_search_view.class.php");
//require_once($class_path."/search_universes/search_universes_history.class.php");

class search_universe {
	
	protected $id;
	
	protected $label;
	
	protected $description;
	
	protected $template_directory;
	
	protected $opac_views;
	
	protected $segments;
	
	protected $default_segment;
	
	protected $rmc_enabled;
	
	protected $universe_settings;
	
	protected static $universes_labels;
	
	public static $current_universe_id = 0;
	
	public static $segments_dynamic_params = [];
	
	/**
	 * pour faire transiter la recherche initiale et eviter toutes les globales
	 * @var array
	 */
	public static $start_search = [];

	public function __construct($id = 0){
		$this->id = intval($id);
		static::$current_universe_id = $this->id;
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		$this->label = '';
		$this->description = '';
		$this->template_directory = '';
		$this->opac_views = array();
		$this->default_segment = 0;
		if ($this->id) {
			$query = "
			    SELECT search_universes.*, id_search_segment FROM search_universes
                LEFT JOIN search_segments
                ON id_search_segment = search_universe_default_segment
				WHERE id_search_universe = '".$this->id."'
			";
			
			$result = pmb_mysql_query($query);
			if ($result) {
				$row = pmb_mysql_fetch_assoc($result);
				$this->label = $row["search_universe_label"];
				$this->description = $row["search_universe_description"];
				$this->template_directory = $row["search_universe_template_directory"];
				$this->opac_views = ( $row["search_universe_opac_views"] ? explode(',', $row["search_universe_opac_views"]) : array());
				if (isset($row["id_search_segment"])) {
    				$this->default_segment = $row["search_universe_default_segment"];
				}
				$this->rmc_enabled = $row["search_universe_rmc_enabled"];
				$this->universe_settings = encoding_normalize::json_decode($row["search_universe_settings"]);
			}
		}
	}
	
	public function get_label() {
		return $this->label;
	}
	
	public function get_translated_label() {
		return translation::get_translated_text($this->id, 'search_universes', 'universe_label',  $this->label);
	}
	
	public function get_description() {
		return $this->description;
	}
	
	public function get_translated_description() {
		return translation::get_translated_text($this->id, 'search_universes', 'universe_description',  $this->description);
	}
		
	public function get_template_directory() {
		return $this->template_directory;
	}
		
	public function get_opac_views() {
		return $this->opac_views;
	}
	
    public function get_form() {
		global $msg;
		global $charset;
		global $search_universe_form;
		global $search_universe_segment_list;
		global $search_universe_type;
		
		$default_segment = $this->get_default_segment();
		$segment_list = $this->get_segments();
		if (count($segment_list) == 1) {
            $default_segment = $segment_list[0]->get_id();
		}
		
		$html = $search_universe_form;
		$html = str_replace('!!search_universe_tabs!!', $this->get_display_search_view(), $html);
		$html = str_replace('!!universe_label!!', htmlentities($this->get_translated_label(), ENT_QUOTES, $charset), $html);
		$html = str_replace('!!universe_description!!', htmlentities($this->get_translated_description(), ENT_QUOTES, $charset), $html);
		if("perio_a2z" == $search_universe_type && $this->has_perio_enabled()){
		    $html = str_replace('!!universe_segment_list!!', "", $html);
		} else {
		    $html = str_replace('!!universe_segment_list!!', $search_universe_segment_list, $html);
		}
		
		$last_query = "";
		if (static::$start_search["launch_search"]) {
		    $last_query = htmlentities(stripslashes(static::$start_search["query"]), ENT_QUOTES, $charset);
		}
		$html = str_replace('!!last_query!!', $last_query, $html);
		$html = str_replace('!!user_rmc!!', (static::$start_search["type"] == "extended" ? htmlentities(stripslashes(static::$start_search["query"]), ENT_QUOTES, $charset) : ""), $html);
// 		$html = str_replace('!!last_query!!', static::$start_search["query"], $html);
		$html = str_replace('!!default_segment!!', $default_segment, $html);
		$html = str_replace('!!universe_segments_form!!', $this->get_segments_form(), $html);
		$html = str_replace('!!search_index!!', $this->get_search_universes_history(), $html);
		$html = str_replace('!!universe_id!!', $this->get_id(), $html);
		
		$query = "";
		if(static::$start_search["type"] != "extended"){
	        $query = htmlentities(stripslashes($this->get_universe_query()), ENT_QUOTES, $charset);
		}
		$html = str_replace('!!user_query!!',$query, $html);
		
		return $html;
	}
	
	private function get_display_search_view() {
	    global $search_universe_type;
	    global $base_path;
	    global $user_query;
	    
	    if (empty($search_universe_type)) {
	        $search_universe_type = "simple_search";
	    }
	    $default_segment = $this->get_default_segment();
	    $segment_list = $this->get_segments();
	    if (count($segment_list) == 1) {
	        $default_segment = $segment_list[0]->get_id();
	    }
	    
	    $url = $base_path."/ajax.php?module=ajax&categ=search_universes&sub=search_universe&action=$search_universe_type&id=" . $this->id;
	    $url_default_segment = "";
	    if ($default_segment != 0) {
	        $url_default_segment = $base_path."/index.php?lvl=search_segment&action=segment_results&id=" . $default_segment;
	    }
	    $url .= static::get_segments_dynamic_params();
	    $url_default_segment .= static::get_segments_dynamic_params();
	    
	    search_universes_search_view::set_object_id($this->id);
	    search_universes_search_view::set_universe($this);
	    search_universes_search_view::set_search_type($search_universe_type);
	    search_universes_search_view::set_user_query($user_query);
	    search_universes_search_view::set_url_base($url);
	    search_universes_search_view::set_url_default_segment($url_default_segment);
	    return search_universes_search_view::get_display_search();
	}
	
	public function get_segments_list($segment_id = 0){
		global $search_universe_segment_list;
		global $msg;
		
		$this->get_segments();	
		
		$segment_list = "<h4 class='new_search_segment_title'><span class='fa fa-search'></span> ". $msg["search_segment_new_search"] ." \"". stripslashes($this->get_universe_query())."\"</h4>";

		if (count($this->segments) <= 1) {
			return $segment_list;
		}
		$segment_list .= $search_universe_segment_list;
		$segment_list = str_replace('!!universe_segments_form!!', $this->get_segments_form($segment_id), $segment_list);
		$segment_list = str_replace('!!universe_id!!', $this->get_id(), $segment_list);
		return $segment_list;
	}
	
	public function set_properties_from_form(){
		global $universe_label;	
		global $universe_description;	
		global $universe_template_directory;
		global $universe_opac_views;
		
		$this->label = stripslashes($universe_label);
		$this->description = stripslashes($universe_description);
		$this->template_directory = stripslashes($universe_template_directory);
		$this->opac_views = array();
		
		if (isset($universe_opac_views)) {
    		if (!in_array('', $universe_opac_views)) {
    		    $this->opac_views = $universe_opac_views;
    		}
		}		
	}
	
	public function save() {
		if($this->id){
			$query = 'update ';
			$query_clause = ' where id_search_universe = '.$this->id;
		}else{
			$query = 'insert into ';
			$query_clause = '';
		}
		$query .= ' search_universes set
			search_universe_label = "'.addslashes($this->label).'",
			search_universe_description = "'.addslashes($this->description).'",
			search_universe_template_directory = "'.addslashes($this->template_directory).'",
			search_universe_opac_views = "'.implode(',', $this->opac_views).'"';
		pmb_mysql_query($query.$query_clause);
		if(!$this->id){
			$this->id = pmb_mysql_insert_id();			
		}
		$translation = new translation($this->id, "search_universes");
		$translation->update("universe_label");
		$translation->update("universe_description");
	}
	
	public static function delete($id) {
		$id += 0;
		$query = "delete from search_universes where id_search_universe = ".$id;
		pmb_mysql_query($query);
	}
	
	public function get_segments() {
		if (!isset($this->segments)) {
			$this->segments = array();
			$query = "SELECT id_search_segment FROM search_segments
						WHERE search_segment_num_universe = '".$this->id."'
						ORDER BY search_segment_order";
			$result = pmb_mysql_query($query);
			if (pmb_mysql_num_rows($result)) {
				while($row = pmb_mysql_fetch_assoc($result)) {
					$segment = search_segment::get_instance($row['id_search_segment']);
					static::$segments_dynamic_params = array_merge(static::$segments_dynamic_params, $segment->get_set()->get_dynamic_params());
					$this->segments[] = $segment;
				}
			}
			static::$segments_dynamic_params = array_unique(static::$segments_dynamic_params);
		}
		return $this->segments;		
	}
	
	public function get_opac_views_form() {
		global $opac_opac_view_activate;
		global $search_universe_opac_views;
		
		$form = '';
		if($opac_opac_view_activate) {
			$form = $search_universe_opac_views;
			$opac_views = new opac_views();
			$form = str_replace("!!opac_views_selector!!", opac_views::get_selector('universe_opac_views', $this->opac_views), $form);
		}
		return $form;
	}
	
	public function get_segments_form($segment_id = 0) {
	    global $search_universe_dialog_universe_associated;
		global $search_universe_segments_form_row;
		global $search_universe_segment_logo;
		global $charset;
		
		$segments_form = "";
		$segments = $this->get_segments();		
		
		if (is_array($segments) && count($segments)) {
			foreach ($segments as $segment) {
			    $segment_form = str_replace("!!segment_label!!", htmlentities(stripslashes($segment->get_translated_label()), ENT_QUOTES, $charset), $search_universe_segments_form_row); 
			    $segment_form = str_replace("!!segment_description!!", htmlentities($segment->get_translated_description(), ENT_QUOTES, $charset), $segment_form);
				if($segment->get_logo()){
					$segment_form = str_replace("!!segment_logo!!", $search_universe_segment_logo, $segment_form);
					$segment_form = str_replace("!!segment_logo!!", $segment->get_logo(), $segment_form);
				}
				$segment_form = str_replace("!!segment_logo!!", '', $segment_form);
				$segment_form = str_replace("!!segment_id!!", $segment->get_id(), $segment_form);
				
				$segment_url = "./index.php?lvl=search_segment&action=segment_results&id=" . $segment->get_id();
				$segment_url .= search_universe::get_segments_dynamic_params();
				
				$segment_form = str_replace("!!segment_dynamic_field!!", $segment->use_dynamic_field(), $segment_form);
				$segment_form = str_replace("!!segment_url!!", $segment_url, $segment_form);
				$segement_selected = "";
				$button_dialog_universe_associated = "";
				if ($segment_id == $segment->get_id()) {
				    $segement_selected = 'selected_segment';
				    if (!empty($segment->get_search_universes_associate())) {		
    				    $button_dialog_universe_associated = $search_universe_dialog_universe_associated;
				    }
				}
				$segment_form = str_replace("!!segment_selected!!", $segement_selected, $segment_form);
				$segment_form = str_replace("!!button_dialog_universe_associated!!", $button_dialog_universe_associated, $segment_form);
				$segments_form .= $segment_form;
			}
		}
		return $segments_form;
	}
	
	public function get_id() {
	    return $this->id;
	}
	
	public function get_display_segments() {
        $this->get_segments();
        return $this->segments;
	}

	public function get_result_from_segments(){
	    $result_tab = array();
	    $this->get_segments();
	    foreach($this->segments as $segment){
	        $set = $segment->get_set();
	        if ($set->get_data_set()) {
	            $result_tab[] = $set->make_search(); 	            
	        }
	        //$segment->get_preview_results();
	    }
	    $query = "SELECT * FROM ". implode(', ', $result_tab);
	    $result = pmb_mysql_query($query);
	    
	    $row = pmb_mysql_fetch_all($result);
	}
	
	public function rec_history() {
        global $search_type;
        global $search_index;
        $search_type = 'search_universes';
        
        rec_history();
        
        return $search_index;
	}
	
	public function get_search_universes_history() {
	    global $universe_history;
	    if (!empty($universe_history)) {
	        return $universe_history;	            
	    }
	    return '';
	}
	
	public static function get_label_from_id($universe_id) {
	    $universe_id = intval($universe_id);
	    if ($universe_id) {
    	    if (isset(static::$universes_labels[$universe_id])) {
    	        return static::$universes_labels[$universe_id];
    	    }
    	    if (!isset(static::$universes_labels)) {
    	        static::$universes_labels = array();
    	    }
    	    $query = "
			    SELECT search_universe_label FROM search_universes
				WHERE id_search_universe = '".$universe_id."'
			";
    	    $result = pmb_mysql_query($query);
    	    if ($result) {
    	        $row = pmb_mysql_fetch_assoc($result);
    	        static::$universes_labels[$universe_id] = translation::get_translated_text($universe_id, 'search_universes', 'universe_label',  $row["search_universe_label"]);
    	        return static::$universes_labels[$universe_id];
    	    }
	    }
	    return '';
	}
	
	public function get_default_segment() {
	    return $this->default_segment;
	}
	
	
	public function get_universe_query() {
	    switch (true) {
	        case (!empty(self::$start_search["shared_query"])):
	            return self::$start_search["shared_query"];
	        case (self::$start_search["type"] == "extended"):
                return self::$start_search["human_query"] ?? self::$start_search["query"];
	        default :
	            return self::$start_search["query"];
	    }
	}
	
	/*
	 * pour recuperer les parametes GET
	 */
	public static function get_parameters() {
	    $get_parameters = "";
	    if (!empty($_GET)) {
	        foreach ($_GET as $key => $value) {
	            if (!in_array($key, ["lvl", "id", "action", "module", "categ", "sub", "new_search", "user_rmc", "user_query", "segment_json_search"])) {
	                $get_parameters .= "&$key=".rawurlencode($value);
	            }
	        }
	    }
	    return $get_parameters;
	}
	
	/*
	 * pour recuperer les parametes GET
	 */
	public static function get_segments_dynamic_params() {
	    $segments_params = "";
	    foreach (static::$segments_dynamic_params as $key => $value) {
            global ${$key};
            if (isset(${$key})) {
                $segments_params .= "&$key=${$key}";
            }
	    }
	    return $segments_params;
	}
	
	/**
	 * Retourne tous les univers
	 * @return search_universe[]
	 */
	public static function get_universe_list()
	{
	    $universes = array();
	    
	    $query = "SELECT DISTINCT id_search_universe FROM search_universes JOIN search_segments ON search_segment_num_universe = id_search_universe";
	    $result = pmb_mysql_query($query);
	    
	    if(pmb_mysql_num_rows($result)) {
	        while ($row = pmb_mysql_fetch_assoc($result)) {
	            $universes[] = new search_universe($row['id_search_universe']);
	        }
	    }
	    return $universes;
	}
	
	public function get_hidden_form() {
	    global $msg;
	    global $charset;
	    global $search_form_hidden;
	    
	    $default_segment = $this->get_default_segment();
	    $segment_list = $this->get_segments();
	    if (count($segment_list) == 1) {
	        $default_segment = $segment_list[0]->get_id();
	    }
	    //search_universe_type=extended_search
	    $search_type = "simple_search";
	    if (static::$start_search["type"] == "extended") {
	        $search_type = "extended_search";
	    }
	    $url = "/index.php?lvl=search_universe&search_universe_type=$search_type&id=".$this->id;
	    if ($default_segment != 0) {
	        $url = "/index.php?lvl=search_segment&action=segment_results&id=" . $default_segment;
	    }
	    $url .= static::get_parameters();
	    $html = $search_form_hidden;
	    $html = str_replace('!!url!!', $url, $html);
	    $html = str_replace('!!user_query!!', (static::$start_search["type"] == "simple" ? htmlentities(stripslashes(static::$start_search["query"]), ENT_QUOTES, $charset) : ""), $html);
	    $html = str_replace('!!user_rmc!!', (static::$start_search["type"] == "extended" ? htmlentities(stripslashes(static::$start_search["query"]), ENT_QUOTES, $charset) : ""), $html);
	    $html = str_replace('!!universe_id!!', $this->get_id(), $html);
	    
	    return $html;
	}
	
	/**
	 * rmc disponible
	 * @return boolean
	 */
	public function has_rmc_enabled() {
	    if (!empty($this->rmc_enabled)) {
	        $search = search::get_instance("search_universes_fields");
	        foreach ($search->universesfields as $field){
	            if (count($field["SEGMENTS"])) {
                    if (self::is_segments_in_current_universe($field["SEGMENTS"])) {
                        return true;
                    }
	            }
	        }
	    }
	    return false;
	}
	
	
	public static function is_segments_in_current_universe(array $segments_tab) {
	    $ids = array_keys($segments_tab);
	    if(!empty($ids)) {
		    $query = "SELECT id_search_segment FROM search_segments WHERE id_search_segment IN (".implode(",", $ids).") AND search_segment_num_universe = ".static::$current_universe_id;
		    $result = pmb_mysql_query($query);
		    if (pmb_mysql_num_rows($result)) {
		        return true;
		    }
	    }
	    return false;
	}
	
	public function has_perio_enabled() {
	    if(!empty($this->universe_settings) && !empty($this->universe_settings->perio_enabled)){
	        return true;
	    }
	    return false;
	}
	
	public function is_autocomplete() {
	    if(!empty($this->universe_settings) && !empty($this->universe_settings->autocomplete)){
	        return true;
	    }
	    return false;
	    
	}
}