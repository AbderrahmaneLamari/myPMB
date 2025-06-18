<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_universes.inc.php,v 1.3 2020/11/06 14:33:25 btafforeau Exp $
if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $autoloader, $id;

require_once "$class_path/search_universes/search_universes_controller.class.php";
require_once "$class_path/autoloader.class.php";

if (!is_object($autoloader)) {
    $autoloader = new autoloader();
}
$autoloader->add_register("onto_class", true);

$search_universes_controller = new search_universes_controller($id);
$search_universes_controller->proceed_ajax();