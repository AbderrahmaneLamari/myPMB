<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_bannettes_abon_priv_ui.class.php,v 1.1.2.2 2023/11/28 10:31:27 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_opac_bannettes_abon_priv_ui extends list_opac_bannettes_abon_ui {
	
// 	protected function get_title() {
// 		global $msg;
		
// 		return "<h3><span>".$msg['dsi_bannette_gerer_priv']."</span></h3>\n";
// 	}
	
    /**
     * Initialisation des colonnes disponibles
     */
    protected function init_available_columns() {
        parent::init_available_columns();
        $this->available_columns['main_fields']['actions'] = '';
    }
    
    protected function init_default_columns() {
        parent::init_default_columns();
        $this->add_column('actions');
    }
    
    protected function init_default_settings() {
        parent::init_default_settings();
        $this->set_setting_column('actions', 'exportable', false);
    }
    
    protected function get_cell_content($object, $property) {
        global $msg, $charset, $base_path;
        global $opac_allow_resiliation;
        
        $content = '';
        switch($property) {
            case 'subscribed':
                if (!$opac_allow_resiliation && count($object->categorie_lecteurs)) {
                    $content .= "\n<input type='checkbox' name='dummy[]' value='' disabled />";
                    $content .= "<input type='hidden' name='bannette_abon[".$object->id_bannette."]' value='1' style='display:none'/>";
                } else {
                    $content .= "\n<input type='checkbox' name='bannette_abon[".$object->id_bannette."]' value='1' />";
                }
                break;
            case 'actions':
                $content .= "<a href='".$base_path."/empr.php?tab=dsi&lvl=bannette_edit&id_bannette=".$object->id_bannette."' style='cursor : pointer'>
                    <img src='".get_url_icon('tag.png')."' alt='".htmlentities($msg['edit'],ENT_QUOTES,$charset)."' title='".htmlentities($msg['edit'],ENT_QUOTES,$charset)."' />
                </a>";
                break;
            default :
                $content .= parent::get_cell_content($object, $property);
                break;
        }
        return $content;
    }
}