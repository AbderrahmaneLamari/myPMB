<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mailing.inc.php,v 1.1 2021/03/08 16:48:52 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php"))
    die("no access");
    
use Pmb\Animations\Controller\MailingController;

global $action, $data;

$data = encoding_normalize::json_decode(encoding_normalize::utf8_normalize(stripslashes($data)));
$mailingController = new MailingController($data);
$result = $mailingController->proceed($action);
ajax_http_send_response(encoding_normalize::utf8_normalize($result));