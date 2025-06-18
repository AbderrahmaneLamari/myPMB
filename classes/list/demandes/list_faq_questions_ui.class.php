<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_faq_questions_ui.class.php,v 1.8.2.1 2021/09/21 16:43:41 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/faq_question.class.php");
require_once($class_path."/faq_themes.class.php");
require_once($class_path."/faq_types.class.php");

class list_faq_questions_ui extends list_ui {
	
	protected $themes;
	
	protected $types;
	
	protected function _get_query_base() {
		$query = 'select id_faq_question
				from faq_questions
				join faq_themes on faq_question_num_theme = id_theme 
				join faq_types on faq_question_num_type = id_type 	
				';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new faq_question($row->id_faq_question);
	}
		
	protected function get_form_title() {
		global $msg;
		
		return $msg['faq_filter_form_title'];
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'theme' => 'faq_question_theme_label',
						'type' => 'faq_question_type_label',
						'status' => 'faq_question_statut_label'
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
                'theme' => 0,
                'type' => 0,
				'status' => 0,
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('theme');
		$this->add_selected_filter('type');
		$this->add_selected_filter('status');
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('faq_question_question_date', 'desc');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'theme' => 'faq_question_theme_label',
						'type' => 'faq_question_type_label',
						'status' => 'faq_question_statut',
						'question' => 'faq_question_question',
						'answer' => 'faq_question_answer',
				)
		);
		
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column('theme');
		$this->add_column('type');
		$this->add_column('status');
		$this->add_column('question');
		$this->add_column('answer');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'export_icons', false);
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		global $id_type, $id_theme, $id_statut;
		
		if(isset($id_type)) {
			$this->filters['type'] = intval($id_type);
		}
		if(isset($id_theme)) {
			$this->filters['theme'] = intval($id_theme);
		}
		if(isset($id_statut)) {
			$this->filters['status'] = intval($id_statut);
		}
		parent::set_filters_from_form();
	}
	
	protected function get_search_filter_theme() {
		return $this->get_themes()->getListSelector($this->filters['theme'],'',true);
	}
	
	protected function get_search_filter_type() {
		return $this->get_types()->getListSelector($this->filters['type'],'',true);
	}
	
	protected function get_search_filter_status() {
		global $msg;
		
		return "
			<select name='id_statut' >
				<option value='0'".($this->filters['status'] == 0 ? " selected='selected'" : "").">".$msg['faq_question_statut_visible_0']."</option>
				<option value='1'".($this->filters['status'] == 1 ? " selected='selected'" : "").">".$msg['faq_question_statut_visible_1']."</option>
				<option value='2'".($this->filters['status'] == 2 ? " selected='selected'" : "").">".$msg['faq_question_statut_visible_2']."</option>
				<option value='3'".($this->filters['status'] == 3 ? " selected='selected'" : "").">".$msg['faq_question_statut_visible_3']."</option>
			</select>";
	}
	
	
	/**
	 * Affichage du formulaire de recherche
	 */
	public function get_display_search_form() {
		$this->is_displayed_add_filters_block = false;
		$display_search_form = parent::get_display_search_form();
		return $display_search_form;
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['theme']) {
			$filters [] = 'faq_question_num_theme = "'.$this->filters['theme'].'"';
		}
		if($this->filters['type']) {
			$filters [] = 'faq_question_num_type = "'.$this->filters['type'].'"';
		}
		if($this->filters['status']) {
			$filters [] = 'faq_question_statut = "'.$this->filters['status'].'"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);		
		}
		return $filter_query;
	}
	
	protected function _get_object_property_theme($object) {
		return $this->get_themes()->getLabel($object->num_theme);
	}
	
	protected function _get_object_property_type($object) {
		return $this->get_types()->getLabel($object->num_type);
	}
	
	protected function _get_object_property_status($object) {
		global $msg;
		return $msg['faq_question_statut_visible_'.$object->statut];
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch($property) {
			case 'question':
				$question = strip_tags($object->question);
				if(strlen($question) > 200){
					$question = substr($question,0,200)."[...]";
				}
				$content .= $question;
				break;
			case 'answer':
				$answer = strip_tags($object->answer);
				if(strlen($answer) > 200){
					$answer = substr($answer,0,200)."[...]";
				}
				$content .= $answer;
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_cell($object, $property) {
		$attributes = array();
		$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->id."\"";
		switch($property) {
			case 'question':
				$question = strip_tags($object->question);
				if(strlen($question) > 200){
					$attributes['title'] = $question;
				}
				break;
			case 'answer':
				$answer = strip_tags($object->answer);
				if(strlen($answer) > 200){
					$attributes['title'] = $answer;
				}
				break;
			default:
				
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	public function get_error_message_empty_list() {
		global $msg, $charset;
		return htmlentities($msg['faq_no_question'],ENT_QUOTES,$charset);
	}
	
	public function get_themes() {
		if(!isset($this->themes)) {
			$this->themes = new faq_themes("faq_themes","id_theme","libelle_theme");
		}
		return $this->themes;
	}
	
	public function get_types() {
		if(!isset($this->types)) {
			$this->types = new faq_types("faq_types", "id_type", "libelle_type");
		}
		return $this->types;
	}
	
	protected function get_button_add() {
		global $msg;
		
		return $this->get_button('new', $msg['faq_add_new_question']);
	}
	
	protected function get_display_others_actions() {
		global $pmb_opac_url, $msg;
		return "
			<div id='list_ui_others_actions' class='list_ui_others_actions ".$this->objects_type."_others_actions'>
				<span class='right list_ui_other_action_permalink ".$this->objects_type."_other_action_permalink'>
					<a href='".$pmb_opac_url."index.php?lvl=faq' target='_blank'>".$msg['opac_faq_link']."</a>
				</span>
			</div>";
	}
	
	public static function get_ajax_controller_url_base() {
		global $base_path, $current_module;
		return $base_path.'/ajax.php?module='.$current_module.'&categ=dmde&sub=faq_questions';
	}
}