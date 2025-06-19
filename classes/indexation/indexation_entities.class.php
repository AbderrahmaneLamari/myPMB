<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_entities.class.php,v 1.1.2.5 2023/06/20 07:21:19 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/indexation.class.php");

//classe générique de calcul d'indexation des entités...
class indexation_entities extends indexation {
	
	protected $mode = 'file'; //sql | file
	protected $directory_files = '';
	protected $deleted_index = true;
	
	protected $fields_prefix = '';
	protected $words_prefix = '';
	
	public static $steps = [
			'queries',
			'custom_fields',
			'authperso',
			'authperso_link',
			'isbd_ask_list',
			'callables'
	];
	
	public function __construct($xml_filepath, $table_prefix, $type = 0){
		global $base_path;
		
		$this->directory_files = $base_path.'/temp/indexation/';
		parent::__construct($xml_filepath, $table_prefix, $type);
	}
	
	public function raz_fields_table() {
		//remise a zero de la table au début
		pmb_mysql_query("TRUNCATE TABLE ".$this->fields_prefix."_global_index");
	}
	
	public function raz_words_table() {
		//remise a zero de la table au début
		pmb_mysql_query("TRUNCATE TABLE ".$this->words_prefix."_global_index");
	}
	
	public function disable_fields_table_keys() {
		pmb_mysql_query("ALTER TABLE ".$this->fields_prefix."_global_index DISABLE KEYS");
	}
	
	public function disable_words_table_keys() {
		pmb_mysql_query("ALTER TABLE ".$this->words_prefix."_global_index DISABLE KEYS");
	}
	
	public function enable_fields_table_keys() {
		pmb_mysql_query("ALTER TABLE ".$this->fields_prefix."_global_index ENABLE KEYS");
	}
	
	public function enable_words_table_keys() {
		pmb_mysql_query("ALTER TABLE ".$this->words_prefix."_global_index ENABLE KEYS");
	}
	
	protected function add_direct_fields($object_id, $datatype='all') {
		//Recherche des champs directs
		if($this->check_datatype($datatype) && isset($this->temp_not['f']) && count($this->temp_not['f'])) {
			$this->queries[0]["rqt"]= "select ".$this->reference_table.".".$this->reference_key." as subst_for_indexation, ".implode(',',$this->temp_not['f'][0])." from ".$this->reference_table;
			if($object_id) {
				$this->queries[0]["rqt"].=" where ".$this->reference_key."='".$object_id."'";
			}
			$this->queries[0]["table"]=$this->reference_table;
		}
	}
	
	protected function get_select_fields_external($table, $k, $v) {
		$select = parent::get_select_fields_external($table, $k, $v);
		$select[] = $this->reference_table.".".$this->reference_key." as subst_for_indexation";
		return $select;
	}
	
	protected function get_query_select_isbd_external($id_aut) {
		return "select $id_aut as id_aut_for_isbd, ".$this->reference_table.".".$this->reference_key." as subst_for_indexation from ".$this->reference_table;
	}
	
	protected function get_query_where_external($table) {
		$where="";
		if(isset($table['FILTER'])){
			foreach ( $table['FILTER'] as $filter ) {
				if($tmp=trim($filter["value"])){
					if(empty($where)) {
						$where.=" WHERE (".$tmp.")";
					} else {
						$where.=" AND (".$tmp.")";
					}
				}
			}
		}
		return $where;
	}
	
	protected function get_query_order_by_external() {
		return " ORDER BY subst_for_indexation";
	}
	
	protected function get_tables_from_external_field_factory($table, $v) {
		switch ($v['DATATYPE']) {
			case 'aut_link':
				$indexation_aut_link = new indexation_aut_link($this->type);
				$indexation_aut_link->set_type('entities');
				return $indexation_aut_link->get_tables($table['NAME']);
		}
	}
	
	protected function init_external_field_union_rqt($table, $k, $v) {
		$query = "SELECT * FROM (".implode(" union ",$this->queries[$k]["new_rqt"]['rqt']).") AS rqt";
		$query .= $this->get_query_order_by_external();
		$this->queries[$k]["rqt"] = $query;
	}
	
	protected function init_external_field_union_isbd_tab_req($table, $k, $v) {
		$query = "SELECT * FROM (".implode(" union ",$this->isbd_tab_req).") AS rqt";
		$query .= $this->get_query_order_by_external();
		$this->isbd_ask_list[$k]['req']=  $query;
	}
	
	protected function add_mots_query_text($nom_champ, $value, $langage, $keep_empty=false) {
		if($this->mode == 'file') {
			return array();
		}
		parent::add_mots_query_text($nom_champ, $value, $langage, $keep_empty);
	}
	
	protected function add_data_tab_insert($object_id, $infos, $value, $order_fields, $keep_empty=false) {
		if($this->mode == 'file') {
			return array();
		}
		parent::add_data_tab_insert($object_id, $infos, $value, $order_fields, $keep_empty);
	}
	
	protected function add_custom_data_tab_insert($object_id, $infos, $values, $order_fields, $keep_empty=false) {
		if($this->mode == 'file') {
			return array();
		}
		parent::add_custom_data_tab_insert($object_id, $infos, $values, $order_fields, $keep_empty);
	}
		
	protected function get_array_file_field_insert($object_id, $order_fields, $isbd, $autorite = 0) {
		return array($object_id, $order_fields, addslashes(trim($isbd)), (intval($autorite)));
	}
	
	protected function add_file_field_insert($object_id,$infos,$order_fields,$isbd, $lang = '', $autorite = 0) {
		if(!empty($this->directory_files)) {
			$content = $this->get_array_file_field_insert($object_id, $order_fields, $isbd, $autorite);
			$filename = "indexation_".LOCATION."_".$infos["champ"]."_".$infos["ss_champ"]."_".$infos["pond"].($lang ? "_".$lang : '').".pmb";
			file_put_contents($this->directory_files.$filename, json_encode($content)."\r\n", FILE_APPEND);
		}
	}
	
	protected function add_tab_field_insert($object_id,$infos,$order_fields,$isbd, $lang = '', $autorite = 0) {
		switch ($this->mode) {
			case 'file':
				$this->add_file_field_insert($object_id,$infos,$order_fields,$isbd, $lang, $autorite);
				break;
			default:
				parent::add_tab_field_insert($object_id, $infos, $order_fields, $isbd, $lang, $autorite);
				break;
		}
	}
	
	public function launch_indexation($token=0){
		//on s'assure qu'on a lu le XML et initialisé ce qu'il faut...
		if(!$this->initialized) {
			$this->init();
		}
		
		//on a des éléments à indexer...
		if (!$this->champ_trouve) {
			return false;
		}
		if(is_dir($this->directory_files)) {
// 			mkdir($this->directory_files);
		}
		netbase_entities::clean_files($this->directory_files);
		
		// on empile l'indexation dans le répertoire temp
		$this->maj(0);
		
		$this->raz_fields_table();
		$this->raz_words_table();
		$this->disable_fields_table_keys();
		$this->disable_words_table_keys();
		$this->maj_bdd_from_files();
		$this->enable_fields_table_keys();
		$this->enable_words_table_keys();
	}
	
	/**
	 * METHODE A RETIRER A LA FIN DES TESTS
	 * {@inheritDoc}
	 * @see indexation::maj()
	 */
	public function maj($object_id,$datatype='all'){
		$object_id = intval($object_id);
		//on s'assure qu'on a lu le XML et initialisé ce qu'il faut...
		if(!$this->initialized) {
			$this->init();
		}
		
		//on a des éléments à indexer...
		if ($this->champ_trouve) {
			//Recherche des champs directs
			$this->add_direct_fields($object_id, $datatype);
			//qu'est-ce qu'on efface?
			if(!$this->deleted_index) {
				$this->delete_index($object_id, $datatype);
			}
			
			//on réinitialise les tableaux d'injection
			//qu'est-ce qu'on met a jour ?
			$this->tab_insert=array();
			$this->tab_field_insert=array();
			
			$uniqId = PHP_log::prepare_time($this->get_label().' : maj_queries', 'indexation');
			$this->maj_queries($object_id, $datatype);
			PHP_log::register($uniqId);
			
			// Les champs perso
			$uniqId = PHP_log::prepare_time($this->get_label().' : maj_custom_fields', 'indexation');
			$this->maj_custom_fields($object_id, $datatype);
			PHP_log::register($uniqId);
			
			//Les autorités perso
			$uniqId = PHP_log::prepare_time($this->get_label().' : maj_authperso', 'indexation');
			$this->maj_authperso($object_id, $datatype);
			PHP_log::register($uniqId);
			
			// Les autorités perso liées
			$uniqId = PHP_log::prepare_time($this->get_label().' : maj_authperso_link', 'indexation');
			$this->maj_authperso_link($object_id, $datatype);
			PHP_log::register($uniqId);
			
			if(count($this->isbd_ask_list)){
				$uniqId = PHP_log::prepare_time($this->get_label().' : maj_isbd_ask_list', 'indexation');
				$this->maj_isbd_ask_list($object_id, $datatype);
				PHP_log::register($uniqId);
			}
			
			if (count($this->callables)) {
				$uniqId = PHP_log::prepare_time($this->get_label().' : maj_callables', 'indexation');
				$this->maj_callables($object_id, $datatype);
				PHP_log::register($uniqId);
			}
			
// 			$this->save_elements($this->tab_insert, $this->tab_field_insert);
		}
	}
	
	public function maj_by_step($step=0){
		$object_id = 0;
		$datatype = 'all';
		
		//on s'assure qu'on a lu le XML et initialisé ce qu'il faut...
		if(!$this->initialized) {
			$this->init();
		}
		
		//on a des éléments à indexer...
		if ($this->champ_trouve) {
			//Recherche des champs directs
			$this->add_direct_fields($object_id, $datatype);
			//qu'est-ce qu'on efface?
			if(!$this->deleted_index) {
				$this->delete_index($object_id, $datatype);
			}
			
			//on réinitialise les tableaux d'injection
			//qu'est-ce qu'on met a jour ?
			$this->tab_insert=array();
			$this->tab_field_insert=array();
			
			$uniqId = PHP_log::prepare_time($this->get_label().' : maj_'.static::$steps[$step], 'indexation');
			switch (static::$steps[$step]) {
				case 'queries':
					$this->maj_queries($object_id, $datatype);
					break;
				case 'custom_fields':
					// Les champs perso
					$this->maj_custom_fields($object_id, $datatype);
					break;
				case 'authperso':
					//Les autorités perso
					$this->maj_authperso($object_id, $datatype);
					break;
				case 'authperso_link':
					// Les autorités perso liées
					$this->maj_authperso_link($object_id, $datatype);
					break;
				case 'isbd_ask_list':
					//ISBD
					if(count($this->isbd_ask_list)){
						$this->maj_isbd_ask_list($object_id, $datatype);
					}
					break;
				case 'callables':
					if (count($this->callables)) {
						$this->maj_callables($object_id, $datatype);
					}
					break;
			}
			PHP_log::register($uniqId);
			
			$this->maj_bdd_from_files();
			
			$step++;
			if(!empty(static::$steps[$step])) {
				return $step;
			}
			return 0;
		}
		return 0;
	}
	
	protected function maj_custom_field($object_id, $table, $id, $code_champ) {
		global $charset;
		
		$p_perso = $this->get_parametres_perso_class($table);
		$prefix = $table;
		$query = "SELECT ".$prefix."_custom_champ,".$prefix."_custom_origine,".$prefix."_custom_small_text, ".$prefix."_custom_text, ".$prefix."_custom_integer, ".$prefix."_custom_date, ".$prefix."_custom_float, ".$prefix."_custom_order, datatype 
			FROM ".$prefix."_custom_values
			JOIN ".$prefix."_custom ON ".$prefix."_custom.idchamp = ".$prefix."_custom_values.".$prefix."_custom_champ AND ".$prefix."_custom.search = 1
			ORDER BY ".$prefix."_custom_origine, ".$prefix."_custom_order";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_array($result)) {
				$code_ss_champ = $row[$prefix."_custom_champ"];
				$value = $row[$prefix."_custom_".$row['datatype']];
				$order =  $row[$prefix."_custom_order"];
				//on doit retrouver l'id des eléments...
				switch($table){
					case "expl" :
						$object_id = exemplaire::get_expl_notice_from_id($row[$prefix."_custom_origine"]);
						break;
					case "explnum" :
						$query_explnum = "select explnum_notice, explnum_bulletin from explnum where explnum_id = ".$row[$prefix."_custom_origine"];
						$result_explnum = pmb_mysql_query($query_explnum);
						$row_explnum = pmb_mysql_fetch_object($result_explnum);
						if($row_explnum->explnum_notice) {
							$object_id = $row_explnum->explnum_notice;
						}
						break;
					default :
						$object_id = $row[$prefix."_custom_origine"];
						break;
				}
				
				$val = stripslashes($p_perso->get_formatted_output(array($value),$code_ss_champ)).' ';//Sa valeur
// 				if ($this->t_fields[$field_id]["TYPE"] == "query_auth") {
// 					$return_val[$field_id] = $this->get_enhanced_values($return_val[$field_id], $value, $field_id);
// 				}
				//la table pour les recherche exacte
				$infos = array(
						'champ' => $code_champ,
						'ss_champ' => $code_ss_champ,
						'pond' => $p_perso->get_pond($code_ss_champ)
				);
				//Elimination des balises HTML - Y compris celles mal formées
				$val = preg_replace('#<[^>]+>#','',$val);
				//Lorsque cela est entité en base (ex : Editeur HTML)
				$val = html_entity_decode($val, ENT_QUOTES, $charset);
				if($val != ''){
					$this->add_tab_field_insert($object_id, $infos, $order, $val);
				}
			}
			pmb_mysql_free_result($result);
		}
	}
	
	protected function maj_custom_fields($object_id, $datatype='all') {
		if(count($this->tab_pp) && $this->check_datatype($datatype, 'custom_field')) {
			foreach ($this->tab_pp as $code_champ => $table ) {
				$this->maj_custom_field($object_id, $table, 0, $code_champ);
			}
		}
	}
	
	protected function maj_authperso_custom_field() {
		global $charset;
		
		$p_perso = $this->get_parametres_perso_class('authperso');
		$prefix = 'authperso';
		$query = "SELECT ".$prefix."_custom_champ,".$prefix."_custom_origine,".$prefix."_custom_small_text, ".$prefix."_custom_text, ".$prefix."_custom_integer, ".$prefix."_custom_date, ".$prefix."_custom_float, ".$prefix."_custom_order, num_type, datatype
			FROM ".$prefix."_custom_values
			JOIN ".$prefix."_custom ON ".$prefix."_custom.idchamp = ".$prefix."_custom_values.".$prefix."_custom_champ AND ".$prefix."_custom.search = 1
			ORDER BY ".$prefix."_custom_origine, ".$prefix."_custom_order";
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_array($result)) {
				$code_champ = $this->authperso_code_champ_start+$row['num_type'];
				$code_ss_champ = $row[$prefix."_custom_champ"];
				$value = $row[$prefix."_custom_".$row['datatype']];
				$order =  $row[$prefix."_custom_order"];
				$object_id = $row[$prefix."_custom_origine"];
				
				$val = stripslashes($p_perso->get_formatted_output(array($value),$code_ss_champ)).' ';//Sa valeur
// 				if ($this->t_fields[$field_id]["TYPE"] == "query_auth") {
// 					$return_val[$field_id] = $this->get_enhanced_values($return_val[$field_id], $value, $field_id);
// 				}
					//la table pour les recherche exacte
				$infos = array(
						'champ' => $code_champ,
						'ss_champ' => $code_ss_champ,
						'pond' => $p_perso->get_pond($code_ss_champ)
				);
				//Elimination des balises HTML - Y compris celles mal formées
				$val = preg_replace('#<[^>]+>#','',$val);
				//Lorsque cela est entité en base (ex : Editeur HTML)
				$val = html_entity_decode($val, ENT_QUOTES, $charset);
				if($val != ''){
					$this->add_tab_field_insert($object_id, $infos, $order, $val);
				}
			}
			pmb_mysql_free_result($result);
		}
	}
	
	protected function maj_authperso($object_id, $datatype='all') {
		global $charset;
		
		if(count($this->tab_authperso) && $this->check_datatype($datatype, 'authperso')) {
			$order_fields=1;
			$query = "SELECT id_authperso, notice_authperso_authority_num, id_authperso_authority 
				FROM authperso, notices_authperso,authperso_authorities 
				WHERE id_authperso=authperso_authority_authperso_num and notice_authperso_authority_num=id_authperso_authority
				ORDER BY notice_authperso_notice_num, notice_authperso_order";
			$result = pmb_mysql_query($query);
			$id_authperso_authority = 0;
			while($row = pmb_mysql_fetch_object($result)) {
				if(empty($id_authperso_authority) || ($id_authperso_authority != $row->id_authperso_authority)) {
					$order_fields=1;
				}
				$code_champ = $row->id_authperso+$this->authperso_code_champ_start;
				//la table pour les recherche exacte
				$infos = array(
						'champ' => $code_champ,
						'ss_champ' => 0,
						'pond' => 0
				);
				
				$isbd = $this->get_entity_isbd('authperso', $row->id_authperso_authority);
				//Elimination des balises HTML - Y compris celles mal formées
				$isbd = preg_replace('#<[^>]+>#','',$isbd);
				//Lorsque cela est entité en base (ex : Editeur HTML)
				$isbd = html_entity_decode($isbd, ENT_QUOTES, $charset);
				
				$this->add_tab_field_insert($row->id_authperso_authority, $infos, $order_fields, $isbd);
				
				$this->add_data_tab_insert($object_id, $infos, $isbd, $order_fields);
				$order_fields++;
// 				$index_fields[$field['code_champ']]['ss_champ'][0][]
			}
			
			$this->maj_authperso_custom_field();
		}
	}

	protected function get_query_authperso_link($object_id) {
		$object_id = intval($object_id);
		$authority_type = $this->get_authority_type();
		return "
		SELECT id_authperso_authority, authperso_authority_authperso_num
		FROM ".$this->reference_table."
		JOIN aut_link ON (".$this->reference_table.".".$this->reference_key."=aut_link.aut_link_from_num and aut_link_from = ".$authority_type." or (".$this->reference_table.".".$this->reference_key." = aut_link_to_num and aut_link_to = ".$authority_type." ))
		JOIN authperso_authorities ON (aut_link.aut_link_to_num=authperso_authorities.id_authperso_authority or ( aut_link_from_num=authperso_authorities.id_authperso_authority ))
		WHERE ((aut_link.aut_link_to > 1000))";
	}
	
	protected function add_isbd_s_from_query($object_id, $infos, $query) {
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)) {
			$order_fields = 1;
			while($row = pmb_mysql_fetch_object($result)){
				if(empty($object_id) || ($object_id != $row->subst_for_indexation)) {
					$object_id = $row->subst_for_indexation;
					$order_fields = 1;
				}
				$entity_isbd = $this->get_entity_isbd($infos["class_name"], $row->id_aut_for_isbd);
				$this->add_isbd_ask($object_id, $entity_isbd, $infos, $order_fields);
				$order_fields++;
			}
		}
	}
	
	protected function get_query_index_concept($entity_type) {
		return "SELECT num_object, num_concept, order_concept FROM index_concept WHERE type_object = ".$entity_type." ORDER BY num_object, order_concept";
	}
	
	public function index_concept_get_concepts_labels_from_entity($infos, $entity_type) {
		$query = $this->get_query_index_concept($entity_type);
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			$values = array();
			$object_id = 0;
			while ($row = pmb_mysql_fetch_object($result)){
				if(empty($object_id) || ($object_id != $row->num_object)) {
					if(!empty($values)) {
						$this->add_callable_data_tab_insert($row->num_object, $infos, $values);
					}
					$object_id = $row->num_object;
					$values = array();
				}
				$values[] = index_concept::get_concept_from_id($row->num_concept, 'label');
			}
			if(!empty($values)) {
				$this->add_callable_data_tab_insert($object_id, $infos, $values);
			}
		}
	}
	
	public function index_concept_get_concepts_altlabels_from_entity($infos, $entity_type) {
		$query = $this->get_query_index_concept($entity_type);
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			$values = array();
			$object_id = 0;
			while ($row = pmb_mysql_fetch_object($result)){
				if(empty($object_id) || ($object_id != $row->num_object)) {
					if(!empty($values)) {
						$this->add_callable_data_tab_insert($row->num_object, $infos, $values);
					}
					$object_id = $row->num_object;
					$values = array();
				}
				$values[] = index_concept::get_concept_from_id($row->num_concept, 'altlabel');
			}
			if(!empty($values)) {
				$this->add_callable_data_tab_insert($object_id, $infos, $values);
			}
		}
	}
	
	public function index_concept_get_concepts_hiddenlabels_from_entity($infos, $entity_type) {
		$query = $this->get_query_index_concept($entity_type);
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			$values = array();
			$object_id = 0;
			while ($row = pmb_mysql_fetch_object($result)){
				if(empty($object_id) || ($object_id != $row->num_object)) {
					if(!empty($values)) {
						$this->add_callable_data_tab_insert($row->num_object, $infos, $values);
					}
					$object_id = $row->num_object;
					$values = array();
				}
				$values[] = index_concept::get_concept_from_id($row->num_concept, 'hiddenlabel');
			}
			if(!empty($values)) {
				$this->add_callable_data_tab_insert($object_id, $infos, $values);
			}
		}
	}
	
// 	public function index_concept_get_generic_concepts_labels_from_entity($infos, $entity_type) {
// 	}
	
// 	public function index_concept_get_specific_concepts_labels_from_entity($infos, $entity_type) {
// 	}
	
	protected function maj_callable($object_id, $data) {
		if(method_exists($this, $data['class_name'].'_'.$data['method'])) {
			$callback_parameters = array($data);
			if (!empty($data['parameters'])) {
				$callback_parameters = array_merge($callback_parameters, explode(',', $data['parameters']));
			}
			call_user_func_array(array($this, $data['class_name'].'_'.$data['method']), $callback_parameters);
		} else {
			$query = "SELECT ".$this->reference_key." FROM ".$this->reference_table." ORDER BY ".$this->reference_key;
			$result = pmb_mysql_query($query);
			while ($row = pmb_mysql_fetch_object($result)) {
				parent::maj_callable($row->{$this->reference_key}, $data);
			}
		}
	}
	
	protected function push_elements($tab_insert, $tab_field_insert){
		
	}
	
	protected function import_bdd_sql_file($prefix) {
		if(file_exists($this->directory_files.$prefix.'_global_index.sql')) {
			$handle = fopen($this->directory_files.$prefix.'_global_index.sql', 'r');
			if ($handle) {
				while (!feof($handle)) {
					$query = "";
					while ( (substr($query, strlen($query)-1, 1) != "\n") && (!feof($handle)) ) {
						$query.= fgets($handle,4096);
					}
					$query = rtrim($query);
					if ($query != "") {
						pmb_mysql_query($query);
					}
				}
				/*On ferme le fichier*/
				fclose($handle);
				unlink($this->directory_files.$prefix.'_global_index.sql');
			}
		}
	}
	
	protected function maj_bdd_fields_global_index() {
		$this->mode = '';
		if(file_exists($this->directory_files.$this->fields_prefix.'_global_index.sql')) {
			unlink($this->directory_files.$this->fields_prefix.'_global_index.sql');
		}
		
		$directory_files = opendir($this->directory_files);
		while($file = readdir($directory_files)){
			if($file != "." && $file !=".." && $file !="CVS" && $file !=".svn" && is_file($this->directory_files.$file) && strpos($file, 'indexation_'.LOCATION) !== false) {
				$handle = fopen($this->directory_files.$file, 'r');
				if ($handle) {
					$file_infos = str_replace("indexation_".LOCATION."_", '', $file);
					$file_infos = substr($file_infos, 0, strpos($file_infos, '.'));
					$exploded_infos = explode('_', $file_infos);
					$infos = array(
							'champ' => $exploded_infos[0],
							'ss_champ' => $exploded_infos[1],
							'pond' => (!empty($exploded_infos[2]) ? $exploded_infos[2] : 0)
					);
					$lang = '';
					if(!empty($exploded_infos[3])) {
						$lang .= $exploded_infos[3];
						if(!empty($exploded_infos[4])) {
							$lang .= '_'.$exploded_infos[4];
						}
					}
					while (!feof($handle)) {
						/*On lit la ligne courante*/
						$buffer = fgets($handle);
						/*On l'affiche*/
						$entity = encoding_normalize::json_decode($buffer, true);
						if(!empty($entity)) {
							$this->add_tab_field_insert($entity[0], $infos, $entity[1], stripslashes($entity[2]), $lang, $entity[3]);
						}
						
						if(count($this->tab_field_insert) > 5000) {
							$this->save_elements($this->tab_insert, $this->tab_field_insert);
							$this->tab_insert = array();
							$this->tab_field_insert = array();
						}
					}
					$this->save_elements($this->tab_insert, $this->tab_field_insert);
					$this->tab_insert = array();
					$this->tab_field_insert = array();
					/*On ferme le fichier*/
					fclose($handle);
				}
			}
		}
// 		$this->import_bdd_sql_file($this->fields_prefix);
	}
	
	protected function maj_bdd_words_global_index() {
		if(file_exists($this->directory_files.$this->words_prefix.'_global_index.sql')) {
			unlink($this->directory_files.$this->words_prefix.'_global_index.sql');
		}
		
		$directory_files = opendir($this->directory_files);
		while($file = readdir($directory_files)){
			if($file != "." && $file !=".." && $file !="CVS" && $file !=".svn" && is_file($this->directory_files.$file) && strpos($file, 'indexation_'.LOCATION) !== false) {
				$handle = fopen($this->directory_files.$file, 'r');
				if ($handle) {
					$file_infos = str_replace("indexation_".LOCATION."_", '', $file);
					$file_infos = substr($file_infos, 0, strpos($file_infos, '.'));
					$exploded_infos = explode('_', $file_infos);
					$infos = array(
							'champ' => $exploded_infos[0],
							'ss_champ' => $exploded_infos[1],
							'pond' => (!empty($exploded_infos[2]) ? $exploded_infos[2] : 0)
					);
					while (!feof($handle)) {
						/*On lit la ligne courante*/
						$buffer = fgets($handle);
						/*On l'affiche*/
						$entity = encoding_normalize::json_decode($buffer, true);
						if(!empty($entity)) {
							$this->add_data_tab_insert($entity[0], $infos, stripslashes($entity[2]), $entity[1]/*, $keep_empty=false*/);
						}
						
						if(count($this->tab_insert) > 100000) {
							$this->save_elements($this->tab_insert, $this->tab_field_insert);
							$this->tab_insert = array();
							$this->tab_field_insert = array();
						}
					}
					$this->save_elements($this->tab_insert, $this->tab_field_insert);
					$this->tab_insert = array();
					$this->tab_field_insert = array();
					/*On ferme le fichier*/
					fclose($handle);
					unlink($this->directory_files.$file);
				}
			}
		}
// 		$this->import_bdd_sql_file($this->words_prefix);
	}
	
	public function maj_bdd_from_files() {
		$this->mode = '';
		
		$uniqId = PHP_log::prepare_time($this->get_label().' : maj_bdd_fields_global_index', 'indexation');
		$this->maj_bdd_fields_global_index();
		PHP_log::register($uniqId);
		
		$uniqId = PHP_log::prepare_time($this->get_label().' : maj_bdd_words_global_index', 'indexation');
		$this->maj_bdd_words_global_index();
		PHP_log::register($uniqId);
	}
	
	public function get_label() {
		return '';
	}
	
	public function get_directory_files() {
		return $this->directory_files;
	}
}