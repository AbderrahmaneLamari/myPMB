<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_breadcrumb.class.php,v 1.12 2022/01/19 14:33:32 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_view_breadcrumb extends cms_module_common_view_django{

	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "<span class='breadcrumb'>		
	>>&nbsp;<a href='{{home.link}}'>{{home.title}}</a>&nbsp;
{% for section in sections %}
	>&nbsp;<span class='elem'><a href='{{section.link}}'>{{section.title}}</a></span>&nbsp;
{% endfor %}
</span>
";
	}
	
	
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label>".$this->format_text($this->msg['cms_module_common_view_breadcrumb_build_section_link'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.= $this->get_constructor_link_form("section");
		$form.="
			</div>
		</div>";
		$form.= parent::get_form();
		return $form;
	}
	
	public function save_form(){
		$this->save_constructor_link_form("section");
		return parent::save_form();
	}
	
	public function render($datas){	
		global $opac_url_base;
		$render_datas = array();
		$render_datas['sections'] = array();
		$render_datas['home'] = array(
			'title' => $this->msg['home'],
			'link' => $opac_url_base
		);
		$links = [
		    "article" => $this->get_constructed_link("article", "!!id!!"),
		    "section" => $this->get_constructed_link("section", "!!id!!")
		];
		
		if($datas['article']){
			$render_datas['article'] = $datas['article'];
		}
		if(is_array($datas['sections']) && count($datas['sections'])) { 
			foreach($datas['sections'] as $section){
				$cms_section = cms_provider::get_instance("section",$section);
				$infos= $cms_section->format_datas($links);
				$render_datas['sections'][]=$infos;
			}
		}
		//on rappelle le tout...
		return parent::render($render_datas);
	}
	
	public function get_format_data_structure(){
		//dans ce cas l�, c'est assez simple, c'est la vue qui va chercher les donn�es...
		$format = array();
		$format[] =	array(
			'var' => 'home',
			'desc' => "",
			'children' => array(
				array(
					'var' => "home.title",
					'desc' => $this->msg['cms_module_common_view_home_title_desc'],
				),
				array(
					'var' => "home.link",
					'desc' => $this->msg['cms_module_common_view_home_link_desc'],
				)
			)
		);
		$sections = array(
			'var' => "sections",
			'desc' => $this->msg['cms_module_common_view_section_desc'],
			'children' => $this->prefix_var_tree(cms_section::get_format_data_structure(false,false),"sections[i]")
		);
		$sections['children'][] = array(
			'var' => "sections[i].link",
			'desc'=> $this->msg['cms_module_common_view_section_link_desc']
		);
		$format[]=$sections;
		$article = array(
			'var' => "article",
			'desc' => $this->msg['cms_module_common_view_article_desc'],
			'children' => $this->prefix_var_tree(cms_article::get_format_data_structure(),"article")
		);
		$format[]=$article;
		$format = array_merge($format,parent::get_format_data_structure());
		return $format;
	}
}