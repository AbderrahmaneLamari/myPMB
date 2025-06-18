<?php
// +-------------------------------------------------+
// © 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.7.2.1 2022/01/18 09:12:47 qvarin Exp $
use Pmb\Animations\Controller\PriceController;
use Pmb\Animations\Controller\StatusController;
use Pmb\Animations\Controller\MailingController;
use Pmb\Animations\Controller\TypesController;

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) {
    die("no access");
}

global $action;
global $data;
global $sub;

switch ($sub) {
    case 'priceTypes':
        $data = json_decode(stripslashes($data));
        $priceController = new PriceController();
        $result = $priceController->proceed($action, $data);
        ajax_http_send_response($result);
        break;
    case 'status':
        $data = json_decode(stripslashes($data));
        $statusController = new StatusController();
        $result = $statusController->proceed($action, $data);
        ajax_http_send_response($result);
        break;
    case 'mailing':
        $data = json_decode(stripslashes($data));
        $mailingController = new MailingController($data);
        $result = $mailingController->proceed($action, $data);
        ajax_http_send_response($result);
        break;
    case 'types':
        $data = json_decode(stripslashes($data));
        $typesController = new TypesController();
        $result = $typesController->proceed($action, $data);
        ajax_http_send_response($result);
        break;
    default:
        break;
}