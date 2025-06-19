<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: main.inc.php,v 1.1 2022/04/29 15:17:09 gneveu Exp $
use Pmb\Digitalsignature\Controller\SignatureController;
use Pmb\Digitalsignature\Controller\CertificateController;

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) {
    die("no access");
}

global $sub, $action, $include_path, $lang;
global $data;
global $id;

$data = json_decode(stripslashes($data));
if (empty($data)) {
    $data = new stdClass();
}

if (isset($id)) {
    $data->id = $id;
}

switch ($sub) {
    case "certificate":
        $digitalSignature = new CertificateController();
        $digitalSignature->proceed($action, $data);
        break;
    case "signature":
        $digitalSignature = new SignatureController();
        $digitalSignature->proceed($action, $data);
        break;
    default:
        include ("$include_path/messages/help/$lang/admin_digital_signature.txt");
        break;
}
