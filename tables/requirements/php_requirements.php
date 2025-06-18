<?php
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: php_requirements.php,v 1.2 2021/04/29 08:39:07 dbellamy Exp $

$php_suggested_setup = [
    "date.timezone" => [
        "value" => "Europe/Paris",
        "numeric_value" => ""
    ],
    "display_errors" => [
        "value" => "Off",
        "numeric_value" => ""
    ],
    "expose_php" => [
        "value" => "Off",
        "numeric_value" => ""
    ],
    "max_execution_time" => [
        "value" => ">= 300",
        "numeric_value" => "300"
    ],
    "max_input_vars" => [
        "value" => ">= 50000",
        "numeric_value" => "50000"
    ],
    "memory_limit" => [
        "value" => "256M",
        "numeric_value" => "256M"
    ],
    "post_max_size" => [
        "value" => ">= 64M",
        "numeric_value" => "64M"
    ],
    "upload_max_filesize" => [
        "value" => ">= 64M",
        "numeric_value" => "64M"
    ],
    "suhosin.request.max_vars" => [
        "value" => ">= 2048",
        "numeric_value" => ""
    ],
    "suhosin.post.max_vars" => [
        "value" => ">= 2048",
        "numeric_value" => ""
    ],
];