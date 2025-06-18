<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_stack.class.php,v 1.13.2.1 2021/12/02 16:14:35 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/notice.class.php');
require_once($class_path.'/indexation_record.class.php');
require_once($class_path.'/indexations_collection.class.php');
require_once($class_path.'/skos/skos_onto.class.php');
require_once($class_path.'/skos/skos_datastore.class.php');
require_once($class_path.'/curl.class.php');
require_once($class_path.'/onto/skos/onto_skos_index.class.php');
require_once($class_path.'/onto/skos/onto_skos_autoposting.class.php');

class indexation_stack {
	
	protected static $indexation_record;
	protected static $onto_index;
	protected static $self;
	protected static $values = array();
	protected static $parent_entity;
	
	protected static $prefixes_type = [
	    TYPE_NOTICE => "notices",
	    TYPE_AUTHOR => "author",
	    TYPE_CATEGORY => "categ",
	    TYPE_PUBLISHER  => "publisher", 
	    TYPE_COLLECTION => "collection",
	    TYPE_SUBCOLLECTION => "subcollection",
	    TYPE_SERIE => "serie",
	    TYPE_TITRE_UNIFORME  => "tu",
	    TYPE_INDEXINT => "indexint",
	    TYPE_EXPL => "expl",
	    TYPE_EXPLNUM => "explnum",
	    TYPE_AUTHPERSO => "authperso",
	    TYPE_CMS_SECTION => "cms_editorial",
	    TYPE_CMS_ARTICLE => "cms_editorial",
	    TYPE_CONCEPT => "skos",
	];
	
	public static function push($entity_id, $entity_type, $datatype = 'all') {
		global $base_path, $pmb_indexation_needed;
		if (!isset(self::$self)) {
			self::$self = new indexation_stack();
		}
		if (array_key_exists($entity_type.'_'.$entity_id.'_'.$datatype, self::$values) || array_key_exists($entity_type.'_'.$entity_id.'_all', self::$values)) {
			return;
		}
	
		if(!$pmb_indexation_needed){
			self::indexation_needed(1);
		}
	
		if (empty(self::$values)) {
			self::$parent_entity = array(
					'id' => $entity_id,
					'type' => $entity_type
			);
		}
		self::$values[$entity_type.'_'.$entity_id.'_'.$datatype] = array(
				'entity_id' => $entity_id,
				'entity_type' => $entity_type,
				'datatype' => $datatype,
				'timestamp' => microtime(true)*1000
		);
		
		if (self::$prefixes_type[$entity_type]) {
		    self::add_reciproc_entities(self::$prefixes_type[$entity_type], $entity_id);
		}
		
	}
	
	public static function init_indexation($token=0){
		global $pmb_indexation_needed, $pmb_indexation_in_progress, $pmb_url_internal , $pmb_indexation_last_entity;
		
		// SI pas besoin d'indexation, on fait simple !
		if(0 == $pmb_indexation_needed){
		    return;
		}
		// si on a un token dans la base, et qu'on arrive sans ! On essaye de voir si ca n'a pas planter !
		if (0 == $token && $pmb_indexation_in_progress != 0) {
		    // ON récupère la prochaine entrée à ce faire réindexer
	        $result = pmb_mysql_query("select  * from indexation_stack order by indexation_stack_timestamp, indexation_stack_entity_id, indexation_stack_entity_type, indexation_stack_datatype limit 1");
	        if(pmb_mysql_num_rows($result) > 0){
	            $row = pmb_mysql_fetch_assoc($result);
	            // Si c'est la même que la dernière qu'on a indexé, il y a un souci, un flush le token pour en relancer une nouvelle
	            // Sinon, bah c'est bon !
	            /*******************************************************************************************************************************
	             *  TODO AR 17/04/2020 : Il faut encore gérer le                                                                               *
	             *  Ca permet de gérer le cas ou l'indexation à planter en cours de route dans le milieu.                                      *
	             *  ON peut encore tomber sur le cas, ou c'est pas le process qui a planté, mais la requete suivante qui ne c'est pas lancé... *
	             *  Dans ce cas, on reste KO !                                                                                                 *
	             *******************************************************************************************************************************/
 	            $last = json_decode($pmb_indexation_last_entity);
 	            $current = new DateTime();
 	            $lastStart = new DateTime($last->started->date);
 	            $diff = $current->diff($lastStart,true);
 	            unset($last->started);
 	            $pmb_indexation_last_entity = json_encode($last);
 	            $time =new DateInterval("PT10M");
 	            // Si c'est différent, c'est que le process tourne
 	            if(encoding_normalize::json_encode($row) !== $pmb_indexation_last_entity){
 	                return ;
 	            }else{ 
 	                // Ca n'a pas bougé, on check depuis combien de temps c'est comme ca.
 	                if($diff->format('%i') <= $time->format("%i")){
 	                    //Ca fait moins de 30min, on ne touche à rien
 	                    return ;
 	                   
 	                }
 	                //Ca fait au moins 10 min que rien n'a bougé, on relance la machine
 	                self::indexation_in_progress($token);
	            }
	        }
		}
		$curl = new Curl();
		$curl->set_option('CURLOPT_TIMEOUT', '1');
		$curl->set_option('CURLOPT_SSL_VERIFYPEER',false);
		
		$curl->get($pmb_url_internal.'indexation_stack.php?token='.$token.'&database='.LOCATION);
// 		$error = $curl->error();
	}
	
	public static function launch_indexation($token=0){
	    global $pmb_indexation_needed, $pmb_indexation_in_progress;
		
		if (!$pmb_indexation_needed || ($token != $pmb_indexation_in_progress && $pmb_indexation_in_progress != 0)) {
			return;
		}
		// Si on arrive, soit on a besoin de réindexer et le token en base  est zéro, soit on a le bon token
		if($token == 0){
		    $token = md5(microtime(true).'_toindex_'.random_bytes(12));
		    self::indexation_in_progress($token);
		}
			
		$limit = 100;
		
		$query = "select * from indexation_stack order by indexation_stack_timestamp, indexation_stack_entity_id, indexation_stack_entity_type, indexation_stack_datatype limit ".$limit;
		$result = pmb_mysql_query($query);
		$nb_results = pmb_mysql_num_rows($result);
		if ($nb_results < $limit) {
			self::indexation_needed(0);
		}
		
		if($nb_results){
			while($row = pmb_mysql_fetch_assoc($result)){
			    self::indexation_last_entity($row);
				self::index_entity($row['indexation_stack_entity_id'], $row['indexation_stack_entity_type'], $row['indexation_stack_datatype']);	
			}
		}
		if ($nb_results < $limit) {
		    self::indexation_in_progress(0);
		    return;
		}
		self::init_indexation($token);
	}
	
	public static function index_entity($entity_id, $entity_type, $datatype){
		switch($entity_type){
			case TYPE_NOTICE:
			    $info = notice::indexation_prepare($entity_id);
				if($datatype == 'all'){
					notice::majNotices($entity_id);
				}
				notice::majNoticesGlobalIndex($entity_id);
				notice::majNoticesMotsGlobalIndex($entity_id, $datatype);
				notice::indexation_restaure($info);
				break;
			case TYPE_AUTHOR:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_AUTHORS);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_CATEGORY:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_CATEG);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_PUBLISHER:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_PUBLISHERS);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_COLLECTION:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_COLLECTIONS);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_SUBCOLLECTION:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_SUB_COLLECTIONS);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_SERIE:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_SERIES);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_TITRE_UNIFORME:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_TITRES_UNIFORMES);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_INDEXINT:
				$indexation_authority = indexations_collection::get_indexation(AUT_TABLE_INDEXINT);
				$indexation_authority->maj($entity_id, $datatype);
				break;
			case TYPE_EXPLNUM:
				//TODO: Regarder ou est faite l'indexation des documents numériques
				break;
			case TYPE_AUTHPERSO:
			    global $include_path;
			    $authperso_authority = authorities_collection::get_authority(AUT_TABLE_AUTHPERSO, $entity_id);
			    $authperso_num = $authperso_authority->info['authperso_num'];
			    $indexation_authperso = new indexation_authperso($include_path."/indexation/authorities/authperso/champs_base.xml", "authorities", (1000+$authperso_num), $authperso_num);
			    $indexation_authperso->set_deleted_index(true);
			    $indexation_authperso->maj($entity_id, $datatype);
				break;
			case TYPE_CMS_SECTION:
				//TODO
				break;
			case TYPE_CMS_ARTICLE:
				//TODO
				break;
			case TYPE_CONCEPT:
				if(!isset(static::$onto_index)){
					static::$onto_index = new onto_skos_index();
					static::$onto_index->load_handler('', skos_onto::get_store(), array(), skos_datastore::get_store(), array(), array(), 'http://www.w3.org/2004/02/skos/core#prefLabel');
				}
				static::$onto_index->maj($entity_id, '', $datatype);
				break;
		}
		$query = "delete from indexation_stack where indexation_stack_entity_type = '".$entity_type."'
				and indexation_stack_entity_id = '".$entity_id."' and indexation_stack_datatype = '".$datatype."' ";
		pmb_mysql_query($query);
	}
	
	public function __destruct() {
		global $dbh;
		
		if (!empty(self::$values)) {
			$dbh = connection_mysql();
			$values = '';
			foreach (self::$values as $value) {
				if ($values) {
					$values.= ',';
				}
				$values.= '("'.$value['entity_id'].'", "'.$value['entity_type'].'", "'.$value['datatype'].'", "'.$value['timestamp'].'", "'.self::$parent_entity['id'].'", "'.self::$parent_entity['type'].'")';
			}
			$query = 'insert into indexation_stack (indexation_stack_entity_id, indexation_stack_entity_type, indexation_stack_datatype, indexation_stack_timestamp, indexation_stack_parent_id, indexation_stack_parent_type)
				values '.$values;
			pmb_mysql_query($query);
		}
	}
	
	public static function get_indexation_state(){
		global $pmb_indexation_in_progress;
		self::init_indexation();
		if (!$pmb_indexation_in_progress) {
			return array();
		}
		$query = 'SELECT count(indexation_stack_entity_id) as nb_entity, indexation_stack_entity_type as entity_type, indexation_stack_parent_id as parent_id, indexation_stack_parent_type as parent_type from indexation_stack group by indexation_stack_parent_type, indexation_stack_parent_id, indexation_stack_entity_type order by indexation_stack_timestamp';
		$result = pmb_mysql_query($query);
		$data = array();
		if(pmb_mysql_num_rows($result)){
			while($row = pmb_mysql_fetch_assoc($result)){
				
				if(!isset($data[$row['parent_type'].'_'.$row['parent_id']])){
					$data[$row['parent_type'].'_'.$row['parent_id']] = array(
						'label' => self::get_entity_isbd($row['parent_type'], $row['parent_id']),
						'children' => array()
					);					
				}
				$data[$row['parent_type'].'_'.$row['parent_id']]['children'][] = array(
					'entity_label' => self::get_label_from_type($row['entity_type']),
					'nb' => $row['nb_entity'],
				);
			}
		}
		return $data;
	}
	
	protected static function get_entity_isbd($entity_type, $entity_id) {
		global $msg;
		$label = '';
		switch ($entity_type) {
			case TYPE_NOTICE :
				$label = $msg['288'].' : '.notice::get_notice_title($entity_id);
				break;
			case TYPE_AUTHOR :
				$authority = new authority(0, $entity_id, AUT_TABLE_AUTHORS);
				break;
			case TYPE_CATEGORY :
				$authority = new authority(0, $entity_id, AUT_TABLE_CATEG);
				break;
			case TYPE_PUBLISHER :
				$authority = new authority(0, $entity_id, AUT_TABLE_PUBLISHERS);
				break;
			case TYPE_COLLECTION :
				$authority = new authority(0, $entity_id, AUT_TABLE_COLLECTIONS);
				break;
			case TYPE_SUBCOLLECTION :
				$authority = new authority(0, $entity_id, AUT_TABLE_SUB_COLLECTIONS);
				break;
			case TYPE_SERIE :
				$authority = new authority(0, $entity_id, AUT_TABLE_SERIES);
				break;
			case TYPE_TITRE_UNIFORME :
				$authority = new authority(0, $entity_id, AUT_TABLE_TITRES_UNIFORMES);
				break;
			case TYPE_INDEXINT :
				$authority = new authority(0, $entity_id, AUT_TABLE_INDEXINT);
				break;
			case TYPE_CONCEPT :
				$authority = new authority(0, $entity_id, AUT_TABLE_CONCEPT);
				break;
			case TYPE_EXPLNUM:
				//TODO
				break;
			case TYPE_AUTHPERSO:
				$authority = new authority(0, $entity_id, AUT_TABLE_AUTHPERSO);
				break;
			case TYPE_CMS_SECTION:
				//TODO
				break;
			case TYPE_CMS_ARTICLE:
				//TODO
				break;
			default :
				break;
		}
		if (!empty($authority)) {
			// On est dans le cas d'une autorité
			$label = $authority->get_type_label().' : '.$authority->get_isbd();
		}
		return $label;
	}
	
	public static function get_label_from_type($entity_type){
		global $msg;
		$label = " : ";
		switch ($entity_type) {
			case TYPE_NOTICE :
				$label = $msg['130'].$label;
				break;
			case TYPE_AUTHOR :
				$label = $msg['133'].$label;
				break;
			case TYPE_CATEGORY :
				$label = $msg['134'].$label;
				break;
			case TYPE_PUBLISHER :
				$label = $msg['135'].$label;
				break;
			case TYPE_COLLECTION :
				$label = $msg['136'].$label;
				break;
			case TYPE_SUBCOLLECTION :
				$label = $msg['137'].$label;
				break;
			case TYPE_SERIE :
				$label = $msg['search_extended_series'].$label;
				break;
			case TYPE_TITRE_UNIFORME :
				$label = $msg['search_extended_titres_uniformes'].$label;
				break;
			case TYPE_INDEXINT :
				$label = $msg['search_extended_indexint'].$label;
				break;
			case TYPE_CONCEPT :
				$label = $msg['skos_view_concepts_concepts'].$label;
				break;
			case TYPE_EXPLNUM:
				break;
			case TYPE_AUTHPERSO:
				$label = $msg['authperso_multi_search_title'].$label;
				break;
			case TYPE_CMS_SECTION:
				//TODO
				break;
			case TYPE_CMS_ARTICLE:
				//TODO
				break;
			default :
				break;
		}
		return $label;
	}
	
	protected static function indexation_in_progress($in_progress) {
		global $pmb_indexation_in_progress;
		
		$pmb_indexation_in_progress = $in_progress;
		$query = "update parametres set valeur_param = '".$in_progress."' where type_param = 'pmb' and sstype_param = 'indexation_in_progress' ";
		pmb_mysql_query($query);
	}
	
	protected static function indexation_last_entity($entity) {
	    global $pmb_indexation_last_entity;
	    
	    $entity['started'] = new DateTime();
	    $pmb_indexation_last_entity = encoding_normalize::json_encode($entity);
	    $query = "update parametres set valeur_param = '".addslashes($pmb_indexation_last_entity)."' where type_param = 'pmb' and sstype_param = 'indexation_last_entity' ";
	    pmb_mysql_query($query);
	}
	protected static function indexation_needed($needed) {
		global $pmb_indexation_needed;
		
		$pmb_indexation_needed = $needed;
		$query = "update parametres set valeur_param = '".$needed."' where type_param = 'pmb' and sstype_param = 'indexation_needed' ";
		pmb_mysql_query($query);
	}
	
	protected static function add_reciproc_entities($prefix, $id) {
	    global $charset;
	    
	    $query = "
                SELECT DISTINCT idchamp, options, datatype 
                FROM " . $prefix . "_custom 
                JOIN " . $prefix . "_custom_values ON " . $prefix . "_custom_champ = idchamp
                WHERE type = 'query_auth'
                AND " . $prefix . "_custom_origine = " . $id . "
                ";
	    $result = pmb_mysql_query($query);
	    if (pmb_mysql_num_rows($result)) {
	        while($row = pmb_mysql_fetch_array($result)){
	            $option = _parser_text_no_function_("<?xml version='1.0' encoding='".$charset."'?>\n".$row['options'], "OPTIONS");
	            if ($option["RECIPROC"][0]["value"] == "yes") {
	                $entity_type = self::get_type_from_datatype($option["DATA_TYPE"][0]["value"]);
	                $query_custom = "
                        SELECT " . $prefix . "_custom_" . $row['datatype'] . " FROM " . $prefix . "_custom_values 
                        WHERE " . $prefix . "_custom_champ = " . $row['idchamp'] . " 
                        AND " . $prefix . "_custom_origine = " . $id;
	                $result_custom = pmb_mysql_query($query_custom);
	                if (pmb_mysql_num_rows($result_custom)) {
	                    while($row_custom = pmb_mysql_fetch_array($result_custom)){
	                        if (intval($row_custom[$prefix . "_custom_" . $row['datatype']])) {
    	                        self::$values[$entity_type.'_'.$row_custom[$prefix . "_custom_" . $row['datatype']].'_all'] = array(
    	                            'entity_id' => $row_custom[$prefix . "_custom_" . $row['datatype']],
    	                            'entity_type' => $entity_type,
    	                            'datatype' => "reciproc_pperso",
    	                            'timestamp' => microtime(true)*1000
    	                        );
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	
	protected static function get_type_from_datatype($type) {
	    $type = intval($type);
	    if ($type > 1000) {
	        return TYPE_AUTHPERSO;
	    }
	    return $type;
	}
}