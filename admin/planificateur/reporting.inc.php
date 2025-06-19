<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reporting.inc.php,v 1.6.12.1 2023/03/16 14:18:36 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id;
require_once($class_path."/scheduler/scheduler_dashboard_controller.class.php");

scheduler_dashboard_controller::proceed($id);




