<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_loans_archives_ui.class.php,v 1.5.2.1 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/pret_archive.class.php");

class list_loans_archives_ui extends list_loans_ui {
	
	protected function _get_query_base() {
		$query = 'select pret_archive.*
			FROM (((pret_archive LEFT JOIN notices AS notices_m ON arc_expl_notice = notices_m.notice_id )
		 		LEFT JOIN bulletins ON arc_expl_bulletin = bulletins.bulletin_id)
				LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id)
				JOIN empr ON empr.id_empr = pret_archive.arc_id_empr
				JOIN docs_type ON arc_expl_typdoc = idtyp_doc
				';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new pret_archive($row->arc_id);
	}
		
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		
		parent::init_available_filters();
		//Il y en aura sûrement des spécifiques aux archives de prêts
	}
		
	protected function init_default_applied_sort() {
		$this->add_applied_sort('arc_fin');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		global $msg;
		parent::init_available_columns();
		$main_fields =
		array(
				'arc_debut' => 'circ_date_emprunt',
				'arc_fin' => 'circ_date_retour',
				'arc_empr_cp' => 'acquisition_cp',
				'arc_empr_ville' => 'ville_empr',
				'arc_empr_prof' => '74',
				'arc_empr_year' => 'year_empr',
				'arc_empr_sexe' => '125',
				'arc_expl_cote' => '4016',
				'arc_empr_categ' => 'categ_empr',
				'arc_empr_codestat' => 'codestat_empr',
				'arc_empr_statut' => 'statut_empr',
				'arc_empr_location' => 'localisation_sort',
				'arc_expl_typdoc' => '294',
				'arc_expl_statut' => '',
				'arc_expl_location' => ''
		);
		foreach ($main_fields as $key=>$main_field) {
			$main_fields[$key] = $msg[$main_field]." <sup>(arc)</sup>";
		}
		$this->available_columns['main_fields'] = array_merge($this->available_columns['main_fields'], $main_fields);
		//Il y en aura sûrement des spécifiques aux archives de prêts
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
	
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'pret_retour_empr' :
					$order .= 'arc_fin, empr_nom, empr_prenom';
					break;
				case 'arc_expl_cote':
				case 'arc_debut':
				case 'arc_fin':
					$order .= $sort_by;
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}
	}
			
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['empr_location_id']) {
			$filters [] = 'arc_empr_location = "'.$this->filters['empr_location_id'].'"';
		}
		if($this->filters['docs_location_id']) {
			$filters [] = 'arc_expl_location = "'.$this->filters['docs_location_id'].'"';
		}
		if($this->filters['empr_categ_filter']) {
			$filters [] = 'arc_empr_categ = "'.$this->filters['empr_categ_filter'].'"';
		}
		if($this->filters['empr_codestat_filter']) {
			$filters [] = 'arc_empr_codestat = "'.$this->filters['empr_codestat_filter'].'"';
		}
		if($this->filters['pret_date_start']) {
			$filters [] = 'arc_debut >= "'.$this->filters['pret_date_start'].'"';
		}
		if($this->filters['pret_date_end']) {
			$filters [] = 'arc_debut < "'.$this->filters['pret_date_end'].'"';
		}
		if($this->filters['pret_retour_start']) {
			$filters [] = 'arc_fin >= "'.$this->filters['pret_retour_start'].'"';
		}
		if($this->filters['pret_retour_end']) {
			$filters [] = 'arc_fin < "'.$this->filters['pret_retour_end'].'"';
		}
		if($this->filters['short_loan_flag']) {
			$filters [] = 'arc_short_loan_flag = "'.$this->filters['short_loan_flag'].'"';
		}
		if ($this->filters['pnb_flag']) {
		    $filters [] = 'arc_pnb_flag = "'.$this->filters['pnb_flag'].'"';
		}
		if(count($filters)) {
		    $filter_query .= $this->_get_query_join_filters();
			$filter_query .= ' where '.implode(' and ', $filters);		
		}
		return $filter_query;
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		$method_name = 'get_'.$property;
		switch($property) {
			case 'arc_debut':
				$content .= formatdate($object->get_arc_debut());
				break;
			case 'arc_fin':
				$content .= formatdate($object->get_arc_fin());
				break;
			case 'arc_empr_categ':
				
				break;
			case 'arc_empr_codestat':
				
				break;
			case 'arc_empr_statut':
				
				break;
			case 'arc_empr_location':
				$docs_location = new docs_location($object->{$method_name}());
				$content .= $docs_location->libelle;
				break;
			case 'arc_expl_typdoc':
				$docs_type = new docs_type($object->{$method_name}());
				$content .= $docs_type->libelle;
				break;
			case 'arc_expl_statut':
				$docs_statut = new docs_statut($object->{$method_name}());
				$content .= $docs_statut->libelle;
				break;
			case 'arc_expl_location':
				$docs_location = new docs_location($object->{$method_name}());
				$content .= $docs_location->libelle;
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
}