<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_store.class.php,v 1.16 2020/04/01 10:13:19 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


/**
 * class onto_store
 * 
 */
abstract class onto_store {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/

	/**
	 * "Objet" configuration du store
	 * @access private
	 */
	protected $config;

	/**
	 * Tableau de r�sultat de la derni�re requ�te
	 * @access private
	 */
	protected $result;

	/**
	 * Noms d'espaces � ins�rer dans toutes les requ�tes SPARQL
	 * @access private
	 */
	protected $namespaces = array();

	/**
	 * Tableau des erreurs
	 */
	protected $errors = array();
	
	/**
	 * 
	 *
	 * @param array() config 

	 * @return void
	 * @access public
	 */
	public function __construct($config){
		$this->config=$config;
		$this->connect();
	} // end of member function __construct

	/**
	 * Se connecter au store
	 *
	 * @return bool
	 * @access public
	 */
	public abstract function connect(); // end of member function connect

	/**
	 * D�connexion du store
	 *
	 * @return bool
	 * @access public
	 */
	public abstract function close(); // end of member function close

	/**
	 * Charge un fichier RDF dans le store
	 *
	 * @param string onto_filepath Chemin du fichier RDF � charger dans le store
	
	 * @return bool
	 * @access public
	 */
	public abstract function load($onto_filepath); // end of member function load
	
	/**
	 * Ex�cute une requ�te SPARQL dans le store
	 * Rempli le result de l'instance, sous forme de tableau de class std
	 *
	 * @param string query Requ�te sparql � ex�cuter dans le store

	 * @return bool
	 * @access public
	 */
	public abstract function query($query,$prefix=""); // end of member function query

	/**
	 * Renvoie le tableau de d�clarations r�sultat de la derni�re requ�te.
	 *
	 * @return array
	 * @access public
	 */
	public function get_result(){
		return $this->result;
	} // end of member function get_result

	/**
	 * Renvoie le nombre de r�sultat de la derni�re requ�te.
	 *
	 * @return int
	 * @access public
	 */
	public function num_rows() {
	    if (empty($this->result)) {
	        $this->result = array();
	    }
		return count($this->result);
	}

	/**
	 * Renvoie le tableau des erreures du store
	 *
	 * @return array
	 * @access public
	 */
	public function get_errors(){
		return $this->errors;
	}
	
	/**
	 * Ajoute les namespaces
	 *
	 * @return void
	 * @access public
	 */
	public function set_namespaces($namespaces){
		$this->namespaces=$namespaces;
	}
	
	/**
	 * Converti les namespaces en chaine pour la requete 
	 *
	 * @param array()  
	 * @return string
	 * @access public
	 */
	public function format_namespaces($namespaces=array()){
		$prefix="";
		
		$namespaces=array_unique(array_merge($this->namespaces,$namespaces));
			
		foreach($namespaces as $key=>$uri){
			$prefix.="PREFIX ".$key.": <".$uri.">\n";
		}
		return $prefix;
	}	
	
	public function charset_normalize($string){
		global $charset;
		if($charset != "utf-8"){
			$string = utf8_decode($string);
		}
		return $string;
	}

	public function utf8_normalize($string){
		global $charset;
		if($charset != "utf-8"){
			$string = utf8_encode($string);
		}
		return $string;
	}
} // end of onto_store