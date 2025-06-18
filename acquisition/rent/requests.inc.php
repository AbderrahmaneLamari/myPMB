<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: requests.inc.php,v 1.5 2021/04/22 09:00:55 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id;

require_once ($class_path."/rent/rent_requests_controller.class.php");
require_once($class_path.'/entites.class.php');
require_once($class_path.'/rent/rent_request.class.php');

rent_requests_controller::proceed($id);