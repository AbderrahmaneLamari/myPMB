<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_sectionslist_view_carousel_responsive.class.php,v 1.6 2022/10/20 13:18:21 jparis Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_sectionslist_view_carousel_responsive extends cms_module_common_view_carousel_responsive{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "
<ul id='carousel_{{id}}'>
	{% for record in records %}
		<li class='{{id}}_item'>
			<a href='{{record.link}}' alt='{{record.title}}' title='{{record.title}}'>
				<img src='{% if record.logo.exists %}{{record.logo.vign}}{% else %}{{no_image_url}}{% endif %}'/>
				<br />
			</a>
		</li>
	{% endfor %}
</ul>
";
	}
	
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_sectionslist_view_link'>".$this->format_text($this->msg['cms_module_sectionslist_view_link'])."</label>
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
	
	public function render($ids){
		$datas = array();
		$links = [
		    "article" => $this->get_constructed_link("article", "!!id!!"),
		    "section" => $this->get_constructed_link("section", "!!id!!")
		];
		$local_ids = $ids;
		if (isset($ids["sections"])) {
		    $local_ids = $ids["sections"];
		}
		for($i=0 ; $i<count($local_ids) ; $i++){
		    $section = new cms_section($local_ids[$i]);
			$infos = $section->format_datas($links);
			$datas[]= $infos;
		}
		return parent::render(array('records' => $datas));
	}
	
	public function get_format_data_structure(){
		$datas = cms_section::get_format_data_structure(false,false);
		$datas[] = array(
			'var' => "link",
			'desc'=> $this->msg['cms_module_sectionslist_view_carousel_link_desc']
		);
		
		$format_datas = array(
			array(
				'var' => "records",
				'desc' => $this->msg['cms_module_carousel_view_carousel_records_desc'],
				'children' => $this->prefix_var_tree($datas,"records[i]")
			)
		);
		$format_datas = array_merge($format_datas,parent::get_format_data_structure());
		return $format_datas;
	}
}