<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_docnum_licence.class.php,v 1.2 2021/03/22 16:33:05 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once $class_path.'/onto/common/onto_common_datatype.class.php';

class onto_contribution_datatype_docnum_licence  extends onto_common_datatype {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/
	
	/**
	 *
	 * @access public
	 */

	public function check_value(){
		if (is_string($this->value)) return true;
		return false;
	}
	
	public function get_value(){
	    // si c'est un tableau on r�cup�re que la premi�re valeur
	    if (is_array($this->value)) {
	        $this->value = array_shift($this->value);
	    }
	    return $this->value;
	}
	
	public function get_formated_value(){
		$display_label = $this->offsetget_value_property("display_label");
		if ($display_label) {
			return $display_label;
		}
		return $this->value;
	}
	
	public static function get_values_from_form($instance_name, $property, $uri_item) {
		$var_name = $instance_name."_".$property->pmb_name;
		
		global ${$var_name};
		if (is_array(${$var_name}[0]['value'])) {
			$values = array();
			foreach (${$var_name}[0]['value'] as $value) {
				$values[] = array(
						'value' => $value,
						'type' => ${$var_name}[0]['type']
				);
			}
			${$var_name} = $values;
		}
		
		return parent::get_values_from_form($instance_name, $property, $uri_item);
	}
}
