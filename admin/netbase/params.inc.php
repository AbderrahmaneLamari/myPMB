<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: params.inc.php,v 1.10 2021/11/25 14:30:16 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $pmb_clean_nb_elements;
define('NOEXPL_PAQUET_SIZE', $pmb_clean_nb_elements);
define('REINDEX_PAQUET_SIZE', $pmb_clean_nb_elements);
define('SUBCOLLECTION_PAQUET_SIZE', $pmb_clean_nb_elements);
define('COLLECTION_PAQUET_SIZE', $pmb_clean_nb_elements);
define('PUBLISHER_PAQUET_SIZE', $pmb_clean_nb_elements);
define('SERIE_PAQUET_SIZE', $pmb_clean_nb_elements);
define('AUTHOR_PAQUET_SIZE', $pmb_clean_nb_elements);
define('CATEGORY_PAQUET_SIZE', $pmb_clean_nb_elements);
define('ACQUISITION_PAQUET_SIZE', $pmb_clean_nb_elements);
define('EMPR_PAQUET_SIZE', $pmb_clean_nb_elements);
define('GAUGE_SIZE', 560);
