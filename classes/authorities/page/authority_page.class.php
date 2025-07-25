<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority_page.class.php,v 1.4 2021/06/14 07:38:34 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/authorities/tabs/authority_tabs.class.php');

/**
 * class authority_page
 * Controler G�n�rique d'une page d'autorit�
 */
class authority_page {	

	/**
	 * Instance de la classe authority
	 * @var authority
	 */
	protected $authority;
	
	/**
	 * Objet repr�sentant les onglets de l'autorit� courante
	 * @var authority_tabs
	 */
	protected $authority_tabs = null;
	
	/**
	 * Constructeur
	 * @param authority $authority Instance d'autorit�
	 */
	public function __construct($authority){
		$this->authority = $authority;
	}
	
	/**
	 * Aiguilleur
	 * @param array $context
	 */
	public function proceed($context=array()){
		global $pmb_url_base, $categ, $sub, $id, $quoi;
		
		if(is_object($this->authority)){
			// On va chercher les onglets de l'autorit�
			$this->authority->set_authority_tabs($this->get_authority_tabs());
			// On va chercher la liste d'�l�ments � afficher
			$authority_list_ui = $this->authority->get_authority_list_ui();
			if ($authority_list_ui) $authority_list_ui->set_current_url($pmb_url_base.'autorites.php?categ='.$categ.'&sub='.$sub.'&id='.$id.'&quoi='.$quoi);
			print $this->authority->render($context);
		}
	}
	
	/**
	 * Retourne les onglets de la page de l'autorit�
	 * @return authority_tabs
	 */
	protected function get_authority_tabs() {
		if (!$this->authority_tabs) {
			$this->authority_tabs = new authority_tabs($this->authority);
		}
		return $this->authority_tabs;
	}
}