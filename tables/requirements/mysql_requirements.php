<?php
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mysql_requirements.php,v 1.3 2021/04/29 13:05:28 rtigero Exp $
$mysql_suggested_setup = [
    "max_allowed_packet" => [
        "numeric_size" => "yes",
        "value" => "16M"
    ],
    "sql_mode" => [
        "numeric_size" => "",
        "value" => "NO_AUTO_CREATE_USER"
    ],
    "character_set_server" => [
        "numeric_size" => "",
        "value" => "utf8 (de préférence) ou latin1"
    ],
    "collation_server" => [
        "numeric_size" => "",
        "value" => "utf8_unicode_ci (de préférence) ou latin1_swedish_ci"
    ],
    "default_storage_engine" => [
        "numeric_size" => "",
        "value" => "MyISAM ou InnoDB"
    ],
    "open_files_limit" => [
        "numeric_size" => "",
        "value" => ">= 10000"
    ],
    "key_buffer_size" => [
        "numeric_size" => "yes",
        "value" => "1G"
    ],
    "join_buffer_size" => [
        "numeric_size" => "yes",
        "value" => "4M"
    ],
    "connect_timeout" => [
        "numeric_size" => "",
        "value" => "10"
    ],
    "interactive_timeout" => [
        "numeric_size" => "",
        "value" => "300"
    ],
    "wait_timeout" => [
        "numeric_size" => "",
        "value" => "300"
    ],
    "query_cache_limit" => [
        "numeric_size" => "yes",
        "value" => ">2M"
    ],
    "query_cache_size" => [
        "numeric_size" => "yes",
        "value" => ">=2M"
    ]
];