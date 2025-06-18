<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collstate_bulletins_display.inc.php,v 1.1.12.1 2022/01/19 11:46:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $id, $serial_id, $bulletin_id;

require_once($class_path."/collstate.class.php");

$collstate = new collstate($id, $serial_id, $bulletin_id);
$html = $collstate->get_collstate_bulletins_display();
print $html;