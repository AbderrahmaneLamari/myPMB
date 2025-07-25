<?php
// +-------------------------------------------------+
// � 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: frbr_entity_collections_view.class.php,v 1.4.4.1 2023/12/07 15:07:34 pmallambic Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class frbr_entity_collections_view extends frbr_entity_common_view_django{
	
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->default_template = "<div>
<h3>{{collection.name}}</h3>
<div>{{collection.comment}}</div>
</div>";
	}
		
	public function render($datas, $grouped_datas = []){	
		//on rajoute nos �l�ments...
		//le titre
		$render_datas = array();
		$render_datas['title'] = $this->msg["frbr_entity_collections_view_title"];
		$render_datas['collection'] = authorities_collection::get_authority('authority', 0, ['num_object' => $datas[0], 'type_object' => AUT_TABLE_COLLECTIONS]);
		//on rappelle le tout...
		return parent::render($render_datas);
	}
	
	public function get_format_data_structure(){		
		$format = array();
		$format[] = array(
			'var' => "title",
			'desc' => $this->msg['frbr_entity_collections_view_title']
		);
		$collection = array(
			'var' => "collection",
			'desc' => $this->msg['frbr_entity_collections_view_label'],
			'children' => authority::get_properties(AUT_TABLE_COLLECTIONS,"collection")
		);
		$format[] = $collection;
		$format = array_merge($format,parent::get_format_data_structure());
		return $format;
	}
}