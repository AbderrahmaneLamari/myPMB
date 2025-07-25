<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_store_arc2.class.php,v 1.28.4.1 2023/10/27 08:59:56 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;

require_once "$class_path/onto/onto_store.class.php";

/**
 * class onto_store_arc2
 * 
 */
class onto_store_arc2 extends onto_store {
	
	public $store;

	/**
	 * @param array() config
	
	 * @return void
	 * @access public
	 */
	public function __construct($config) {
		parent::__construct($config);
	} // end of member function __construct
	
	/**
	 * Se connecter au store
	 *
	 * @return bool
	 * @access public
	 */
	public function connect() {
	
		$this->store = ARC2::getStore($this->config);
		
		if (!$this->store->getDBCon()) {
			//On regarde si l'on peut se connecter avec les informations fournies
			$this->errors[]=$this->store->getErrors();
			return false;
		}else{
		    pmb_mysql_query("SET wait_timeout=300", $this->store->getDBCon());
			if(!$this->store->isSetUp()) {
				//Si les tables du store n'existent pas
				//On cr�e les tables
				$this->store->setUp();
				if($erreurs=$this->store->getErrors()){
					//Si la cr�ation � �chou�e
					foreach ($erreurs as $value) {
						$this->errors[]=$value;
					}
					
					$this->close();
					return false;
				}else{
					//Si on viens de faire la cr�ation pour pouvoir faire autre chose on doit se d�connecter et se reconnecter
					$this->close();
					$this->store = ARC2::getStore($this->config);
					$this->store->getDBCon();
				}
			}
			return true;
		}
	} // end of member function connect

	/**
	 * D�connexion du store
	 *
	 * @return bool
	 * @access public
	 */
	public function close() {
		$this->store->closeDBCon();
	} // end of member function close
	
	
	/**
	 * Charge un fichier RDF dans le store
	 *
	 * @param string onto_filepath Chemin du fichier RDF � charger dans le store
	
	 * @return bool
	 * @access public
	 */
	public function load($onto_filepath){
		global $dbh,$thesaurus_ontology_filemtime;

		//evolution pour la possibilit� d'avoir plusieurs fichier rdf
		if (!is_numeric($thesaurus_ontology_filemtime)) {
			$tab_file_rdf =  unserialize($thesaurus_ontology_filemtime);
			if (!isset($tab_file_rdf[$this->store->getName()])) {
				$tab_file_rdf[$this->store->getName()] = 0;
			}
		} else {
			$tab_file_rdf[$this->store->getName()] = $thesaurus_ontology_filemtime;
		}	
				
		//on charge l'ontologie seulement si la date de modification du fichier est > � la date de derni�re lecture
		if(filemtime($onto_filepath) != $tab_file_rdf[$this->store->getName()]){
			// le load ne fait qu'ajouter les nouveaux triplets sans supprimer les anciens, donc on purge avant...
			$this->store->reset();
			
			//LOAD n'accepte qu'un chemin absolu
			$res=$this->query('LOAD <file:///'.realpath($onto_filepath).'>');
			
			$tab_file_rdf[$this->store->getName()] = filemtime($onto_filepath);			
			
			if($res){
				$query='UPDATE parametres SET valeur_param="'.addslashes(serialize($tab_file_rdf)).'" WHERE type_param="thesaurus" AND sstype_param="ontology_filemtime"';
				pmb_mysql_query($query,$dbh);
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	} // end of member function load
	
	/**
	 * Ex�cute une requ�te SPARQL dans le store
	 * Rempli le result de l'instance, sous forme de tableau de class std
	 *
	 * @param string query Requ�te sparql � ex�cuter dans le store
	
	 * @return bool
	 * @access public
	 */
	public function query($query,$prefix=array()){
        $query_insert = false;
        if (preg_match("/^\s*(insert\sinto|delete)/", strtolower($query))) {
            $query_insert = true;
        }
	
        $query = $this->format_namespaces($prefix).encoding_normalize::utf8_normalize($query);
		$result = [];
		$tabResult = [];
		$this->result = [];

		if(!count($this->errors)){
			//Si je n'ai pas d�j� des erreurs
		    if (!pmb_mysql_ping($this->store->a['db_con'])) {
		        $this->store->createDBCon();
		    }
		    //J'execute la requete
			$result = $this->store->query($query);
			if ($query_insert) {
			    $this->close();
			    $this->store->getDBCon();
			}
			if($erreurs=$this->store->getErrors()){
				//Si l'execution de la requete a �chou�
				foreach($erreurs as $value){
					$this->errors[]=$value;
				}
				return false;
			}elseif(!$result){
				//et que je n'ai pas de r�sultat
				$this->errors[]='Query '.$query.' failed.';
				return false;
			}else{
				//on transforme le r�sultat et on l'ins�re dans la variable $this->result
				if (!empty($result["result"]["rows"])) {
					foreach($result["result"]["rows"] as $keyLine=>$valueLine) {
						//on construit l'objet
						$stdClass=new stdClass();
						foreach($valueLine as $property=>$value){
							$stdClass->{str_replace(" ","_",trim($property))}=$this->charset_normalize(trim($value));
						}
						//et on ins�re l'objet dans le tablea de result
						$tabResult[$keyLine]=$stdClass;
					}
				}
				
				//on ins�re le tableau d'objet dans la variable $this->result
				$this->result=$tabResult;
				return true;
			}
		}

		return false;
	} // end of member function query

	
	public function import($filepath){
		$res=$this->query('LOAD <file:///'.realpath($filepath).'>');
		if($res){
			return true;
		}else{
			var_dump($this->get_errors());
		}
	}
	
	public function reset(){
		$this->store->reset();
	}
	
	public function get_RDF($print = false){
		$config = array(
			'ns' => $this->namespaces
		);
		$ser = ARC2::getRDFXMLSerializer($config);
		$result = $this->store->query($this->format_namespaces()."SELECT ?s ?p ?o WHERE { ?s ?p ?o }");
 		$rdf = $ser->getSerializedTriples($result["result"]["rows"]);
 		if($print){
 			header("Content-type: ".$ser->content_header);
 			print $rdf;
 		}else{
 			return $rdf;
 		}
	}
	
	public function drop(){
		$this->store->drop();
	}
	
	public function reset_after_save()
	{
	    $this->close();
	    $this->store = ARC2::getStore($this->config);
	    $this->store->getDBCon();
	}
} // end of onto_store_arc2