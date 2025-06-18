<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_segment_searcher_extended.class.php,v 1.1 2021/04/30 12:15:43 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once $class_path.'/searcher.class.php';

class search_segment_searcher_extended extends searcher_extended {

    protected function get_search_instance(){
        return new search('search_fields_gestion');
    }
}