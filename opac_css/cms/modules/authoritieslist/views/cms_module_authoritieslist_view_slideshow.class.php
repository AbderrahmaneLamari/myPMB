<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_authoritieslist_view_slideshow.class.php,v 1.2 2019/12/16 08:06:02 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_authoritieslist_view_slideshow extends cms_module_common_view_slideshow {
	
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	public function get_form(){
		$form="
		<div class='row'>
			<div class='colonne3'>
				<label for='cms_module_recordslist_view_link'>".$this->format_text($this->msg['cms_module_recordslist_view_link'])."</label>
			</div>
			<div class='colonne-suite'>";
		$form.= $this->get_constructor_link_form("notice");
		$form.="
			</div>
		</div>";
		$form.= parent::get_form();
		return $form;
	}
	
	public function save_form(){
		$this->save_constructor_link_form("notice");
		return parent::save_form();
	}
	
	public function render($authorities){
		$datas = array();
		global $opac_url_base, $add_to_cart_link;
	
		$datas = array(
			'title' => $authorities['title'],
		    'authorities' => $authorities,
			'add_to_cart_link' => $add_to_cart_link
		);
		return parent::render($datas);
	}
	
	public function get_format_data_structure(){
		$datas = new cms_module_carousel_datasource_notices();
		$format_datas = $datas->get_format_data_structure();
		$format_datas[0]['children'][] = array(
				'var' => "records[i].header",
				'desc'=> $this->msg['cms_module_common_view_record_header_desc']
		);
		$format_datas[0]['children'][] = array(
				'var' => "records[i].content",
				'desc' => $this->msg['cms_module_common_view_slideshow_record_content_desc']
		);
		$format_datas[] = array(
				'var' => "add_to_cart_link",
				'desc' => $this->msg['cms_module_recordslist_view_add_cart_link_desc']
		);
 		$format_datas = array_merge($format_datas,parent::get_format_data_structure());
		return $format_datas;
	}
}