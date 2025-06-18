<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_view_animationslist.class.php,v 1.1 2021/03/26 13:47:45 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

use Pmb\Animations\Models\AnimationModel;

class cms_module_common_view_animationslist extends cms_module_common_view_django {
    
	public function __construct($id = 0) {
		parent::__construct($id);
		
		$this->default_template = "{% for animation in animations %}
    <p>{{ animation.header }}</p>
    <blockquote>{{ animation.content }}</blockquote>
{% endfor %}";
	}
	
	public function get_form() {
		if (!isset($this->parameters['used_template'])) $this->parameters['used_template'] = '';
		$form = "
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_view_animationslist_link'>".$this->format_text($this->msg['cms_module_common_view_animationslist_link'])."</label>
				</div>
				<div class='colonne-suite'>";
		$form .= $this->get_constructor_link_form('animation');
		$form .= "
				</div>
			</div>";
		$form .= parent::get_form();
		$form .= "
			<div class='row'>
				<div class='colonne3'>
					<label for='cms_module_common_view_animationslist_used_template'>".$this->format_text($this->msg['cms_module_common_view_animationslist_used_template'])."</label>
				</div>
				<div class='colonne-suite'>";
		$form .= notice_tpl::gen_tpl_select('cms_module_common_view_animationslist_used_template', $this->parameters['used_template']);
		$form .= "				
				</div>
			</div>
		";
		
		return $form;
	}
	
	public function save_form() {
		global $cms_module_common_view_animationslist_used_template;
		
		$this->save_constructor_link_form('animation');
		$this->parameters['used_template'] = $cms_module_common_view_animationslist_used_template;
		
		return parent::save_form();
	}
	
	public function render($data) {
	    $animation_data = [];
	    if (! empty($data['animations'])) {
	        foreach ($data['animations'] as $animation_id) {
	            if (! empty($animation_id)) {
	                $animation = new AnimationModel($animation_id);
	                $animation->getViewData();
	                $animation_data[] = $animation;
	            }
	        }
	    }
	    
	    $render_data = array(
	        'title' => $data['title'] ?? '',
	        'animations' => $animation_data,
	    );
	    
	    return parent::render($render_data);
	}
	
	public function get_format_data_structure() {
        $animation = new AnimationModel();
        
        return array_merge($animation->getCmsStructure('animation'), parent::get_format_data_structure());
	}
}