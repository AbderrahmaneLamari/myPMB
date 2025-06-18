<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_linked_record_selector.class.php,v 1.3 2020/11/05 15:44:01 jlaurent Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once $class_path.'/onto/common/onto_common_datatype.class.php';


/**
 * class onto_common_datatype_resource_selector
 * Les méthodes get_form,get_value,check_value,get_formated_value,get_raw_value
 * sont éventuellement à redéfinir pour le type de données
 */
class onto_contribution_datatype_linked_record_selector  extends onto_common_datatype {

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
	    if (isset($this->formated_value)) {
	        return $this->formated_value;
	    }
	    $this->formated_value = [
	        "record" => [
	            'value' => $this->get_raw_value(),
	            'display_label' => $this->offsetget_value_property('display_label') ?? "",
	        ]
	    ];
	    
	    $assertions = $this->offsetget_value_property("assertions");
	    if (is_array($assertions)) {
	        /* @var $assertion onto_assertion */
	        foreach ($assertions as $assertion) {
	            switch ($assertion->get_predicate()) {
	                case 'http://www.pmbservices.fr/ontology#relation_type' :
	                case 'relation_type' :
	                    $this->formated_value['relation_type'] = $assertion->get_object();
	                    break;
	                case 'http://www.pmbservices.fr/ontology#has_record' :
	                case 'has_record' :
	                    $this->formated_value['record'] = array(
                                'value' => $assertion->get_object(),
                                'display_label' => $assertion->offset_get_object_property('display_label')
	                    );
	                    break;
	                case 'http://www.pmbservices.fr/ontology#direction' :
	                case 'direction' :
	                    $this->formated_value['direction'] = $assertion->get_object();
	                    break;
	                case 'http://www.pmbservices.fr/ontology#num_reverse_link' :
	                case 'num_reverse_link' :
	                    $this->formated_value['num_reverse_link'] = $assertion->get_object();
	                    break;
	            }
	        }
	    }
		return $this->formated_value;
	}
	
	public function get_value_type() {
	    return 'http://www.pmbservices.fr/ontology#linked_record';
	}
	
	
	public function get_raw_value() {
	    //si c'est un tableau, on retourne la première valeur dans le cas générale
	    if (is_array($this->value)) {
	        foreach ($this->value as $key => $value) {
	            return $value;
	        }
	    }
	    return $this->value;
	}
 
} // end of onto_common_datatype_resource_selector
