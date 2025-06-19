<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.14 2022/10/06 07:32:19 gneveu Exp $
use Pmb\Animations\Controller\PriceController;
use Pmb\Animations\Controller\StatusController;
use Pmb\Animations\Controller\MailingController;
use Pmb\Animations\Controller\TypesController;
use Pmb\Animations\Controller\CalendarController;

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) {
    die("no access");
}

global $id;
global $data;
global $action;
global $sub;

switch ($sub) {
    case 'priceTypes':
        $data = json_decode(stripslashes($data));
        $priceController = new PriceController();
        $priceController->proceed($action, $data);
        break;
    case 'status':
        $data = json_decode(stripslashes($data));
        $statusController = new StatusController();
        $statusController->proceed($action, $data);
        break;
    case 'types':
        $data = json_decode(stripslashes($data));
        $typesController = new TypesController();
        $typesController->proceed($action, $data);
        break;
    case 'calendar':
        $data = json_decode(stripslashes($data));
        $calendarController = new CalendarController();
        $calendarController->proceed($action, $data);
        break;
    case 'priceTypesPerso':
    case 'perso':
        include ("./admin/animations/perso.inc.php");
        break;
    case 'mailing':
        $data = json_decode(stripslashes($data));
        if (empty($data)) {
            $data = new stdClass();
        }
        if (! empty($id)) {
            $data->id = $id;
        }
        $mailingController = new MailingController($data);
        $mailingController->proceed($action, $data);
        break;
    default:
        break;
}
