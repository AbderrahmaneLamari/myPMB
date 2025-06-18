<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.1.16.1 2021/12/01 13:11:32 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $include_path, $docwatch_tpl;

require_once($include_path."/templates/docwatch.tpl.php");

print $docwatch_tpl;