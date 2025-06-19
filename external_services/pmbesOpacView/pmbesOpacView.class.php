<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesOpacView.class.php,v 1.2.14.1 2023/03/16 10:52:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/external_services.class.php");
require_once($class_path."/opac_view.class.php");

class pmbesOpacView extends external_services_api_class {
	
	public function gen_search() {
		$views=new opac_view();
		$views->gen();
	}
}