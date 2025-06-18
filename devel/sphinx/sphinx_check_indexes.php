<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sphinx_check_indexes.php,v 1.2 2020/04/23 09:22:55 btafforeau Exp $

$base_path = __DIR__ . '/../..';
$base_noheader = 1;
$base_nocheck = 1;
$base_nobody = 1;
$base_nosession = 1;

global $class_path, $argv;

require_once $base_path.'/includes/init.inc.php';
require_once $class_path.'/parametres_perso.class.php';

require_once $class_path.'/sphinx/sphinx_records_indexer.class.php';
require_once $class_path.'/sphinx/sphinx_titres_uniformes_indexer.class.php';

$entities = array(
    'records',
    'titres_uniformes',
    'series',
    'categories',
    'collections',
    'subcollections',
    'authperso',
    'indexint',
    'authors',
    'concepts',
    'publishers'
);

foreach ($entities as $entity) {
    $index_class = 'sphinx_'.$entity.'_indexer';
    if (class_exists($index_class)) {
        $sconf = new $index_class();
        $sconf->checkExistingIndexes();
    }
}
