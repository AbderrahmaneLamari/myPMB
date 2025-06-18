<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_authorities_indexer.class.php,v 1.11 2020/11/06 14:16:21 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;

require_once "$class_path/sphinx/sphinx_indexer.class.php";

class sphinx_authorities_indexer extends sphinx_indexer {
	protected $type;
	
	public function __construct() {
		$this->object_id = 'id_authority';
		$this->object_key = 'id_authority';
		$this->object_index_table = 'authorities_fields_global_index';
		$this->object_table = 'authorities';
		$this->filters = ['multi' => ['status']];
		parent::__construct();
	}
	
	public function fillIndex($object_id = 0) {
	    global $sphinx_indexes_prefix;
	    
		$this->parse_file();
		$object_id = (int) $object_id;
		$dbh = $this->getDBHandler();
		$separator = $this->getSeparator();
		$langs = $this->getAvailableLanguages();
		$nb_langs = count($langs);
		$imploded_langs = implode("','", $langs);
		
		// Remplissage des indexs...
		$and_clause = '';
		if (!empty($object_id)) {
		    $and_clause = "AND $this->object_key = $object_id";
		}
		$rqt = "SELECT $this->object_key FROM $this->object_table WHERE type_object = $this->type $and_clause ORDER BY 1";
		$res = pmb_mysql_query($rqt);
		if ($res) {
			pmb_mysql_query('set session group_concat_max_len = 16777216');
			if (empty($object_id)) {
			    print ProgressBar::start(pmb_mysql_num_rows($res), "Index $this->default_index");
			}
			while ($object = pmb_mysql_fetch_object($res)) {
			    $id = $object->{$this->object_key};
			    
				// Purge
				for ($i = 0; $i < $nb_langs; $i++) {
					foreach ($this->indexes as $index_name => $infos) {
					    $table = $sphinx_indexes_prefix . $index_name;
					    if (!empty($langs[$i])) {
					        $table .= '_' . $langs[$i];
					    }
					    $query = "DELETE FROM $table WHERE id = $id";
						if (!pmb_mysql_query($query, $dbh)) {
							print "$table : " . pmb_mysql_error($dbh) . "($query)\n";
						}
					}
				}
				
				// Construction de l'index
				$rqt = "SELECT code_champ, code_ss_champ, lang, group_concat(value SEPARATOR '$separator') AS value FROM $this->object_index_table WHERE $this->object_id = $id AND lang in ('$imploded_langs') GROUP BY code_champ, code_ss_champ, lang";
				$inserts = array();
				$res_notice = pmb_mysql_query($rqt);
				while ($champ = pmb_mysql_fetch_object($res_notice)) {
					if (in_array($champ->lang, $langs)) {
						$code_champ = str_pad($champ->code_champ, 3, "0", STR_PAD_LEFT);
						$code_ss_champ = str_pad($champ->code_ss_champ, 2, "0", STR_PAD_LEFT);
						$field = 'f_' . $code_champ . '_' . $code_ss_champ;
	
						if (!empty($this->insert_index[$field])) {
							$inserts[$this->insert_index[$field].($champ->lang ? '_'.$champ->lang : '')][$field] = addslashes(encoding_normalize::utf8_normalize($champ->value));
						}
					}
				}
				
				// Création de la requête d'insertion
				$inserts = $this->getSpecificsFiltersValues($id, $inserts);
				foreach ($inserts as $insert_table => $fields) {
					$keys = $values =  "";
					foreach ($fields as $key => $value) {
						if (!empty($keys)) {
							$keys .= ",";
							$values .= ",";
						}
						$keys .= $key;
						if (substr($key, 0, 2) !== "f_") {
						    $values .= $value;
						} else {
						    $values .= "'$value'";
						};
					}
					$table = $sphinx_indexes_prefix . $insert_table;
					$query = "INSERT INTO $table (id, $keys) values ($id, $values)";
					if (!pmb_mysql_query($query, $dbh)) {
						print "$table : " . pmb_mysql_error($dbh) . "($query)\n";
					}
				}
				if (empty($object_id)) {
				    print ProgressBar::next();
				}
			}
			if (empty($object_id)) {
			    print ProgressBar::finish();
			}
		}
	}
	
	protected function addSpecificsFilters($id, $filters = array()) {
		$filters = parent::addSpecificsFilters($id, $filters);
		return $filters;
	}
}