<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.class.php,v 1.2 2022/08/17 13:19:54 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/search_universes/search_segment_set.class.php');

//Classe de gestion de la recherche sp�cial "facette"

class search_universe_segment_search {
	public $id;
	public $n_ligne;
	public $params;
	public $search;
	public $champ_base;
	
	protected $segment_set;
	
	//Constructeur
    public function __construct($id,$n_ligne,$params,&$search) {
    	$this->id=$id;
    	$this->n_ligne=$n_ligne;
    	$this->params=$params;
    	$this->search=&$search;
    }
    
	public function get_op() {
    	$operators = array();
		$operators["EQ"] = "=";
    	return $operators;
    }
    
    public function make_search(){
    	$this->get_segment_set();
    	
    	//enregistrement de l'environnement courant
    	$this->search->push();
    	
    	$table_tempo = $this->segment_set->make_search("tempo_".$this->n_ligne);
    	
    	//restauration de l'environnement courant
    	$this->search->pull();
    	
    	return $table_tempo;
    }
    
    public function make_human_query(){
    	$litteral = array();
    	
    	$this->get_segment_set();
    	
    	//enregistrement de l'environnement courant
    	$this->search->push();
    	
    	$litteral[0] = $this->segment_set->get_human_query();

    	//restauration de l'environnement courant
    	$this->search->pull();
    	
    	return $litteral;
    }
    
    public function make_unimarc_query(){
    	//R�cup�ration de la valeur de saisie
    	$valeur_="field_".$this->n_ligne."_s_".$this->id;
    	global ${$valeur_};
    	$valeur=${$valeur_};
    	return "";
    }
    
    public function get_input_box() {
    	global $charset;

    	$field_name="field_".$this->n_ligne."_s_".$this->id;
    	global ${$field_name};
    	$valeur = ${$field_name};
    	
    	$this->get_segment_set();
    	
		//enregistrement de l'environnement courant
		$this->search->push();
		
    	//on g�n�re une human_query
    	$r = $this->segment_set->get_human_query();
    	$r.="<span><input type='hidden' name='field_".$this->n_ligne."_s_".$this->id."[]' value='$valeur[0]'/></span>";
    	
    	//restauration de l'environnement courant
    	$this->search->pull();
    	
    	return $r;
    }
    
    //fonction de v�rification du champ saisi ou s�lectionn�
    public function is_empty($valeur) {
    	if (count($valeur)) {
    		if ($valeur[0]=="") return true;
    		else return ($valeur[0] === false);
    	} else {
    		return true;
    	}
    }
    
    public function get_segment_set() {
    	if (isset($this->segment_set)) {
    		return $this->segment_set;
    	}
    	$value = "field_".$this->n_ligne."_s_".$this->id;
    	global ${$value};
    	
    	$segment_id = ${$value};
    	if (is_array(${$value})) {
    	    $segment_id = ${$value}[0];
    	}
    	
    	$this->segment_set = new search_segment_set($segment_id);

    	return $this->segment_set;
    }
    
}
?>