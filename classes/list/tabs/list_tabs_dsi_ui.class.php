<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_tabs_dsi_ui.class.php,v 1.1 2020/11/23 09:02:56 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_tabs_dsi_ui extends list_tabs_ui {
	
	protected function _init_tabs() {
		//Diffusion
		$this->add_tab('dsi_menu_diffusion', 'diffuser', 'dsi_menu_dif_lancer', 'lancer');
		$this->add_tab('dsi_menu_diffusion', 'diffuser', 'dsi_menu_dif_auto', 'auto');
		$this->add_tab('dsi_menu_diffusion', 'diffuser', 'dsi_menu_dif_manu', 'manu');
		
		//Bannettes
		$this->add_tab('dsi_menu_bannettes', 'bannettes', 'dsi_menu_ban_pro', 'pro');
		$this->add_tab('dsi_menu_bannettes', 'bannettes', 'dsi_menu_ban_abo', 'abo');
		
		//Equations
		$this->add_tab('dsi_menu_equations', 'equations', 'dsi_menu_equ_gestion', 'gestion');
		
		//Options
		$this->add_tab('dsi_menu_options', 'options', 'dsi_menu_cla_gestion', 'classements');
		
		//Flux RSS
		$this->add_tab('dsi_menu_flux', 'fluxrss', 'dsi_menu_flux_definition', 'definition');
		
		//Veilles
		$this->add_tab('dsi_menu_docwatch', 'docwatch', 'dsi_menu_docwatch_definition');
	}
}