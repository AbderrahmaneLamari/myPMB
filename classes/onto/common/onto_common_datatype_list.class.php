<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_common_datatype_list.class.php,v 1.3 2021/08/05 12:22:03 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once $class_path.'/onto/common/onto_common_datatype.class.php';


/**
 * class onto_common_datatype_list
 * Les m�thodes get_form,get_value,check_value,get_formated_value,get_raw_value
 * sont �ventuellement � red�finir pour le type de donn�es
 */
class onto_common_datatype_list  extends onto_common_datatype {

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
		if (!empty(${$var_name}) && !empty(${$var_name}[0]) && !empty(${$var_name}[0]['value']) && is_array(${$var_name}[0]['value'])) {
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

} // end of onto_common_datatype_resource_selector
