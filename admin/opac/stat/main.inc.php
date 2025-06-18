<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.4 2021/02/09 07:25:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

if(!isset($act)) $act = '';

require_once("$class_path/stat_view.class.php");

$stat_view = new stat_view($section,$act);
$stat_view->proceed();

?>