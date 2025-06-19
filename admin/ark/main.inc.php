<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.2 2022/04/05 07:19:45 tsamson Exp $
use Pmb\Ark\Controller\NaanController;
use Pmb\Ark\Controller\ArkGenerateController;

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) {
    die("no access");
}

global $id;
global $data;
global $action;
global $sub;

switch ($sub) {
    case 'naan':
        $data = json_decode(stripslashes($data));
        $naanController = new NaanController();
        $naanController->proceed($action, $data);
        break;
    case 'generate':
        $data = json_decode(stripslashes($data));
        $generateController = new ArkGenerateController();
        $generateController->proceed($action, $data);
        break;
    default:
        include("$include_path/messages/help/$lang/admin_ark.txt");
        break;
}
