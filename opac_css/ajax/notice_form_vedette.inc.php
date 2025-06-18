<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_form_vedette.inc.php,v 1.2 2020/12/22 16:55:44 btafforeau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $pmb_name, $index, $instance_name, $grammar;

require_once "$class_path/vedette/vedette_ui.class.php";

$vedette_ui = new vedette_ui(new vedette_composee(0, $grammar));
$type = $vedette_ui->get_vedette_type_from_pmb_name($pmb_name);
$form = $vedette_ui->get_form($pmb_name, $index, $instance_name, $type, 1, true);
print pmb_utf8_array_encode($form);