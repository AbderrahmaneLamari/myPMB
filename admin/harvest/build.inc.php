<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: build.inc.php,v 1.3 2021/01/29 09:34:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id;

require_once($class_path."/harvest.class.php");
require_once($class_path."/configuration/configuration_harvest_controller.class.php");

configuration_harvest_controller::set_model_class_name('harvest');
configuration_harvest_controller::set_list_ui_class_name('list_configuration_harvest_profil_ui');
configuration_harvest_controller::proceed($id);