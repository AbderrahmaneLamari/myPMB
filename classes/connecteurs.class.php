<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: connecteurs.class.php,v 1.75.4.3 2023/12/14 08:43:24 dbellamy Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path."/parser.inc.php");
require_once($include_path."/templates/connecteurs.tpl.php");
require_once($class_path."/upload_folder.class.php");
require_once "$class_path/event/events/event_connector.class.php";
require_once("$class_path/interface/admin/interface_admin_connecteurs_form.class.php");

class connector {

	public $repository;				//Est-ce un entrepot ?
	public $timeout;					//Time-out
	public $retry;						//Nombre de r�essais
	public $ttl;						//Time to live
	public $parameters;				//Param�tres propres au connecteur
	public $sources;					//Sources disponibles
	public $msg;						//Messages propres au connecteur
	public $connector_path;
	protected static $sources_params = array();

	//Calcul ISBD
	protected static $xml_indexation;
	protected static $isbd_ask_list = array();
	protected static $ufields = array();

	//Variables internes pour la progression de la r�cup�ration des notices
	public $callback_progress;		//Nom de la fonction de callback progression pass�e par l'appellant
	public $source_id;				//Num�ro de la source en cours de synchro
	public $del_old;				//Supression ou non des notices dej� existantes

	//R�sultat de la synchro
	public $error;					//Y-a-t-il eu une erreur
	public $error_message;			//Si oui, message correspondant

	public function __construct($connector_path="") {
		$this->fetch_global_properties();
		$this->get_messages($connector_path);
		$this->connector_path=$connector_path;
	}

	//Signature de la classe
	public function get_id() {
		return "";
	}

	//Est-ce un entrepot ?
	public function is_repository() {
		return 0;
	}

	public function get_libelle($message) {
		if (substr($message,0,4)=="msg:") return $this->msg[substr($message,4)]; else return $message;
	}


    /**
     * Retourne une liste des champs specifiques interrogeables en recherche externe
     * (tableau vide => tous les champs peuvent etre interroges)
     *
     * Les donnees retournees sont les id et valeurs des attributs unimarField decrits dans
     * - includes/search_queries/search_fields_unimarc.xml
     * - includes/search_queries/search_simple_fields_unimarc.xml
     *
     * @return string []
     */
    public static function getSpecificUnimarcSearchFields()
    {
        return [];
    }

	protected function unserialize_source_params($source_id) {
		$params=$this->get_source_params($source_id);
		if ($params["PARAMETERS"]) {
			$vars=unserialize($params["PARAMETERS"]);
			$params["PARAMETERS"]=$vars;
		}
		return $params;
	}



	public function get_messages($connector_path) {
		global $lang;

		$file_name = '';
		if (file_exists($connector_path."/messages/".$lang.".xml")) {
			$file_name=$connector_path."/messages/".$lang.".xml";
		} else if (file_exists($connector_path."/messages/fr_FR.xml")) {
			$file_name=$connector_path."/messages/fr_FR.xml";
		}
		if ($file_name) {
			$xmllist=new XMLlist($file_name);
			$xmllist->analyser();
			$this->msg=$xmllist->table;
		}
	}

	//Recuperation de la liste des sources d'un connecteur
	public function get_sources() {
		if(!isset($this->sources) || !count($this->sources)) {
			$sources=array();
			$requete="SELECT connectors_sources.*, source_sync.cancel, source_sync.percent, source_sync.date_sync FROM connectors_sources LEFT JOIN source_sync ON ( connectors_sources.source_id = source_sync.source_id ) where id_connector='".addslashes($this->get_id())."' order by connectors_sources.name";
			$resultat=pmb_mysql_query($requete);
			if (pmb_mysql_num_rows($resultat)) {
				while ($r=pmb_mysql_fetch_object($resultat)) {
					$s=array();
					$s["SOURCE_ID"]=$r->source_id;
					$s["PARAMETERS"]=$r->parameters;
					$s["NAME"]=$r->name;
					$s["COMMENT"]=$r->comment;
					$s["RETRY"]=$r->retry;
					$s["REPOSITORY"]=$r->repository;
					$s["TTL"]=$r->ttl;
					$s["TIMEOUT"]=$r->timeout;
					$s["OPAC_ALLOWED"]=$r->opac_allowed;
					$s["UPLOAD_DOC_NUM"]=$r->upload_doc_num;
					$s["REP_UPLOAD"] = $r->rep_upload;
					$s["ENRICHMENT"] = $r->enrichment;
					$s["OPAC_AFFILIATE_SEARCH"] = $r->opac_affiliate_search;
					$s["OPAC_SELECTED"] = $r->opac_selected;
					$s["GESTION_SELECTED"] = $r->gestion_selected;
					$s["TYPE_ENRICHEMENT_ALLOWED"]=unserialize($r->type_enrichment_allowed);
					$s["CANCELLED"]=$r->cancel;
					$s["PERCENT"]=$r->percent;
					$s["DATESYNC"]=$r->date_sync;
					$s["LASTSYNCDATE"]=$r->last_sync_date;
					$s["ICO_NOTICE"]=$r->ico_notice;
					$sources[$r->source_id]=$s;
				}
			}
			$this->sources=$sources;
		}
		return $this->sources;
	}

	//R�cup�ration des param�tres d'une source
	public function get_source_params($source_id) {
		if(isset(self::$sources_params[$source_id])) {
			return self::$sources_params[$source_id];
		}

		$s = [];
		if ($source_id) {
			$requete="select * from connectors_sources where id_connector='".addslashes($this->get_id())."' and source_id=".$source_id."";
			$resultat=pmb_mysql_query($requete);
			if (pmb_mysql_num_rows($resultat)) {
				$r=pmb_mysql_fetch_object($resultat);
				$s["SOURCE_ID"]=$r->source_id;
				$s["PARAMETERS"]=$r->parameters;
				$s["NAME"]=$r->name;
				$s["COMMENT"]=$r->comment;
				$s["RETRY"]=$r->retry;
				$s["REPOSITORY"]=$r->repository;
				$s["TTL"]=$r->ttl;
				$s["TIMEOUT"]=$r->timeout;
				$s["OPAC_ALLOWED"]=$r->opac_allowed;
				$s["UPLOAD_DOC_NUM"]=$r->upload_doc_num;
				$s["REP_UPLOAD"] = $r->rep_upload;
				$s["ENRICHMENT"] = $r->enrichment;
				$s["OPAC_AFFILIATE_SEARCH"] = $r->opac_affiliate_search;
				$s["OPAC_SELECTED"]=$r->opac_selected;
				$s["GESTION_SELECTED"] = $r->gestion_selected;
				if($r->type_enrichment_allowed == ""){
					$s["TYPE_ENRICHMENT_ALLOWED"] = array();
				}else{
					$s["TYPE_ENRICHMENT_ALLOWED"]=unserialize($r->type_enrichment_allowed);
				}
				$s["ICO_NOTICE"]=$r->ico_notice;
			}
		} else {
			$s["SOURCE_ID"]="";
			$s["PARAMETERS"]="";
			$s["NAME"]="Nouvelle source";
			$s["COMMENT"]="";
			$s["RETRY"]=$this->retry;
			$s["REPOSITORY"]=$this->repository;
			$s["TTL"]=$this->ttl;
			$s["TIMEOUT"]=$this->timeout;
			$s["OPAC_ALLOWED"]=0;
			$s["UPLOAD_DOC_NUM"]=1;
			$s["REP_UPLOAD"] = 0;
			$s["ENRICHMENT"] = 0;
			$s["OPAC_AFFILIATE_SEARCH"] = 0;
			$s["OPAC_SELECTED"]=0;
			$s["GESTION_SELECTED"] = 0;
			$s["ICO_NOTICE"]="";
			$s["TYPE_ENRICHMENT_ALLOWED"]=array();
		}
		//Gestion du timeout au niveau de mysql pour ne pas perdre la connection
		if($s["TIMEOUT"]){
			$res=pmb_mysql_query("SHOW SESSION VARIABLES like 'wait_timeout'");
			$timeout_default=0;
			if($res && pmb_mysql_num_rows($res)){
				$timeout_default=pmb_mysql_result($res,0,1);
			}
			pmb_mysql_query("SET SESSION wait_timeout=".($timeout_default+(($s["TIMEOUT"])*1)));
		}
		self::$sources_params[$source_id] = $s;
		return self::$sources_params[$source_id];
	}

	//Formulaire des propri�t�s d'une source
	public function source_get_property_form($source_id) {
		return "";
	}

	public function make_serialized_source_properties($source_id) {
		$this->sources[$source_id]["PARAMETERS"]="";
	}

	//Formulaire de sauvegarde des propri�t�s d'une source
	public function source_save_property_form($source_id) {
		global $source_categories;
		$this->make_serialized_source_properties($source_id);
		$this->sources[$source_id]["OPAC_ALLOWED"] = $this->sources[$source_id]["OPAC_ALLOWED"] ? 1 : 0;
		$this->sources[$source_id]["UPLOAD_DOC_NUM"] = $this->sources[$source_id]["UPLOAD_DOC_NUM"] ? 1 : 0;
		$this->sources[$source_id]["ENRICHMENT"] = $this->sources[$source_id]["ENRICHMENT"] ? 1 : 0;
		$this->sources[$source_id]["OPAC_AFFILIATE_SEARCH"] = $this->sources[$source_id]["OPAC_AFFILIATE_SEARCH"] ? 1 : 0;
		$this->sources[$source_id]["OPAC_SELECTED"] = $this->sources[$source_id]["OPAC_SELECTED"] ? 1 : 0;
		$this->sources[$source_id]["GESTION_SELECTED"] = $this->sources[$source_id]["GESTION_SELECTED"] ? 1 : 0;
		if(!is_array($this->sources[$source_id]["TYPE_ENRICHMENT_ALLOWED"])){
			$this->sources[$source_id]["TYPE_ENRICHMENT_ALLOWED"]=array();
		}
		$this->sources[$source_id]["TYPE_ENRICHMENT_ALLOWED"] = serialize($this->sources[$source_id]["TYPE_ENRICHMENT_ALLOWED"]);
		if(is_array($this->sources[$source_id]["PARAMETERS"])){
			$this->sources[$source_id]["PARAMETERS"]=serialize($this->sources[$source_id]["PARAMETERS"]);
		}
		$requete="replace into connectors_sources (source_id,id_connector,parameters,comment,name,repository,retry,ttl,timeout,opac_allowed,upload_doc_num,rep_upload,enrichment,opac_affiliate_search,opac_selected,gestion_selected,type_enrichment_allowed,ico_notice)
			values('".$source_id."','".addslashes($this->get_id())."','".addslashes($this->sources[$source_id]["PARAMETERS"])."','".addslashes($this->sources[$source_id]["COMMENT"])."','".addslashes($this->sources[$source_id]["NAME"])."','".addslashes($this->sources[$source_id]["REPOSITORY"])."','".addslashes($this->sources[$source_id]["RETRY"])."','".addslashes($this->sources[$source_id]["TTL"])."','".addslashes($this->sources[$source_id]["TIMEOUT"])."','".addslashes($this->sources[$source_id]["OPAC_ALLOWED"])."','".addslashes($this->sources[$source_id]["UPLOAD_DOC_NUM"])."','".addslashes($this->sources[$source_id]["REP_UPLOAD"])."','".addslashes($this->sources[$source_id]["ENRICHMENT"])."','".addslashes($this->sources[$source_id]["OPAC_AFFILIATE_SEARCH"])."','".addslashes($this->sources[$source_id]["OPAC_SELECTED"])."','".addslashes($this->sources[$source_id]["GESTION_SELECTED"])."','".addslashes($this->sources[$source_id]["TYPE_ENRICHMENT_ALLOWED"])."','".addslashes($this->sources[$source_id]["ICO_NOTICE"])."')";
		$result = pmb_mysql_query($requete);
		if (!$source_id) $source_id = pmb_mysql_insert_id();

		$table_entrepot_sql = "CREATE TABLE IF NOT EXISTS `entrepot_source_".$source_id."` (
							  `connector_id` varchar(20) NOT NULL default '',
							  `source_id` int(11) unsigned NOT NULL default '0',
							  `ref` varchar(220) NOT NULL default '',
							  `date_import` datetime NOT NULL default '0000-00-00 00:00:00',
							  `ufield` char(3) NOT NULL default '',
							  `field_ind` char(2) NOT NULL default '  ',
							  `usubfield` char(1) NOT NULL default '',
							  `field_order` int(10) unsigned NOT NULL default '0',
							  `subfield_order` int(10) unsigned NOT NULL default '0',
							  `value` text NOT NULL,
							  `i_value` text NOT NULL,
							  `recid` bigint(20) unsigned NOT NULL default '0',
							  `search_id` varchar(32) NOT NULL default '',
							  PRIMARY KEY  (`connector_id`,`source_id`,`ref`,`ufield`,`usubfield`,`field_order`,`subfield_order`,`search_id`),
							  KEY `usubfield` (`usubfield`),
							  KEY `ufield_2` (`ufield`,`usubfield`),
							  KEY `recid_2` (`recid`,`ufield`,`usubfield`),
							  KEY `source_id` (`source_id`),
							  KEY `i_recid_source_id` (`recid`,`source_id`),
							  KEY `i_ref` (`ref`)
							)";
		pmb_mysql_query($table_entrepot_sql);

		//Mise � jour des cat�gories
		$sql = "DELETE FROM connectors_categ_sources WHERE num_source = ".$source_id;
		pmb_mysql_query($sql);
		if ($source_categories) {
			$values = array();
			foreach($source_categories as $acateg_id) {
				if (!$acateg_id)
					continue;
					$values[] = "(".addslashes($acateg_id).", ".addslashes($source_id).")";
			}
			$values = implode(",", $values);
			if ($values) {
				$sql = "INSERT INTO `connectors_categ_sources` (`num_categ`, `num_source`) VALUES ".$values;
				pmb_mysql_query($sql) or die (pmb_mysql_error());
			}
		}
		return $result;
	}

	//Suppression d'une source
	public function del_source($source_id) {

		$evth = events_handler::get_instance();
		$evt = new event_connector('connector', 'del_source');
		$evt->set_source_id($source_id);
		$evth->send($evt);
		if($evt->is_locked_warehouse()){
			$this->error_message = $evt->get_error_message();
			return false;
		}

		//suppression des documents num�riques int�gr�s en tant que fichiers
		$this->del_explnums($source_id);
		pmb_mysql_query("DELETE FROM external_count WHERE source_id = '".$source_id."'");
		pmb_mysql_query("DELETE FROM source_sync where source_id=".$source_id);
		$sql = "DELETE FROM connectors_categ_sources WHERE num_source = ".$source_id;
		pmb_mysql_query($sql);
		$table_entrepot_sql = "DROP TABLE `entrepot_source_$source_id`;";
		pmb_mysql_query($table_entrepot_sql);

		$requete="delete from connectors_sources where source_id=$source_id and id_connector='".addslashes($this->get_id())."'";
		return pmb_mysql_query($requete);
	}

	//R�cup�ration  des propri�t�s globales par d�faut du connecteur (timeout, retry, repository, parameters)
	public function fetch_default_global_values() {
		$this->timeout=5;
		$this->repository=2;
		$this->retry=3;
		$this->ttl=1800;
		$this->parameters="";
	}

	//R�cup�ration  des propri�t�s globales du connecteur (timeout, retry, repository, parameters)
	public function fetch_global_properties() {
		$requete="select * from connectors where connector_id='".addslashes($this->get_id())."'";
		$resultat=pmb_mysql_query($requete);
		if (pmb_mysql_num_rows($resultat)) {
			$r=pmb_mysql_fetch_object($resultat);
			$this->repository=$r->repository;
			$this->timeout=$r->timeout;
			$this->retry=$r->retry;
			$this->ttl=$r->ttl;
			$this->parameters=$r->parameters;
		} else {
			$this->fetch_default_global_values();
		}
	}

	//Formulaire des propri�t�s g�n�rales
	public function get_property_form() {
		$this->fetch_global_properties();
		return "";
	}

	public function make_serialized_properties() {
		//Mise en forme des param�tres � partir de variables globales (mettre le r�sultat dans $this->parameters)
		$this->parameters="";
	}

	//Sauvegarde des propri�t�s g�n�rales
	public function save_property_form() {
		$this->make_serialized_properties();
		$requete="replace into connectors (connector_id,parameters, retry, timeout, ttl, repository) values('".addslashes($this->get_id())."',
		'".addslashes($this->parameters)."','".$this->retry."','".$this->timeout."','".$this->ttl."','".$this->repository."')";
		return pmb_mysql_query($requete);
	}

	//Supression des notices dans l'entrepot !
	public function del_notices($source_id) {
		$requete="select * from source_sync where source_id=".$source_id;
		$resultat=pmb_mysql_query($requete);
		if (pmb_mysql_num_rows($resultat)) {
			$r=pmb_mysql_fetch_object($resultat);
			if (!$r->cancel) return false;
		}

		$evth = events_handler::get_instance();
		$evt = new event_connector('connector', 'del_notices');
		$evt->set_source_id($source_id);
		$evth->send($evt);
		if($evt->is_locked_warehouse()){
			$this->error_message = $evt->get_error_message();
			return false;
		}

		//suppression des documents num�riques int�gr�s en tant que fichiers
		$this->del_explnums($source_id);

		pmb_mysql_query("TRUNCATE TABLE entrepot_source_".$source_id);

		pmb_mysql_query("DELETE FROM external_count WHERE source_id = '".$source_id."'");

		pmb_mysql_query("delete from source_sync where source_id=".$source_id);
		return true;
	}

	//Suppression des documents num�riques int�gr�s en tant que fichiers
	public function del_explnums($source_id) {
		$q = "select value as file_name from entrepot_source_$source_id where ufield='897' and usubfield='a' and value like '/%' ";
		$r = pmb_mysql_query($q);
		if (pmb_mysql_num_rows($r)) {
			while ($row = pmb_mysql_fetch_object($r)) {
				@unlink($row->file_name);
			}
		}
	}

	//Annulation de la mise � jour (faux = synchro conserv�e dans la table, vrai = synchro supprim�e dans la table)
	public function cancel_maj($source_id) {
		return false;
	}

	//Annulation de la mise � jour (faux = synchro conserv�e dans la table, vrai = synchro supprim�e dans la table)
	public function break_maj($source_id) {
		return false;
	}

	public function sync_custom_page($source_id) {
		return '';
	}

	/**
	 * Formulaire complementaire facultatif pour la synchronisation
	 *
	 * @param int $source_id
	 * @return boolean
	 */
	public function form_pour_maj_entrepot($source_id) {
		return false;
	}

	/**
	 * N�cessaire pour passer les valeurs obtenues dans form_pour_maj_entrepot au javascript asynchrone
	 *
	 * @param int $source_id
	 * @return array
	 */
	public function get_maj_environnement($source_id) {
		return array();
	}

	/**
	 * Permet de verifier les donnees passees dans l'environnement
	 *
	 * @param int $source_id
	 * @param array $env
	 * @return array
	 */
	public function check_environnement($source_id, $env) {
	    return array();
	}

	//M.A.J. Entrep�t li� � une source
	public function maj_entrepot($source_id,$callback_progress="",$recover=false,$recover_env="") {
		return 0;
	}

	//Export d'une notice en UNIMARC
	public function to_unimarc($notice) {
	}

	//Export d'une notice en Dublin Core (c'est le minimum)
	public function to_dublin_core($notice) {
	}

	//Fonction de recherche
	public function search($source_id,$query,$search_id) {
	}

	//Recherche d'une page de r�sultat
	public function get_page_result($search_id,$page, $n_per_page) {
	}

	//Nombre de r�sultats d'une recherche
	public function get_n_results($search_id) {
	}

	//R�cup�ration de la valeur d'une autorit�
	public function get_values_from_id($id,$ufield) {
		$r="";
		switch ($ufield) {
			//Categorie
			case "60X":
				$requete="select libelle_categorie from categories where num_noeud=".$id;
				$r_cat=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_cat)) {
					$r=pmb_mysql_result($r_cat,0,0);
				}
				break;
				//Dewey
			case "676\$a686\$a":
				$requete="select indexint_name from indexint where indexint_id=".$id;
				$r_indexint=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_indexint)) {
					$r=pmb_mysql_result($r_indexint,0,0);
				}
				break;
				//Editeur
			case "210\$c":
			case "214\$c":
				$requete="select ed_name from publishers where ed_id=".$id;
				$r_pub=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_pub)) {
					$r=pmb_mysql_result($r_pub,0,0);
				}
				break;
				//Collection
			case "225\$a410\$t":
				$requete="select collection_name from collections where collection_id=".$id;
				$r_coll=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_coll)) {
					$r=pmb_mysql_result($r_coll,0,0);
				}
				break;
				//Sous collection
			case "225\$i411\$t":
				$requete="select sub_coll_name from sub_collections where sub_coll_id=".$id;
				$r_subcoll=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_subcoll)) {
					$r=pmb_mysql_result($r_subcoll,0,0);
				}
				break;
				//Auteur
			case "7XX":
				$requete="select concat(author_name,', ',author_rejete) from authors where author_id=".$id;
				$r_author=pmb_mysql_query($requete);
				if (@pmb_mysql_num_rows($r_author)) {
					$r=pmb_mysql_result($r_author,0,0);
				}
				break;
		}
		return $r;
	}

	public function get_unimarc_search_fields() {
		$fields=array();
		//Calcul de la liste des champs disponibles
		$sc=new search(false,"search_fields_unimarc");
		$lf=$sc->get_unimarc_fields();
		$sc=new search(false,"search_simple_fields_unimarc");
		$lfs=$sc->get_unimarc_fields();
		//On fusionne les deux listes
		foreach($lf as $ufield=>$values) {
			if (substr($ufield,0,3)=="id:") {
				$ufield=substr($ufield,3);
			}
			$fields[$ufield]["TITLE"]=$values["TITLE"];
			foreach($values["OPERATORS"] as $op=>$top) {
				$fields[$ufield]["OPERATORS"][$op]=$top;
			}
		}
		foreach($lfs as $ufield=>$values) {
			if (substr($ufield,0,3)=="id:") {
				$ufield=substr($ufield,3);
			}
			if (empty($fields[$ufield]["TITLE"])) {
				$fields[$ufield]["TITLE"] = $values["TITLE"];
			} else {
				foreach($values["TITLE"] as $title) {
					if (array_search($title,$fields[$ufield]["TITLE"])===false) {
						$fields[$ufield]["TITLE"][] = $title;
					}
				}
			}
			foreach($values["OPERATORS"] as $op=>$top) {
				$fields[$ufield]["OPERATORS"][$op]=$top;
			}
		}
		return $fields;
	}

	public function enrichment_is_allow(){
		return false;
	}

	public function rec_records_from_xml_array($records=array(),$source_id=0) {
		if (is_array($records) && count($records) && $source_id*1) {
			$this->source_id=$source_id;
			foreach($records as $rec) {
				//Initialisation
				$ref='';
				$ufield='';
				$usubfield='';
				$field_order=0;
				$subfield_order=0;
				$value='';
				$date_import=today();

				$ref=$rec['f'][0]['value'];
				$ref.=(($ref)?'-':'').md5(microtime());
				$n_header=array();
				$n_header['rs']=$rec['rs']['value'];
				$n_header['ru']=$rec['ru']['value'];
				$n_header['el']=$rec['el']['value'];
				$n_header['bl']=$rec['bl']['value'];
				$n_header['hl']=$rec['hl']['value'];
				$n_header['dt']=$rec['dt']['value'];

				//suppression des anciennes notices
				$this->delete_from_external_count($this->source_id, $ref);
				$this->delete_from_entrepot($this->source_id, $ref);

				//R�cup�ration d'un ID
				$recid = $this->insert_into_external_count($this->source_id, $ref);

				foreach($n_header as $hc=>$code) {
					$this->insert_header_into_entrepot($this->source_id, $ref, $date_import, $hc, $code, $recid);
				}

				for ($i=0; $i<count($rec['f']); $i++) {
					$ufield=$rec['f'][$i]['c'];
					$field_order=$i;
					$field_ind=$rec['f'][$i]['ind'];
					$ss=$rec['f'][$i]['s'];
					if (is_array($ss)) {
						for ($j=0; $j<count($ss); $j++) {
							$usubfield=$ss[$j]['c'];
							$value=$ss[$j]['value'];
							$subfield_order=$j;
							$q="insert into entrepot_source_".$this->source_id." (connector_id,source_id,ref,date_import,ufield,field_ind,usubfield,field_order,subfield_order,value,i_value,recid) values(
							'".addslashes($this->get_id())."',".$this->source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
							'".addslashes($ufield)."','".addslashes($field_ind)."','".addslashes($usubfield)."',".$field_order.",".$subfield_order.",'".addslashes($value)."',
							' ".addslashes(strip_empty_words($value))." ',$recid)";
							pmb_mysql_query($q);
						}
					}
				}
			}
		}
	}

	protected function delete_from_external_count($source_id, $ref) {
		$requete="delete from external_count where recid='".addslashes($this->get_id()." ".$source_id." ".$ref)."' and source_id = ".$source_id;
		pmb_mysql_query($requete);
	}

	protected function is_into_external_count($source_id, $ref) {
		$rid = 0;
		$query = "select rid from external_count where source_id=".$source_id." and recid='".addslashes($this->get_id()." ".$source_id." ".$ref)."' limit 1";
		$result = pmb_mysql_query($query);
		if($result && pmb_mysql_num_rows($result)) {
			$rid = pmb_mysql_result($result, 0, 0);
		}
		return $rid;
	}

	protected function insert_into_external_count($source_id, $ref) {
		$recid = 0;
		$query = "insert into external_count (recid, source_id) values('".addslashes($this->get_id()." ".$source_id." ".$ref)."', ".$source_id.")";
		$rid=pmb_mysql_query($query);
		if ($rid) $recid=pmb_mysql_insert_id();
		return $recid;
	}

	protected function insert_header_into_entrepot($source_id, $ref, $date_import, $ufield, $value, $recid, $search_id = '') {
		$query = "insert ignore into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid, search_id) values(
			'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
			'".$ufield."','',0,0,'".addslashes($value)."','',$recid, '$search_id')";
		pmb_mysql_query($query);
	}

	protected function insert_content_into_entrepot($source_id, $ref, $date_import, $ufield, $usubfield, $field_order, $subfield_order, $value, $recid, $search_id = '') {
		$query = "insert ignore into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid, search_id) values(
			'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
			'".addslashes($ufield)."','".addslashes($usubfield)."',".$field_order.",".$subfield_order.",'".addslashes($value)."',
			' ".addslashes(strip_empty_words($value))." ',$recid, '$search_id')";
		pmb_mysql_query($query);
	}

	protected function update_content_into_entrepot($source_id, $ref, $ufield, $usubfield, $field_order, $subfield_order, $search_id = '', $value = '') {
		$query = "update ignore entrepot_source_".$source_id." ";
		$query.= "set value = '".addslashes($value)."', i_value = '".addslashes(strip_empty_words($value))."' ";
		$query.= "where source_id = {$source_id} ";
		$query.= "and ref ='".addslashes($ref)."' ";
		$query.= "and ufield = '".addslashes($ufield)."' ";
		$query.= "and usubfield = '".addslashes($usubfield)."' ";
		$query.= "and field_order = {$field_order} ";
		$query.= "and subfield_order = {$subfield_order} ";
		$query.= "and search_id = '{$search_id}' ";
		$query.= "limit 1";
		pmb_mysql_query($query);
	}

	protected function insert_content_into_entrepot_multiple($records) {
		$query = "insert ignore into entrepot_source_".$records[0]["source_id"]." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid, search_id) values";
		for ($i=0; $i<count($records);$i++) {
			$record=$records[$i];
			if ($i>0) $query.=",";
			$query.="(
			'".addslashes($this->get_id())."',".$record["source_id"].",'".addslashes($record["ref"])."','".addslashes($record["date_import"])."',
			'".addslashes($record["ufield"])."','".addslashes($record["usubfield"])."',".$record["field_order"].",".$record["subfield_order"].",'".addslashes($record["value"])."',
			' ".addslashes(strip_empty_words($record["value"]))." ',".$record["recid"].", '".$record["search_id"]."')";
		}
		pmb_mysql_query($query);
	}

	protected function insert_origine_into_entrepot($source_id, $ref, $date_import, $recid, $search_id = '') {
		$this->insert_content_into_entrepot($source_id, $ref, $date_import, '801', 'a', 0, 0, 'FR', $recid, $search_id);
		$this->insert_content_into_entrepot($source_id, $ref, $date_import, '801', 'b', 0, 0, $this->get_sources()[$source_id]["NAME"], $recid, $search_id);
	}

	protected function insert_human_query_into_entrepot($source_id, $ref, $date_import, $value, $recid, $search_id = '') {
		$query = "insert ignore into entrepot_source_".$source_id." (connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid, search_id) values(
			'".addslashes($this->get_id())."',".$source_id.",'".addslashes($ref)."','".addslashes($date_import)."',
			'hum','',0,0,'".addslashes($value)."',' ".addslashes(strip_empty_words($value))." ',$recid, '$search_id')";
		pmb_mysql_query($query);
	}

	protected function delete_from_entrepot($source_id, $ref, $search_id = '') {
		$query = "delete from entrepot_source_".$source_id." where ref='".addslashes($ref)."'";
		if($search_id) {
			$query .= " and search_id='".addslashes($search_id)."'";
		}
		pmb_mysql_query($query);
	}

	protected function has_ref($source_id, $ref, $search_id = '') {
		$query = "select count(*) from entrepot_source_".$source_id." where ref='".addslashes($ref)."'";
		if($search_id) {
			$query .= " and search_id='".addslashes($search_id)."'";
		}
		$result = pmb_mysql_query($query);
		if($result) {
			return pmb_mysql_result($result, 0, 0);
		}
		return 0;
	}

	protected function get_ref($source_id, $ref, $search_id='') {
		$query = "select * from entrepot_source_".$source_id." where ref='".addslashes($ref)."'";
		if($search_id) {
			$query .= " and search_id='".addslashes($search_id)."'";
		}
		$result = pmb_mysql_query($query);
		if(!pmb_mysql_num_rows($result)) {
			return [];
		}
		$ret = [];
		while( $row = pmb_mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		return $ret;
	}

	public function apply_xsl_to_xml($xml, $xsl) {
		global $charset;
		$xh = xslt_create();
		xslt_set_encoding($xh, $charset);
		$arguments = array(
				'/_xml' => $xml,
				'/_xsl' => $xsl
		);
		$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
		xslt_free($xh);
		return $result;
	}

	/**
	 * ISBD d'une personne physique
	 */
	protected function get_isbd_physical_author($unimarcKey, $field_order, $subfield_order) {
		$name = static::$ufields[$unimarcKey][$field_order][$subfield_order];
		if(isset(static::$ufields[substr($unimarcKey, 0, 3).'$b'][$field_order][$subfield_order])) {
			$rejete = static::$ufields[substr($unimarcKey, 0, 3).'$b'][$field_order][$subfield_order];
		} else {
			$rejete = '';
		}
		if(isset(static::$ufields[substr($unimarcKey, 0, 3).'$f'][$field_order][$subfield_order])) {
			$date = static::$ufields[substr($unimarcKey, 0, 3).'$f'][$field_order][$subfield_order];
		} else {
			$date = '';
		}
		$isbd = '';
		if($rejete) {
			$isbd = $name.", ".$rejete.($date ? " (".$date.")" : "");
		} else {
			$isbd = $name.($date ? " (".$date.")" : "");
		}
		return $isbd;
	}

	/**
	 * ISBD d'une collectivit� / d'un congr�s
	 */
	protected function get_isbd_coll_congres_author($unimarcKey, $field_order, $subfield_order) {
		$name = static::$ufields[$unimarcKey][$field_order][$subfield_order];
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$b'][$field_order][$subfield_order])) {
			$subdivision = static::$ufields[substr($unimarcKey, 0, 3).'$b'][$field_order][$subfield_order];
		} else {
			$subdivision = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$d'][$field_order][$subfield_order])) {
			$numero = static::$ufields[substr($unimarcKey, 0, 3).'$d'][$field_order][$subfield_order];
		} else {
			$numero = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$f'][$field_order][$subfield_order])) {
			$date = static::$ufields[substr($unimarcKey, 0, 3).'$f'][$field_order][$subfield_order];
		} else {
			$date = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$g'][$field_order][$subfield_order])) {
			$rejete = static::$ufields[substr($unimarcKey, 0, 3).'$g'][$field_order][$subfield_order];
		} else {
			$rejete = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$k'][$field_order][$subfield_order])) {
			$lieu = static::$ufields[substr($unimarcKey, 0, 3).'$k'][$field_order][$subfield_order];
		} else {
			$lieu = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$l'][$field_order][$subfield_order])) {
			$ville = static::$ufields[substr($unimarcKey, 0, 3).'$l'][$field_order][$subfield_order];
		} else {
			$ville = '';
		}
		if(!empty(static::$ufields[substr($unimarcKey, 0, 3).'$m'][$field_order][$subfield_order])) {
			$pays = static::$ufields[substr($unimarcKey, 0, 3).'$m'][$field_order][$subfield_order];
		} else {
			$pays = '';
		}

		$isbd = $name;
		if ($rejete) {
			$isbd .= ", " .$rejete;
		}
		$liste_field = $liste_lieu = array();
		if ($subdivision) {
			$liste_field[] = $subdivision;
		}
		if ($numero) {
			$liste_field[] = $numero;
		}
		if ($date) {
			$liste_field[] = $date;
		}
		if ($lieu) {
			$liste_lieu[] = $lieu;
		}
		if ($ville) {
			$liste_lieu[] = $ville;
		}
		if ($pays) {
			$liste_lieu[] = $pays;
		}
		if (count($liste_lieu))
			$liste_field[] = implode(", ", $liste_lieu);
			if (count($liste_field)) {
				$liste_field = implode("; ", $liste_field);
				$isbd .= ' (' .$liste_field .')';
			}
			return $isbd;
	}

	public function get_external_isbd($class_name, $type = '') {
		$external_isbd = array();
		foreach (static::$ufields as $unimarcKey=>$ufield) {
			switch ($class_name){
				case 'author':
					foreach ($ufield as $field_order=>$subfield) {
						foreach ($subfield as $subfield_order=>$name) {
							switch ($type) {
								case '0':
									if ($unimarcKey == '700$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_physical_author($unimarcKey, $field_order, $subfield_order);
									} elseif($unimarcKey == '710$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_coll_congres_author($unimarcKey, $field_order, $subfield_order);
									}
									break;
								case '1':
									if ($unimarcKey == '701$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_physical_author($unimarcKey, $field_order, $subfield_order);
									} elseif($unimarcKey == '711$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_coll_congres_author($unimarcKey, $field_order, $subfield_order);
									}
									break;
								case '2':
									if ($unimarcKey == '702$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_physical_author($unimarcKey, $field_order, $subfield_order);
									} elseif($unimarcKey == '712$a') {
										$external_isbd[$field_order][$subfield_order] = $this->get_isbd_coll_congres_author($unimarcKey, $field_order, $subfield_order);
									}
									break;
							}
						}
					}
					break;
				case 'editeur':
					if ($unimarcKey == '210$c') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								if(isset(static::$ufields['210$b'][$field_order][$subfield_order])) {
									$address = static::$ufields['210$b'][$field_order][$subfield_order];
								} else {
									$address = '';
								}
								if(isset(static::$ufields['210$a'][$field_order][$subfield_order])) {
									$city = static::$ufields['210$a'][$field_order][$subfield_order];
								} else {
									$city = '';
								}
								// Determine le lieu de publication
								$l = '';
								if ($address) $l = $address;
								if ($city) $l = ($l=='') ? $city : $city.' ('.$l.')';
								if ($l=='') $l = '[S.l.]';
								$external_isbd[$field_order][$subfield_order] = $l.' : '.$name;
							}
						}
					}
					break;
				case 'indexint':
					if ($unimarcKey == '676$a' || $unimarcKey == '686$a') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								$comment = static::$ufields[substr($unimarcKey, 0, 3).'$l'][$field_order][$subfield_order];
								if($comment) {
									$external_isbd[$field_order][$subfield_order] = $name." (".$comment.")";
								} else {
									$external_isbd[$field_order][$subfield_order] = $name;
								}
							}
						}
					}
					break;
				case 'collection':
					if ($unimarcKey == '410$t' || $unimarcKey == '225$a') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								if(static::$ufields['410$x'][$field_order][$subfield_order]) {
									$issn = static::$ufields['410$x'][$field_order][$subfield_order];
								} else {
									$issn = static::$ufields['225$x'][$field_order][$subfield_order];
								}
								$external_isbd[$field_order][$subfield_order] = $name.($issn ? ', ISSN '.$issn : '');
							}
						}

					}
					break;
				case 'subcollection':
					if ($unimarcKey == '411$t' || $unimarcKey == '225$i') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								if(static::$ufields['411$x'][$field_order][$subfield_order]) {
									$issn = static::$ufields['411$x'][$field_order][$subfield_order];
								} else {
									$issn = static::$ufields['225$i'][$field_order][$subfield_order];
								}
								$external_isbd[$field_order][$subfield_order] = $name.($issn ? ', ISSN '.$issn : '');
							}
						}
					}
					break;
				case 'serie':
					if ($unimarcKey == '461$t' || $unimarcKey == '200$i') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								$external_isbd[$field_order][$subfield_order] = $name;
							}
						}
					}
					break;
				case 'categories':

					break;
				case 'titre_uniforme':
					if ($unimarcKey == '500$a') {
						foreach ($ufield as $field_order=>$subfield) {
							foreach ($subfield as $subfield_order=>$name) {
								$external_isbd[$field_order][$subfield_order] = $name;
							}
						}
					}
					break;
			}
		}
		return $external_isbd;
	}

	public function rec_isbd_record($source_id, $ref, $recid) {

		$this->get_xml_indexation();
		$query = "select * from entrepot_source_".$source_id." where ref='".addslashes($ref)."'";
		$result = pmb_mysql_query($query);
		static::$ufields = array();
		if($result) {
			while($row = pmb_mysql_fetch_object($result)) {
				static::$ufields[$row->ufield.($row->usubfield ? "$".$row->usubfield : "")][$row->field_order][$row->subfield_order] = $row->value;
			}
		}
		foreach(self::$isbd_ask_list as $infos){
			$isbd = $this->get_external_isbd($infos['class_name'], $infos['type']);
			if(count($isbd)) {
				foreach ($isbd as $field_order=>$authority) {
					foreach ($authority as $subfield_order=>$value) {
						$this->insert_content_into_entrepot($source_id, $ref, date("Y-m-d H:i:s",time()), substr($infos['class_name'],0,3), 'i', $field_order, $subfield_order, $value, $recid);
					}
				}
			}
		}
	}


	/**
	 * Insertion de lots d'enregistrements avec header,content,origine,isbd
	 *
	 * @param array $records = [
	 * 	[search_id] => 0ed5b1b97e7d91f2baa7d9372210f421,
	 * 	[source_id] => 11,
	 * 	[date_import] => 2020-09-16 08:25:12,
	 * 	[records] => [
	 * 		[$ref] => [
	 * 			[header] => [
	 * 				[rs] => *
	 * 				[ru] => *
	 * 				[el] => *
	 * 				[bl] => a
	 * 				[hl] => 2
	 * 				[dt] => a
	 * 			],
	 * 			[content] => [
	 * 				[0] => [
	 * 					[ufield] => 001
	 * 					[usubfield] =>
	 * 					[value] => S2352250X20301147
	 * 					[field_order] => 0
	 * 					[subfield_order] => 0
	 * 				],
	 * 				[1] => [
	 * 					...
	 * 				],
	 * 			[recid] => 103055
	 * 		],
	 *
	 * 	]
	 *
	 */
	protected function insert_records_into_entrepot($records) {

		$q = "insert ignore into entrepot_source_".$records['source_id']." ";
		$q.= "(connector_id,source_id,ref,date_import,ufield,usubfield,field_order,subfield_order,value,i_value,recid, search_id) ";
		$q.= "values ";
		$i = 0;

		//ISBD
		$this->get_xml_indexation();

		$addslashed_date_import = addslashes($records['date_import']);

		foreach($records['records'] as $ref=>$record) {

			$addslashed_ref = addslashes($ref);
			$addslashed_id = addslashes($this->get_id());

			//header
			foreach($record['header'] as $ufield=>$v) {
				if($i) {
					$q.= ", ";
				}
				$q.= "('".$addslashed_id."', ".$records['source_id'].", '".$addslashed_ref."', '".$addslashed_date_import."', ";
				$q.= "'".$ufield."', '', 0, 0, '".addslashes($v)."', '', ".$record['recid'].", '".$records['search_id']."')";
				$i = 1;

				//prepare isbd
				$records['records'][$ref]['isbd'][$ufield][0][0] = $v;

			}

			//content
			foreach($record['content'] as $v) {
				$q.= ", ";
				$q.= "('".$addslashed_id."', ".$records['source_id'].", '".$addslashed_ref."', '".$addslashed_date_import."', ";
				$q.= "'".$v['ufield']."', '".$v['usubfield']."', ".$v['field_order'].", ".$v['subfield_order'].", ";
				$q.= "'".addslashes($v['value'])."', '".addslashes(strip_empty_words($v["value"]))."', ".$record['recid'].", '".$records['search_id']."')";

				//prepare isbd
				$records['records'][$ref]['isbd'][$v['ufield'].($v['usubfield'] ? '$'.$v['usubfield'] : '')][$v['field_order']][$v['subfield_order']] = $v["value"];
			}

			//origine
			$q.= ", ('".$addslashed_id."', ".$records['source_id'].", '".$addslashed_ref."', '".$addslashed_date_import."', ";
			$q.= "'801', 'a', 0, 0, 'FR', '', ".$record['recid'].", '".$records['search_id']."')";
			$q.= ", ('".$addslashed_id."', ".$records['source_id'].", '".$addslashed_ref."', '".$addslashed_date_import."', ";
			$q.= "'801', 'b', 0, 0, '".addslashes($this->get_sources()[$records['source_id']]["NAME"])."', '', ".$record['recid'].", '".$records['search_id']."')";

			//ISBD
			static::$ufields = $records['records'][$ref]['isbd'];
			foreach(static::$isbd_ask_list as $infos){
				$isbd = $this->get_external_isbd($infos['class_name'], $infos['type']);
				if(!empty($isbd)) {
					foreach ($isbd as $field_order=>$authority) {
						foreach ($authority as $subfield_order=>$value) {
							$q.= ", ";
							$q.= "('".$addslashed_id."', ".$records['source_id'].", '".$addslashed_ref."', '".$addslashed_date_import."', ";
							$q.= "'".substr($infos['class_name'],0,3)."', 'i', ".$field_order.", ".$subfield_order.", ";
							$q.= "'".addslashes($value)."', '".addslashes(strip_empty_words($value))."', ".$record['recid'].", '".$records['search_id']."')";
						}
					}
				}
			}
		}
		pmb_mysql_query($q);

	}


	protected function get_xml_indexation() {

		global $include_path;
		$type = 'notices_externes';
		if(!isset(static::$xml_indexation[$type])) {
			$file = $include_path."/indexation/".$type."/champs_base_subst.xml";
			if(!file_exists($file)){
				$file = $include_path."/indexation/".$type."/champs_base.xml";
			}
			$fp=fopen($file,"r");
			if ($fp) {
				$xml=fread($fp,filesize($file));
			}
			fclose($fp);
			static::$xml_indexation[$type] = _parser_text_no_function_($xml,"INDEXATION",$file);

			for ($i=0;$i<count(static::$xml_indexation[$type]['FIELD']);$i++) { //pour chacun des champs decrits
				if(isset(static::$xml_indexation[$type]['FIELD'][$i]['ISBD']) && static::$xml_indexation[$type]['FIELD'][$i]['ISBD']){ // isbd autorit�s
					static::$isbd_ask_list[static::$xml_indexation[$type]['FIELD'][$i]['ID']]= array(
							'champ' => static::$xml_indexation[$type]['FIELD'][$i]['ID'],
							'ss_champ' => static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['ID'],
							'pond' => (isset(static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['POND']) ? static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['POND'] : ''),
							'class_name' => static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['CLASS_NAME'],
							'type' => (isset(static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['TYPE']) ? static::$xml_indexation[$type]['FIELD'][$i]['ISBD'][0]['TYPE'] : '')
					);
				}
			}
		}
	}

	/**
	 * Liste les appels de fonctions autoris�s en ajax
	 * @return array
	 */
	public function get_ajax_allowed_methods() {
		return [];
	}

}

class connecteurs {

    /* Liste des connecteurs declares */
	public $catalog = array();

	/* Instance de la classe */
	private static $instance;

	/* Tableau liste des sources */
	protected static $source_list = null;

	/* Tableau liste des sources dans lesquelles on peut chercher */
	protected static $searchable_source_list = null;


	public function __construct()
    {
        global $base_path;
        if (file_exists($base_path . "/admin/connecteurs/in/catalog_subst.xml")) {
            $catalog = $base_path . "/admin/connecteurs/in/catalog_subst.xml";
        } else {
            $catalog = $base_path . "/admin/connecteurs/in/catalog.xml";
            $this->parse_catalog($catalog);
        }
    }


    public static function get_instance() {
        if(!isset(static::$instance)) {
            static::$instance = new connecteurs();
        }
        return static::$instance;
    }

	public static function get_class_name($source_id) {

		$source_id = intval($source_id);
		if( !$source_id) {
			return '';
		}

		$connector_id="";
		$requete="select id_connector from connectors_sources where source_id=".$source_id;
		$resultat=pmb_mysql_query($requete);
		if (@pmb_mysql_num_rows($resultat)) {
			$connector_id=pmb_mysql_result($resultat,0,0);
		}
		return $connector_id;
	}


    /**
     * Construction du tableau des connecteurs disponibles
     *
     * @param string $catalog : fichier catalog
     */
    public function parse_catalog($catalog)
    {
        global $base_path, $lang;

        $xml = file_get_contents($catalog);
        $param = _parser_text_no_function_($xml, "CATALOG", $catalog);
        for ($i = 0; $i < count($param["ITEM"]); $i++) {
            $item = $param["ITEM"][$i];
            $t = array();
            $t["PATH"] = $item["PATH"];
            //Parse du manifest du connecteur!
            $xml_manifest = file_get_contents($base_path . "/admin/connecteurs/in/" . $item["PATH"] . "/manifest.xml");
            $manifest = _parser_text_no_function_($xml_manifest, "MANIFEST");
            $t["NAME"] = $manifest["NAME"][0]["value"];
            $t["AUTHOR"] = $manifest["AUTHOR"][0]["value"];
            $t["ORG"] = $manifest["ORG"][0]["value"];
            $t["DATE"] = $manifest["DATE"][0]["value"];
            $t["STATUS"] = $manifest["STATUS"][0]["value"];
            $t["URL"] = $manifest["URL"][0]["value"];
            $t["SEARCH"] = ('yes' == $manifest["SEARCH"][0]["value"]) ? 'yes' : 'no';
            $t["REPOSITORY"] = $manifest["REPOSITORY"][0]["value"];
            $t["ENRICHMENT"] = ('yes' == $manifest["ENRICHMENT"][0]["value"]) ? 'yes' : 'no';
            //Commentaires
            $comment = array();
            for ($j = 0; $j < count($manifest["COMMENT"]); $j++) {
                if (!isset($manifest["COMMENT"][$j]["lang"])) {
                    $manifest["COMMENT"][$j]["lang"] = '';
                }
                if ($manifest["COMMENT"][$j]["lang"] == $lang) {
                    $comment = $manifest["COMMENT"][$j]["value"];
                    break;
                } else if (!$manifest["COMMENT"][$j]["lang"]) {
                    $c_default = $manifest["COMMENT"][$j]["value"];
                }
            }
            if ($j == count($manifest["COMMENT"])) {
                $comment = $c_default;
            }
            $t["COMMENT"] = $comment;
            $this->catalog[$item["ID"]] = $t;
        }
    }


    /**
     * Recupere la liste des sources
     *
     * @return []
     */
    public function getSourceList()
    {
        if (!is_null(static::$source_list)) {
            return static::$source_list;
        }
        static::$source_list = [];
        $q = "SELECT * FROM connectors_sources";
        $r = pmb_mysql_query($q);
        if (!pmb_mysql_num_rows($r)) {
            return static::$source_list;
        }
        while ($row = pmb_mysql_fetch_assoc($r)) {
            static::$source_list[$row['source_id']] = $row;
        }
        return static::$source_list;
    }


    /**
     * Recupere la liste des sources dans lesquelles on peut chercher
     *
     * @return []
     */
    public function getSearchableSourceList()
    {
        if (!is_null(static::$searchable_source_list)) {
            return static::$searchable_source_list;
        }
        static::$searchable_source_list = [];

        // Parcours du catalogue des connecteurs pour trouver ceux dans lesquels on peut chercher
        $searchable_connectors = [];
        foreach($this->catalog as $k => $connector) {
            if ( ('yes' == $connector['SEARCH']) || ('yes' == $connector['REPOSITORY']) ) {
                $searchable_connectors[] = $connector['PATH'];
            }
        }

        //Parcours des sources
        $sources = $this->getSourceList();
        foreach($sources as $source) {
            if( in_array($source['id_connector'], $searchable_connectors) ) {
                static::$searchable_source_list[$source['source_id']] = $source;
            }
        }
        return static::$searchable_source_list;
    }


	public function show_connector_form($id) {
		global $base_path,$charset,$admin_connecteur_global_params,$lang,$msg;
		//Inclusion de la classe
		require_once($base_path."/admin/connecteurs/in/".$this->catalog[$id]["PATH"]."/".$this->catalog[$id]["NAME"].".class.php");
		eval("\$conn=new ".$this->catalog[$id]["NAME"]."(\"".$base_path."/admin/connecteurs/in/".$this->catalog[$id]["PATH"]."\");");
		$connector_form=$conn->get_property_form();
		$connector_form=str_replace("!!special_form!!",$connector_form,$admin_connecteur_global_params);
		//Remplacement des valeurs par d�faut
		$connector_form=str_replace("!!id!!",$id,$connector_form);
		$connector_form=str_replace("!!connecteur!!",htmlentities($this->catalog[$id]["COMMENT"],ENT_QUOTES,$charset),$connector_form);
		switch ($conn->is_repository()) {
			//Oui
			case 1:
				$connector_form=str_replace("!!repository!!","<input type='hidden' value='1' name='repository' id='repository'/>".$msg["connecteurs_yes"],$connector_form);
				break;
				//Non
			case 2:
				$connector_form=str_replace("!!repository!!","<input type='hidden' value='2' name='repository' id='repository'/>".$msg["connecteurs_no"],$connector_form);
				break;
				//Possible
			case 3:
				$connector_form=str_replace("!!repository!!","<select name='repository' id='repositiory'><option value='1' ".($conn->repository==1?"selected":"").">".$msg["connecteurs_yes"]."</option><option value='2' ".($conn->repository==2?"selected":"").">".$msg["connecteurs_no"]."</option></select>",$connector_form);
				break;
		}
		$connector_form=str_replace("!!timeout!!",$conn->timeout,$connector_form);
		$connector_form=str_replace("!!ttl!!",$conn->ttl,$connector_form);
		$connector_form=str_replace("!!retry!!",$conn->retry,$connector_form);
		return $connector_form;
	}

	public function show_source_form($id,$source_id="") {
		global $base_path,$charset,$admin_connecteur_source_global_params,$lang,$msg, $pmb_docnum_in_database_allow, $deflt_upload_repertoire;

		$content_form = $admin_connecteur_source_global_params;

		$interface_form = new interface_admin_connecteurs_form('source_form');
		$interface_form->set_enctype('multipart/form-data');

		//Inclusion de la classe
		require_once($base_path."/admin/connecteurs/in/".$this->catalog[$id]["PATH"]."/".$this->catalog[$id]["NAME"].".class.php");
		eval("\$conn=new ".$this->catalog[$id]["NAME"]."(\"".$base_path."/admin/connecteurs/in/".$this->catalog[$id]["PATH"]."\");");
		$connector_form=$conn->source_get_property_form($source_id);
		$s=$conn->get_source_params($source_id);

		$label = $msg["connecteurs_source_prop"];
		$label=str_replace("!!connecteur!!", $this->catalog[$id]["COMMENT"],$label);
		$label=str_replace("!!source!!", $s["NAME"],$label);
		$interface_form->set_label($label);

		$content_form=str_replace("!!special_form!!", $connector_form ?? "",$content_form);
		//Remplacement des valeurs par defaut
		$content_form=str_replace("!!id!!", $id ?? "", $content_form);
		$content_form=str_replace("!!source_id!!", $source_id ?? "", $content_form);
		$content_form=str_replace("!!name!!", htmlentities($s["NAME"] ?? "", ENT_QUOTES, $charset), $content_form);
		$content_form=str_replace("!!comment!!", htmlentities($s["COMMENT"] ?? "", ENT_QUOTES, $charset), $content_form);
		$content_form=str_replace("!!ico_notice!!", htmlentities($s["ICO_NOTICE"] ?? "", ENT_QUOTES, $charset), $content_form);

		$xsl_exemplaire_input = '&nbsp;<input onchange="document.source_form.action_xsl_expl.selectedIndex=1" type="file" name="xsl_exemplaire">';

		$categories_select = '<select MULTIPLE name="source_categories[]">';
		$categories_select .= '<option value="">'.$msg["source_no_category"].'</option>';
		$categories_sql = "SELECT connectors_categ.*, connectors_categ_sources.num_categ FROM connectors_categ LEFT JOIN connectors_categ_sources ON (connectors_categ_sources.num_categ = connectors_categ.connectors_categ_id AND connectors_categ_sources.num_source = ".(isset($source_id) ? $source_id : '-1').")";

		$res = pmb_mysql_query($categories_sql);
		while($row=pmb_mysql_fetch_object($res)) {
			$categories_select .= '<option value="'.$row->connectors_categ_id.'" '.(isset($row->num_categ) ? "SELECTED" : "").'>'.htmlentities($row->connectors_categ_name , ENT_QUOTES,$charset).'</option>';
		}
		$categories_select .= '</select>';
		$content_form=str_replace("!!categories!!", $categories_select, $content_form);

		if ($s["OPAC_ALLOWED"]) $content_form=str_replace("!!opac_allowed_checked!!","checked",$content_form);
		else $content_form=str_replace("!!opac_allowed_checked!!","",$content_form);

		if ($s["OPAC_SELECTED"]) $content_form=str_replace("!!opac_selected_checked!!","checked",$content_form);
		else $content_form=str_replace("!!opac_selected_checked!!","",$content_form);

		if ($s["GESTION_SELECTED"]) $content_form=str_replace("!!gestion_selected_checked!!","checked",$content_form);
		else $content_form=str_replace("!!gestion_selected_checked!!","",$content_form);

		if ($s["OPAC_AFFILIATE_SEARCH"]) $content_form=str_replace("!!opac_affiliate_search!!","checked",$content_form);
		else $content_form=str_replace("!!opac_affiliate_search!!","",$content_form);

		if ($s["UPLOAD_DOC_NUM"]) $content_form=str_replace("!!upload_doc_num!!","checked",$content_form);
		else $content_form=str_replace("!!upload_doc_num!!","",$content_form);

		switch ($conn->is_repository()) {
			//Oui
			case 1:
				$content_form=str_replace("!!repository!!","<input type='hidden' value='1' name='repository' id='repository'/>".$msg["connecteurs_yes"],$content_form);
				break;
				//Non
			case 2:
				$content_form=str_replace("!!repository!!","<input type='hidden' value='2' name='repository' id='repository'/>".$msg["connecteurs_no"],$content_form);
				break;
				//Possible
			case 3:
				$content_form=str_replace("!!repository!!","<select name='repository' id='repositiory'><option value='1' ".($s["REPOSITORY"]==1?"selected":"").">".$msg["connecteurs_yes"]."</option><option value='2' ".($s["REPOSITORY"]==2?"selected":"").">".$msg["connecteurs_no"]."</option></select>",$content_form);
				break;
		}

		if($conn->enrichment_is_allow()){
			//si l'enrichissement est possible, le propose
			$enrichment = "
		<div class='row'>
			<div class='colonne3'>
				<label for='enrichment'>".$msg['connecteurs_source_enrichment']."</label>
			</div>
			<div class='colonne_suite'>
				<input type='checkbox' name='enrichment' id='enrichment' value='1' ".($s["ENRICHMENT"] ? "checked":"")." />
			</div>
		</div>
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='type_enrichement_allowed'>".$msg['connecteurs_source_type_enrichment_allowed']."</label>
			</div>
			<div class='colonne_suite'>
				!!types!!
			</div>
		</div>
		<div class='row'>&nbsp;</div>";

			$type_enrichment_form = "
				<table class='quadrille'>
					<tr>
						<th>".$msg['enrichment_type']."</th>
						<th>".$msg['enrichment_type_allow']."</th>
					</tr>";
			//on r�cup�re les libell�s par d�faut des onglets d'enrichissement
			global $include_path,$lang;
			$file = $include_path."/enrichment/categories.xml";
			$xml = file_get_contents($file);
			$elems= _parser_text_no_function_($xml,"XMLLIST");
			$type_labels=array();
			foreach($elems['ENTRY'] as $elem){
				$type_labels[$elem['CODE']] = $elem['value'];
			}


			$enrichment_types = $conn->getTypeOfEnrichment($source_id);
			foreach($enrichment_types['type'] as $elem){
				$type=array();
				$type_enrichment_form .= "
					<tr>
						<td>";
				if(!is_array($elem)) {
					$type = array(
							'code' => $elem,
							'label' => $msg[substr($type_labels[$elem],4)]
					);
				}else{
					$type = $elem;
					if (empty($type['label']) && !empty($type_labels[$type['code']])) {
						$type['label'] = $msg[substr($type_labels[$type['code']],4)];
					}
				}
				$type_enrichment_form .= "
							".$type['label']."
						</td>
						<td>
							<input type='checkbox' name='type_enrichment_allowed[]' value='".$type['code']."' ".(in_array($type['code'],$s['TYPE_ENRICHMENT_ALLOWED']) ? "checked='checked'" : "" )." />
						</td>
					</tr>";
			}
			$type_enrichment_form .= "
				</table>";

			$enrichment=str_replace("!!types!!",$type_enrichment_form,$enrichment);
			$content_form=str_replace("!!enrichment!!",$enrichment,$content_form);
		}else{
			$content_form=str_replace("!!enrichment!!","",$content_form);
		}
		$content_form=str_replace("!!timeout!!",$s["TIMEOUT"],$content_form);
		$content_form=str_replace("!!ttl!!",$s["TTL"],$content_form);
		$content_form=str_replace("!!retry!!",$s["RETRY"],$content_form);

		//rep upload : on tient compte du param�trage
		if (!$source_id) {
			$s_rep_upload = $deflt_upload_repertoire;
		} else {
			$s_rep_upload = $s['REP_UPLOAD'];
		}

		$rep_upload_form="
				<select name='rep_upload'>";
		if ($pmb_docnum_in_database_allow) {
			$rep_upload_form.="
					<option value=''>".$msg["connecteurs_no_upload_rep"]."</option>";
		}
		//on r�cup la liste des r�pertoires d'upload...
		$res = pmb_mysql_query("select repertoire_id from upload_repertoire");
		if(pmb_mysql_num_rows($res)){
			while ($r = pmb_mysql_fetch_object($res)){
				$rep = new upload_folder($r->repertoire_id);
				$rep_upload_form.="
					<option value='".$rep->repertoire_id."' ".($s_rep_upload==$rep->repertoire_id ? "selected" : "").">".$rep->repertoire_nom."</option>";
			}
		}
		$rep_upload_form.="
				</select>";
		$content_form=str_replace("!!rep_upload!!",$rep_upload_form,$content_form);

		$interface_form->set_object_id($source_id)
		->set_connector_id($id)
		->set_confirm_delete_msg($msg["connecteurs_delete_source_confirm"])
		->set_content_form($content_form)
		->set_table_name('connectors_sources')
		->set_field_focus('name');
		return $interface_form->get_display();
	}



	public static function get_id_connector_from_source_id($source_id=0) {
		$id_connector = 0;
		$source_id = intval($source_id);
		$query = "select id_connector from connectors_sources where source_id=".$source_id;
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			$id_connector = pmb_mysql_result($result, 0, 0);
		}
		return $id_connector;
	}

	public static function get_connector_instance_from_source_id($source_id=0) {
		global $base_path;

		$contrs = static::get_instance();
		$id_connector = static::get_id_connector_from_source_id($source_id);
		$catalog_id = 0;
		foreach ($contrs->catalog as $indice=>$catalog) {
			if($catalog['NAME'] == $id_connector) {
				$catalog_id = $indice;
				break;
			}
		}
		require_once($base_path."/admin/connecteurs/in/".$contrs->catalog[$catalog_id]["PATH"]."/".$contrs->catalog[$catalog_id]["NAME"].".class.php");
		eval("\$conn=new ".$contrs->catalog[$catalog_id]["NAME"]."(\"".$base_path."/admin/connecteurs/in/".$contrs->catalog[$catalog_id]["PATH"]."\");");
		return $conn;
	}
}
