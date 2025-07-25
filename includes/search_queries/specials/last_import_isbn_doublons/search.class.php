<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.class.php,v 1.2.14.1 2023/08/31 12:56:46 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

//Classe de gestion de la recherche sp�cial "doublons isbn depuis import"

class last_import_isbn_doublons_search {
	public $id;
	public $n_ligne;
	public $params;
	public $search;

	//Constructeur
    public function __construct($id,$n_ligne,$params,&$search) {
    	$this->id=$id;
    	$this->n_ligne=$n_ligne;
    	$this->params=$params;
    	$this->search=&$search;
    }
    
    //fonction de r�cup�ration des op�rateurs disponibles pour ce champ sp�cial (renvoie un tableau d'op�rateurs)
    public function get_op() {
    	$operators = array();
    	$operators["EQ"]="=";
    	return $operators;
    }
    
    //fonction de r�cup�ration de l'affichage de la saisie du crit�re
    public function get_input_box() {
    	global $msg;
    	
    	if ((!isset($_SESSION['last_import_isbn_doublons'])) || (!trim($_SESSION['last_import_isbn_doublons'])) || ($_SESSION['last_import_isbn_doublons']=='""')) {
    		return $msg['last_import_isbn_doublons_msg_search_no_import'];
    	} else {
    		return $msg['last_import_isbn_doublons_msg_search'].formatdate($_SESSION["last_import_isbn_doublons_datetime"],1);
    	}
    }
    
    //fonction de cr�ation de la requ�te (retourne une table temporaire)
    public function make_search() {
        global $default_tmp_storage_engine;

    	$table_tempo = 'last_import_isbn_doublons_'.md5(microtime(true));
    	$requete="create temporary table ".$table_tempo." ENGINE={$default_tmp_storage_engine} SELECT notice_id FROM notices WHERE code IN (".json_decode($_SESSION['last_import_isbn_doublons']).")";	
		pmb_mysql_query($requete);
    	    	    	
    	return $table_tempo;
    }
    
    //fonction de traduction litt�rale de la requ�te effectu�e (renvoie un tableau des termes saisis)
    public function make_human_query() {
    	global $msg;

    	$litteral=array();
    	$litteral[]=formatdate($_SESSION["last_import_isbn_doublons_datetime"],1);
    			
		return $litteral;    
    }
    
    public function is_empty($valeur) {
    	if ((!isset($_SESSION['last_import_isbn_doublons'])) || (!trim($_SESSION['last_import_isbn_doublons'])) || ($_SESSION['last_import_isbn_doublons']=='""')) {
    		return true;
    	} else {
    		return false;
    	}
    }

}
?>