<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_bannettes_abon_pub_ui.class.php,v 1.1.2.2 2023/11/28 10:31:27 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_opac_bannettes_abon_pub_ui extends list_opac_bannettes_abon_ui {
	
	protected function _get_query() {
	    $query = $this->_get_query_base();
        $query .= " join bannette_abon on num_bannette=id_bannette ";
	    $query .= $this->_get_query_filters();
        $query .= " union ".$this->_get_query_base()." where ((id_bannette IN (".implode(',',$this->get_access_liste_id()).")) or (bannette_opac_accueil = 1)) and proprio_bannette=0 ";
	    $query .= $this->_get_query_order();
	    if($this->applied_sort_type == "SQL"){
	        $this->pager['nb_results'] = pmb_mysql_num_rows(pmb_mysql_query($query));
	        $query .= $this->_get_query_pager();
	    }
	    return $query;
	}
	
// 	protected function get_title() {
// 		global $msg;
		
// 		return "<h3><span>".$msg['dsi_bannette_gerer_pub']."</span></h3>\n";
// 	}
	
    protected function get_cell_content($object, $property) {
        global $opac_allow_resiliation;
        
        $content = '';
        switch($property) {
            case 'subscribed':
                if (!$opac_allow_resiliation && count($object->categorie_lecteurs)) {
                    $content .= "<input type='checkbox' name='dummy[]' value='' ".($object->is_subscribed(static::$id_empr) ? "checked='checked'" : "")." disabled />";
                    $content .= "<input type='hidden' name='bannette_abon[".$object->id_bannette."]' value='1' style='display:none'/>";
                } else {
                    $content .= "<input type='checkbox' name='bannette_abon[".$object->id_bannette."]' value='1' ".($object->is_subscribed(static::$id_empr) ? "checked='checked'" : "")." />";
                }
                break;
            default :
                $content .= parent::get_cell_content($object, $property);
                break;
        }
        return $content;
    }
}