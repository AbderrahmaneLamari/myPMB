<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: index.php,v 1.3.20.1 2023/11/30 11:07:24 qvarin Exp $

//Ce script nécéssite php5
if (PHP_VERSION < 5) {
    die("PHP5 required");
}

$group = isset($_GET["group"]) ? $_GET["group"] : '';
if ($group !== basename($group) || strpos($group, '.') !== false) {
    header('Location: ./index.php', true, 301);
    exit;
}

header("Content-Type: text/html; charset=utf-8");

$doc = new DOMDocument('1.0');
$xsl = new XSLTProcessor();
$xsl->registerPHPFunctions();

$doc->load("mache_doc_group_to_html.xsl");
$xsl->importStyleSheet($doc);
$xsl->setParameter('', 'working_group', $group);
$xsl->setParameter('', 'external_services_basepath', '..');
$xsl->setParameter('', 'navigation_base', '?');

$catalog_file = 'catalog.xml';
if (file_exists('../catalog_subst.xml')) {
    $catalog_file = 'catalog_subst.xml';
}

$xsl->setParameter('', 'catalog_file', $catalog_file);
print $xsl->transformToXML($doc);
