<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_metadatas_datasource_metadatas_record.class.php,v 1.10.4.2 2023/06/09 08:21:08 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

use Pmb\Thumbnail\Models\ThumbnailSourcesHandler;

class cms_module_metadatas_datasource_metadatas_record extends cms_module_metadatas_datasource_metadatas_generic{
	
	public function __construct($id=0){
		parent::__construct($id);
	}
	
	/*
	 * On d�fini les s�lecteurs utilisable pour cette source de donn�e
	*/
	public function get_available_selectors(){
		return array(
				"cms_module_common_selector_record",
				"cms_module_common_selector_record_permalink",
				"cms_module_common_selector_env_var",
				"cms_module_common_selector_type_article",
				"cms_module_common_selector_type_section",
				"cms_module_common_selector_type_article_generic",
				"cms_module_common_selector_type_section_generic"
		);
	}
	
	protected function get_record_content($notice_class) {
		global $opac_notices_format;
		global $opac_notice_affichage_class;
		global $opac_notices_format_django_directory;
		global $record_css_already_included;
		global $include_path;
		
		$content = '';
		if(isset($this->parameters['used_template']) && $this->parameters['used_template']){
			$tpl = notice_tpl_gen::get_instance($this->parameters['used_template']);
			$content = $tpl->build_notice($notice_class->id);
		}else{
			if($opac_notices_format == AFF_ETA_NOTICES_TEMPLATE_DJANGO){
				if (!$opac_notices_format_django_directory) $opac_notices_format_django_directory = "common";
				if (!$record_css_already_included) {
					if (file_exists($include_path."/templates/record/".$opac_notices_format_django_directory."/styles/style.css")) {
						$content .= "<link type='text/css' href='./includes/templates/record/".$opac_notices_format_django_directory."/styles/style.css' rel='stylesheet'></link>";
					}
					$record_css_already_included = true;
				}
				$content .= record_display::get_display_extended($notice_class->id);
			}else {
				if ($opac_notice_affichage_class) $notice_affichage = $opac_notice_affichage_class; else $notice_affichage = "notice_affichage";
				$notice_affichage = new $notice_affichage($notice_class->id);
				$notice_affichage->do_isbd();
				$content = $notice_affichage->notice_isbd;
			}
		}
		return $content;
	}
	
	/*
	 * R�cup�ration des donn�es de la source...
	*/
	public function get_datas(){
		global $opac_show_book_pics;
		global $opac_book_pics_url;
		global $base_path;
		//on commence par r�cup�rer l'identifiant retourn� par le s�lecteur...
		if($this->parameters['selector'] != ""){
			for($i=0 ; $i<count($this->selectors) ; $i++){
				if($this->selectors[$i]['name'] == $this->parameters['selector']){
					$selector = new $this->parameters['selector']($this->selectors[$i]['id']);
					break;
				}
			}
			
			$notice=$selector->get_value();
			if(is_array($notice)){
				$notice = $notice[0];
			}

			if($notice){
				$group_metadatas = parent::get_group_metadatas();
				
				$datas = array();
				$notice_class = new notice($notice);
				$url_vign = "";
				if ($opac_show_book_pics=='1') {
				    $thumbnailSourcesHandler = new ThumbnailSourcesHandler();
				    $url_vign = $thumbnailSourcesHandler->generateUrl(TYPE_NOTICE, $notice_class->id);
				}
				$datas = array(
						'id' => $notice_class->id,
						'title' => $notice_class->tit1,
						'link' => $this->get_constructed_link("notice",$notice_class->id),
						'logo_url' => $url_vign,
						'header' => $notice_class->notice_header,
						'resume' => $notice_class->n_resume, 
						'content' => $this->get_record_content($notice_class),
						'type' => 'notice',
						'record' => $notice_class
				);
				$datas["details"] = $datas;
				$datas = array_merge($datas,parent::get_datas());
				$datas['link'] = $this->get_constructed_link("notice",$notice_class->id);
				foreach ($group_metadatas as $i=>$metadatas) {
					if (is_array($metadatas["metadatas"])) {
						foreach ($metadatas["metadatas"] as $key=>$value) {
							try {
								$template_path = $base_path.'/temp/'.LOCATION.'_datasource_metadatas_records_'.$this->id;
								if(!file_exists($template_path) || (md5($value) != md5_file($template_path))){
								    file_put_contents($template_path, $value);
								}
								$H2o = H2o_collection::get_instance($template_path);
								$group_metadatas[$i]["metadatas"][$key] = $H2o->render($datas);
							}catch(Exception $e){
							    
							}
						}
					}
				}
				return $group_metadatas;
			}
		}
		return false;
	}

	public function get_format_data_structure(){
		$datas = array(
			array(
				'var' => $this->msg['cms_module_metadatas_datasource_main_fields'],
				"children" => array(
								array(
										'var' => "id",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_id_desc']
								),
								array(
										'var' => "title",
										'desc' => $this->msg['cms_module_metadatas_datasource_record_title_desc']
								),
								array(
										'var' => "resume",
										'desc' => $this->msg['cms_module_metadatas_datasource_record_resume_desc']
								),
								array(
										'var' => "logo_url",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_vign_desc']
								),
								array(
										'var' => "header",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_header_desc']
								),
								array(
										'var' => "content",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_content_desc']
								),
								array(
										'var' => "link",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_link_desc']
								),
								array(
										'var' => "record",
										'desc'=> $this->msg['cms_module_metadatas_datasource_record_data']
								)
						)
				),
			);
		
		$format_datas = array(
				array(
						'var' => "details",
						'desc' => $this->msg['cms_module_metadatas_datasource_metadatas_record_record_desc'],
						'children' => $this->prefix_var_tree($datas,"details")
				)
		);
		$format_datas = array_merge(parent::get_format_data_structure(),$format_datas);
		return $format_datas;
	}
	
	public function get_form(){
		$form = parent::get_form();
	
		if (!isset($this->parameters["used_template"]))		$this->parameters["used_template"] = "";
		$form.="
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_metadatas_datasource_record_used_template'>".$this->format_text($this->msg['cms_module_metadatas_datasource_record_used_template'])."</label>
				</div>
				<div class='colonne-suite'>";
		
		$form.= notice_tpl::gen_tpl_select("cms_module_metadatas_datasource_record_used_template",$this->parameters['used_template']);
		$form.="
				</div>
			</div>
		";
	
		return $form;
	}
	
	public function save_form(){
		global $cms_module_metadatas_datasource_record_used_template;
	
		$this->parameters['used_template'] = $cms_module_metadatas_datasource_record_used_template;
	
		return parent::save_form();
	}
}