<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_element.class.php,v 1.4 2020/12/22 16:55:42 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_cache.class.php");

/**
 * class vedette_element
 * un �l�ment d'une vedette composee : une instance d'une autorit� ou d'une notice
 */
abstract class vedette_element {

	/**
	 * Type de l'�l�ment
	 * @var unknown_type
	 */
	protected $type;
	
	/**
	 * Identifiant de l'�l�ment (URI dans le cas d'un concept)
	 * @var int or string
	 */
	protected $id;
	
	/**
	 * Libell� � afficher
	 * @var unknown_type
	 */
	protected $isbd;
	
	/**
	 * Param�tres suppl�mentaires
	 * @var array
	 */
	protected $params;
	
	/**
	 * Instance de la classe PMB de l'autorit� ou de la notice
	 * @access private
	 */
	protected $element;
	
	/**
	 * Identifiant de l'�l�ment dans la base
	 * @var int
	 */
	protected $db_id;
	
	
	/**
	 * Instance li�e � l'�l�ment
	 * @var unknown
	 */
	protected $entity;
	
	/**
	 * Num�ro du champ comme d�fini dans la grammaire
	 * @var int
	 */
	protected $num_available_field;
	
	/**
	 * Construit un element de la vedette
	 *
	 * @param int type Type d'�l�ment : auteur, titre uniforme, concept, notice, etc.
	 * @param int id Identifiant de l'�l�ment (id d'autorit� ou de notice)
	 * 
	 * @return void
	 * @access public
	 */
	public function __construct($num_available_field, $id, $isbd = "", $params = array()){
		$this->num_available_field = $num_available_field;
		$this->id = $id;
		$this->isbd = $isbd;
		$this->params = $params;
		$this->fetch_datas_cache();
	}
	
	/**
	 * Methode permettant de construire l'objet element dans la classe
	 * A r�-impl�menter pour faire un new concept ou une new notice ...
	 */
	public abstract function set_vedette_element_from_database();
	
	/**
	 * Retourne l'identifiant de l'�l�ment. Pas n�cessairement un entier (une URI pour un concept).
	 * @return unknown_type
	 */
	public function get_id(){
		return $this->id;
	}
	
	/**
	 * Retourne l'identifiant en base de l'�l�ment
	 * @return int
	 */
	public function get_db_id(){
		if (!$this->db_id) {
			$this->db_id = $this->id;
		}
		return $this->db_id;
	}
	
	public function get_type(){
		return $this->type;
	}
	
	public function get_isbd(){
		return $this->isbd;
	}
	
	public function get_element(){
		return $this->element;
	}
	
	public function get_num_available_field() {
		return $this->num_available_field;
	}
	
	public static function search_vedette_element_ui_class_name($vedette_element_class_name){
		if(class_exists($vedette_element_class_name.'_ui')){
			return $vedette_element_class_name.'_ui';
		}else{
			return 'vedette_element_ui';
		}
	}
	
	protected function fetch_datas_cache(){
		$tmp=vedette_cache::get_at_vedette_cache($this);
		if($tmp){
			$this->restore($tmp);
		}else{
			$this->set_vedette_element_from_database();
			vedette_cache::set_at_vedette_cache($this);
		}
	}
	
	protected function restore($vedette_object){
		foreach(get_object_vars($vedette_object) as $propertieName=>$propertieValue){
			$this->{$propertieName}=$propertieValue;
		}
	}
	
	protected function get_generic_link(){
	    global $use_opac_url_base, $opac_url_base;
	    
	    if($use_opac_url_base) {
	        return $opac_url_base."index.php?lvl=!!type!!_see&id=!!id!!";
	    } else {
	        return "./autorites.php?categ=see&sub=!!type!!&id=!!id!!";
	    }
	}
	
	public function get_lien_opac() {
		global $liens_opac;
		global $use_opac_url_base, $opac_url_base;
		
		if($use_opac_url_base) return $opac_url_base.str_replace('./', '', $liens_opac[$this->key_lien_opac]);
		return $liens_opac[$this->key_lien_opac];
	}
	
	public function get_entity(){
		return $this->entity;
	}
	
	public function get_params() {
	    return $this->params;
	}
}
