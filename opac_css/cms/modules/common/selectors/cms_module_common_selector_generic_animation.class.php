<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_common_selector_generic_animation.class.php,v 1.1 2021/03/31 08:47:33 qvarin Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_common_selector_generic_animation extends cms_module_common_selector {
	
	public function __construct($id=0){
		parent::__construct($id);
		$this->once_sub_selector=true;
	}
	
	protected function get_sub_selectors(){
		return array(
			"cms_module_common_selector_animation",
			"cms_module_common_selector_env_var",
			"cms_module_common_selector_global_var"
		);
	}
	
	/*
	 * Retourne la valeur sélectionné
	 */
	public function get_value(){
		if(!$this->value){
			$sub = $this->get_selected_sub_selector();
			$this->value = $sub->get_value()*1;
		}
		return $this->value;
	}
}