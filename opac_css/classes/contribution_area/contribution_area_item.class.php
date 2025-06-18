<?php
// +-------------------------------------------------+
// © 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: contribution_area_item.class.php,v 1.3 2020/11/02 14:36:13 tsamson Exp $
if (stristr($_SERVER ['REQUEST_URI'], ".class.php"))
	die("no access");

require_once($class_path.'/contribution_area/contribution_area_store.class.php');

/**
 * class contribution_area_item
 * Represente un item de contribution
 */
class contribution_area_item {
    
    /**
     * 
     * @var string
     */
    private $uri = "";
    
    /**
     * 
     * @var string
     */
    private $linked_data = [];
    
    /**
     * 
     * @var contribution_area_store
     */
    private $store = null;
    
	public function __construct($uri) {
	    $this->uri = $uri;
	    $this->store = new contribution_area_store();
	    $this->fetch_data();
	}
	
	private function fetch_data() {	    
	    if (!empty($this->uri)) {
	        $query = "SELECT * WHERE {
                    <$this->uri> ?p ?o
                }";
	        $this->store->get_datastore()->query($query);
	        if ($this->store->get_datastore()->num_rows()) {
	            $results = $this->store->get_datastore()->get_result();
	            foreach ($results as $result){
	                if ($result->o_type !== "uri") {
    	                $prop = str_replace("http://www.pmbservices.fr/ontology#", "", $result->p);
    	                $this->{$prop} = $result->o;
	                }
	            }
	        }
	    }
	}
	
	private function get_linked_data() {
	    if (!empty($this->linked_data)) {
	        return $this->linked_data;
	    }
	    if (!empty($this->uri)) {
	        $query = "SELECT * WHERE {
                    <$this->uri> ?p ?o
                }";
	        $this->store->get_datastore()->query($query);
	        if ($this->store->get_datastore()->num_rows()) {
	            $results = $this->store->get_datastore()->get_result();
	            foreach ($results as $result){
	                if ($result->o_type === "uri") {
	                    $prop = str_replace("http://www.pmbservices.fr/ontology#", "", $result->p);
	                    if (!is_array($this->linked_data[$prop])) {
	                        $this->linked_data[$prop] = [];
	                    }
	                    $this->linked_data[$prop][] = new contribution_area_item($result->o);
	                }
	            }
	        }
	    }
	    return $this->linked_data;
	    
	}

	public function __get($name)
	{
	    if (method_exists($this, "get_".$name)) {
	        return $this->{"get_".$name}();
	    }
	    if (isset($this->{$name})) {
	        return $this->{$name};
	    }
	    return null;
	}
	
	public function __set($name, $value)
	{
	    $this->{$name} = $value;
	}
}
