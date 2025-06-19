<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_opac_bulletins_a2z_ui.class.php,v 1.1.2.2 2023/12/12 14:23:01 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_opac_bulletins_a2z_ui extends list_opac_bulletins_ui {
	
	protected function get_display_pager() {
	    $action = "show_perio(".$this->filters['serial_id'].");return false;";
	    $url_page = "javascript:changepage(!!page!!,".$this->filters['serial_id'].")";
	    if ($this->pager['nb_page']>1) {
	        $navBar = getNavbar($this->pager['page'], $this->pager['nb_results'], $this->pager['nb_per_page'], $url_page, '', '#');
	        $navBar->setOnsubmit($action);
	        return $navBar->getPaginatorPerio();
	    }
	    return '';
	}
	
	protected function _cell_is_sortable($name) {
	    return false;
	}
	
	protected function get_js_sort_script_sort() {
	    return '';
	}
}