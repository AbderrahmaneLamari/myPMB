<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_datasource_section.class.php,v 1.17.6.1 2023/04/25 10:12:20 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_datasource_section extends cms_module_common_datasource{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	 */
	public function get_available_selectors(){
		return array(
			"cms_module_common_selector_section",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_global_var",
			"cms_module_common_selector_generic_parent_section",
			"cms_module_common_selector_type_section",
			"cms_module_common_selector_type_section_generic",
		    "cms_module_common_selector_section_by_cp_and_search_segment",
		    "cms_module_common_selector_section_by_cp_and_search_universe",
		    "cms_module_common_selector_section_by_value_cp"
		);
	}
	
	public function get_form(){
		global $msg;
		
		if(!isset($this->parameters['load_articles_data'])) $this->parameters['load_articles_data'] = 1;
	
		$form = parent::get_form();
		$form .= "
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_common_datasource_section_load_articles_data'>".$this->format_text($this->msg['cms_module_common_datasource_section_load_articles_data'])."</label>
			</div>
			<div class='colonne-suite'>
				".$msg[39]." <input type='radio' name='cms_module_common_datasource_section_load_articles_data' value='0' ".(!$this->parameters['load_articles_data'] ? "checked='checked'" : "")." />
				".$msg[40]." <input type='radio' name='cms_module_common_datasource_section_load_articles_data' value='1' ".($this->parameters['load_articles_data'] ? "checked='checked'" : "")." />
			</div>
		</div>";
		
		return $form;
	}
	
	/*
	 * Sauvegarde du formulaire, revient � remplir la propri�t� parameters et appeler la m�thode parente...
	 */
	public function save_form(){
		global $selector_choice;
		global $cms_module_common_datasource_section_load_articles_data;
		
		$this->parameters= array();
		$this->parameters['selector'] = $selector_choice;
		$this->parameters['load_articles_data'] = (int) $cms_module_common_datasource_section_load_articles_data;
		return parent::save_form();
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	 */
	public function get_datas(){
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		$selector = $this->get_selected_selector();
		if($selector){
			$section_id = $selector->get_value();
			$section_ids = $this->filter_datas("sections",array($section_id));
			if(isset($section_ids[0]) && $section_ids[0]){
			    $section = new cms_section($section_ids[0]);
			    $links = [
			        "article" => $this->get_constructed_link("article", "!!id!!"),
			        "section" => $this->get_constructed_link("section", "!!id!!")
			    ];
				if(!isset($this->parameters['load_articles_data'])) $this->parameters['load_articles_data'] = 1;
				$return = $section->format_datas($links);
				return $return;
			}
		}
		return false;
	}
	
	public function get_format_data_structure(){
		return cms_section::get_format_data_structure(true, true, true, true);
	}
}