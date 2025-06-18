<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_records_indexer.class.php,v 1.10 2021/06/11 09:27:48 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;

require_once "$class_path/sphinx/sphinx_indexer.class.php";

class sphinx_records_indexer extends sphinx_indexer {
	
	public function __construct() {
		global $include_path;
		$this->object_id = 'id_notice';
		$this->object_key = 'notice_id';
		$this->object_index_table = 'notices_fields_global_index';
		$this->object_table = 'notices';
		parent::__construct();
		$this->filters = ['multi' => ['statut', 'typdoc'], 'bigint' => ['date_parution']];
		$this->setChampBaseFilepath($include_path."/indexation/notices/champs_base.xml");
	}
	
	protected function addSpecificsFilters($id, $filters = array()) {
		$filters = parent::addSpecificsFilters($id, $filters);
		$result = pmb_mysql_query("SELECT typdoc, statut, TIMESTAMPDIFF(second, FROM_UNIXTIME(0), date_parution) AS date_parution FROM notices WHERE notice_id = $id");
		$row = pmb_mysql_fetch_object($result);
		$filters['multi']['statut'] = $row->statut;
		$filters['multi']['typdoc'] = $row->typdoc;
		
	    $filters['bigint']['date_parution'] = $row->date_parution;
	    if (empty($filters['bigint']['date_parution'])) {
		    $filters['bigint']['date_parution'] = -9999999999999;
		}
		
		return $filters;
	}
}