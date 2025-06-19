<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_records.class.php,v 1.1.2.5 2023/06/13 13:53:44 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/indexation/indexation_entities.class.php");

//classe de calcul d'indexation des notices...
class indexation_records extends indexation_entities {
	
	public function __construct($xml_filepath, $table_prefix, $type = 0){
		$this->fields_prefix = 'notices_fields';
		$this->words_prefix = 'notices_mots';
		parent::__construct($xml_filepath, $table_prefix, $type);
	}
	
	protected function get_indexation_lang() {
		global $indexation_lang;
		return $indexation_lang;
	}
	
	protected function get_query_records_formations($nature=0) {
		$nature = intval($nature);
		return "SELECT notice_nomenclature_num_notice, id_formation
				FROM nomenclature_notices_nomenclatures
				JOIN nomenclature_formations ON nomenclature_formations.id_formation = nomenclature_notices_nomenclatures.notice_nomenclature_num_formation
				AND nomenclature_formations.formation_nature = ".$nature."
				ORDER BY notice_nomenclature_order, notice_nomenclature_label";
	}
	
	public function nomenclature_record_formations_get_instruments_index($infos, $property, $family) {
		$query = $this->get_query_records_formations();
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			while($row = pmb_mysql_fetch_object($result)){
				$instruments_index_data = nomenclature_record_formation::get_instance($row->id_formation)->get_instruments_index_data();
// 				$index_data = array();
// 				for($j=0 ; $j<count($instruments_index_data) ; $j++){
// 					$index = [];
// 					foreach($instruments_index_data[$j] as $info => $value){
// 						$index[$info] =$value;
// 					}
// 					$index_data[] =	 $index;
// 				}
				$return_data = [];
				foreach ($instruments_index_data as $instrument_index_data) {
					if (!empty($instrument_index_data[$property]) && $instrument_index_data["family"] == $family) {
						$return_data[] = $instrument_index_data[$property];
					}
				}
				$this->add_callable_data_tab_insert($row->notice_nomenclature_num_notice, $infos, $return_data);
// 				return $return_data;
			}
		}
	}
	
	public function record_display_get_locations_list($infos, $property) {
		//Localisations des exemplaires + documents numériques
		$query = "
			SELECT DISTINCT locations.location_libelle, locations.expl_notice FROM (
				SELECT DISTINCT location_libelle, expl_notice FROM exemplaires JOIN docs_location ON docs_location.idlocation = exemplaires.expl_location
				WHERE docs_location.location_visible_opac=1 AND expl_notice <> 0
				GROUP BY expl_notice
				UNION
				SELECT DISTINCT location_libelle, explnum_notice FROM explnum JOIN explnum_location ON explnum_location.num_explnum = explnum.explnum_id JOIN docs_location ON docs_location.idlocation = explnum_location.num_location 
				WHERE docs_location.location_visible_opac=1 AND explnum_notice <> 0
				GROUP BY explnum_notice
				) as locations
				GROUP BY locations.expl_notice
		";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)) {
				$this->add_callable_data_tab_insert($row->expl_notice, $infos, array($row->location_libelle));
			}
			pmb_mysql_free_result($result);
		}
	}

	public function record_display_get_lenders_list($infos, $property) {
		//Propriétaires des exemplaires + documents numériques
		$query = "
			SELECT DISTINCT lenders.lender_libelle, lenders.expl_notice FROM (
				SELECT DISTINCT lender_libelle, expl_notice FROM exemplaires JOIN lenders ON lenders.idlender = exemplaires.expl_owner
				WHERE expl_notice <> 0
				GROUP BY expl_notice
				UNION
				SELECT DISTINCT lender_libelle, explnum_notice FROM explnum JOIN explnum_lenders ON explnum_lenders.explnum_lender_num_explnum = explnum.explnum_id JOIN lenders ON lenders.idlender = explnum_lenders.explnum_lender_num_lender
				WHERE explnum_notice <> 0
				GROUP BY explnum_notice
				) as lenders
				GROUP BY lenders.expl_notice
		";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)) {
				$this->add_callable_data_tab_insert($row->expl_notice, $infos, array($row->lender_libelle));
			}
			pmb_mysql_free_result($result);
		}
	}
		
	public function nomenclature_record_formations_get_voices_index($infos, $property) {
		$query = $this->get_query_records_formations(1);
		$result = pmb_mysql_query($query);
		if(pmb_mysql_num_rows($result)){
			while($row = pmb_mysql_fetch_object($result)){
				$voices_index_data = nomenclature_record_formation::get_instance($row->id_formation)->get_voices_index_data();
// 				$index_data = array();
// 				for($j=0 ; $j<count($voices_index_data) ; $j++){
// 					$index = [];
// 					foreach($voices_index_data[$j] as $info => $value){
// 						$index[$info] =$value;
// 					}
// 					$index_data[] =	 $index;
// 				}
				$return_data = [];
				foreach ($voices_index_data as $voice_index_data) {
					if (!empty($voice_index_data[$property])) {
						$return_data[] = $voice_index_data[$property];
					}
				}
				$this->add_callable_data_tab_insert($row->notice_nomenclature_num_notice, $infos, $return_data);
			}
		}
	}
		
	protected function push_elements($tab_insert, $tab_field_insert){
		if($tab_insert && count($tab_insert)){
			$req_insert="insert into ".$this->table_prefix."_mots_global_index(id_notice,code_champ,code_ss_champ,num_word,pond,position,field_position) values ".implode(',',$tab_insert)." ON DUPLICATE KEY UPDATE num_word = num_word";
			file_put_contents($this->directory_files.$this->words_prefix.'_global_index.sql', $req_insert."\n", FILE_APPEND);
		}
		if($tab_field_insert && count($tab_field_insert)){
			//la table pour les recherche exacte
			$req_insert="insert into ".$this->table_prefix."_fields_global_index(id_notice,code_champ,code_ss_champ,ordre,value,lang,pond,authority_num) values ".implode(',',$tab_field_insert)." ON DUPLICATE KEY UPDATE value = value";
			file_put_contents($this->directory_files.$this->fields_prefix.'_global_index.sql', $req_insert."\n", FILE_APPEND);
		}
	}
	
	protected function save_elements($tab_insert, $tab_field_insert){
		if($tab_insert && count($tab_insert)){
			$req_insert="insert into ".$this->table_prefix."_mots_global_index(id_notice,code_champ,code_ss_champ,num_word,pond,position,field_position) values ".implode(',',$tab_insert)." ON DUPLICATE KEY UPDATE num_word = num_word";
			pmb_mysql_query($req_insert);
		}
		if($tab_field_insert && count($tab_field_insert)){
			//la table pour les recherche exacte
			$req_insert="insert into ".$this->table_prefix."_fields_global_index(id_notice,code_champ,code_ss_champ,ordre,value,lang,pond,authority_num) values ".implode(',',$tab_field_insert)." ON DUPLICATE KEY UPDATE value = value";
			pmb_mysql_query($req_insert);
		}
	}

	public function get_label() {
		global $msg;
		
		return $msg['nettoyage_reindex_notices'];
	}
}