<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_accounting_suppliers_ui.class.php,v 1.1.2.3 2021/07/01 08:13:55 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_accounting_suppliers_ui extends list_accounting_ui {
	
	protected function _get_query_base() {
		$this->set_filter_from_form('user_input');
		$this->set_filter_from_form('entite', 'integer');
		if(!$this->filters['user_input']) {
			$query = 'SELECT id_entite as id, entites.* FROM entites';
		} else {
			$restrict = 'num_bibli=0 ';
			if($this->filters['entite']) {
				$restrict = "num_bibli in (0, ".$this->filters['entite'].") ";
			}
			$members = $this->get_analyse_query()->get_query_members("entites","raison_sociale","index_entite","id_entite",$restrict);
			$query = "SELECT id_entite as id, entites.*, ".$members["select"]." as pert from entites";
		}
		return $query;
	}
	
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'type_entite' => TYP_ENT_FOU,
				'user_input' => '',
				'entite' => 0,
		);
		list_ui::init_filters($filters);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'global_search' => 'global_search',
						'entity' => 'acquisition_coord_lib',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('global_search');
		$this->add_selected_filter('entity');
	}
	
	protected function init_default_applied_sort() {
		$this->add_applied_sort('raison_sociale');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('raison_sociale', 'text', array('italic' => true));
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'raison_sociale' => 'acquisition_raison_soc',
						'condition' => 'acquisition_cond_fourn',
						'hist_rel' => 'acquisition_hist_rel_fou'
				)
		);
	}
	
	protected function init_default_columns() {
		$this->add_column('raison_sociale');
		$this->add_column('condition');
		$this->add_column('hist_rel');
	}
	
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		$filters[] = 'type_entite = "'.$this->filters['type_entite'].'"';
		if($this->filters['user_input']) {
			$restrict = 'num_bibli=0 ';
			if($this->filters['entite']) {
				$restrict = "num_bibli in (0, ".$this->filters['entite'].") ";
			}
			$members = $this->get_analyse_query()->get_query_members("entites","raison_sociale","index_entite","id_entite",$restrict);
			$filters[] = "(".$members["where"].")";
		}
		if($this->filters['entite']) {
			$filters[] = "num_bibli IN (0, ".$this->filters['entite'].")";
		} else {
			$filters[] = "num_bibli=0";
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function _get_query_order() {
		return list_ui::_get_query_order();
	}
	
	protected function get_button_add() {
		global $msg;
		
		return "<input class='bouton' type='button' value='".$msg['acquisition_ajout_fourn']."' onClick=\"document.location='".static::get_controller_url_base()."&action=add';\" />";
	}
	
	protected function get_search_filter_entity() {
		$sel_all = TRUE;
		$sel_attr = [
				'class'=> 'saisie-50em',
				'id'=> $this->objects_type.'_entite',
				'name'=> $this->objects_type.'_entite',
				'onchange'=> 'submit();',
		];
		return entites::get_hmtl_select_etablissements(SESSuserid, $this->filters['entite'], $sel_all, $sel_attr);
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'raison_sociale':
				$content .= $object->raison_sociale;
				if(!$object->num_bibli) {
					$content .= $msg['acquisition_coord_all_in_parenthesis'];
				}
				break;
			case 'condition':
				$content .= "<a href='".static::get_controller_url_base()."&action=cond&id=".$object->id_entite."' >".$msg['acquisition_cond_fourn']."</a>";
				break;
			case 'hist_rel':
				$content .= "<a href='".static::get_controller_url_base()."&action=histrel&id=".$object->id_entite."' >".$msg['acquisition_hist_rel_fou']."</a>";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
		switch ($property) {
			case 'raison_sociale':
				$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=modif&id=".$object->id_entite."\"";
				break;
			default:
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	public function get_display_header_list() {
		return '';
	}
	
	public function get_type_acte() {
		return 0;
	}
	
	public function get_initial_name() {
		return 'fou';
	}
	
	public function get_error_message_empty_list() {
		global $msg, $charset;
		return htmlentities(str_replace('!!fou_cle!!', stripslashes($this->filters['user_input']), $msg['acquisition_fou_rech_error']), ENT_QUOTES, $charset);
	}
}