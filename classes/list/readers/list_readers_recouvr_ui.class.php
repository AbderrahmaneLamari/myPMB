<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_readers_recouvr_ui.class.php,v 1.10 2021/04/16 14:47:30 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/comptes.class.php");

class list_readers_recouvr_ui extends list_readers_ui {
	
	protected function _get_query_base() {
	    $query = "SELECT empr.*, round(sum(id_expl!=0)/2) as nb_ouvrages, sum(montant) as somme ,location_libelle 
			FROM recouvrements
			JOIN empr ON id_empr=empr_id
			JOIN docs_location ON empr_location=idlocation";
	    return $query;
	}
	
	protected function _get_query_order() {
		return ' GROUP BY empr.id_empr '.parent::_get_query_order();
	}
	
	protected function get_object_instance($row) {
		return null;
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		parent::init_available_columns();
		$this->available_columns['main_fields']['nb_ouvrages'] = 'relance_recouvrement_nb_ouvrages';
		$this->available_columns['main_fields']['somme_totale'] = 'relance_recouvrement_somme_totale';
	}
	
	protected function init_default_columns() {
		global $pmb_lecteurs_localises;
		
		$this->add_column('cb', 'relance_recouvrement_cb');
		$this->add_column('empr_name', 'relance_recouvrement_name');
		if($pmb_lecteurs_localises) {
			$this->add_column('location', 'empr_location');
		}
		$this->add_column('nb_ouvrages');
		$this->add_column('somme_totale');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
		$this->set_setting_column('nb_ouvrages', 'align', 'center');
		$this->set_setting_column('somme_totale', 'align', 'right');
	}
	
	/**
	 * Affichage d'une colonne
	 * @param object $object
	 * @param string $property
	 */
	protected function get_display_cell($object, $property) {
		$attributes = array(
				'onclick' => "document.location=\"".$this->get_controller_url_base()."&act=recouvr_reader&id_empr=".$object->id_empr."\";"
		);
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'cb':
				$content .= $object->empr_cb;
				break;
			case 'empr_name':
				$content .= $object->empr_nom." ".$object->empr_prenom;
				break;
			case 'location':
				$content .= $object->location_libelle;
				break;
			case 'somme_totale':
				$content .= comptes::format_simple($object->somme);
				break;
			default:
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_selection_actions() {
		if(!isset($this->selection_actions)) {
			$this->selection_actions = array();
		}
		return $this->selection_actions;
	}
}