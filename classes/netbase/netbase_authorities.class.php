<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: netbase_authorities.class.php,v 1.2.6.2 2023/06/15 11:57:48 dgoron Exp $
if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once ($class_path . "/indexations_collection.class.php");

class netbase_authorities {

    public static function index_from_query($query, $object_type=0) {
        $result = pmb_mysql_query($query);
        $nb_indexed = pmb_mysql_num_rows($result);
        if ($nb_indexed) {
            $indexation_authority = indexations_collection::get_indexation($object_type);
            $indexation_authority->set_deleted_index(true);
            authorities_collection::setOptimizer(authorities_collection::OPTIMIZE_MEMORY);
            while (($row = pmb_mysql_fetch_object($result))) {
                $indexation_authority->maj($row->id);
            }
            pmb_mysql_free_result($result);
        }
        return $nb_indexed;
    }
    
    public static function index($object_type=0) {
    	$indexation_authorities = new indexation_authorities(indexations_collection::get_xml_file_path($object_type), "authorities", $object_type);
		$indexation_authorities->launch_indexation();
    }
    
    public static function index_by_step($object_type=0, $step=0) {
    	$indexation_authorities = new indexation_authorities(indexations_collection::get_xml_file_path($object_type), "authorities", $object_type);
    	if($step == 0) {
    		netbase_entities::clean_files($indexation_authorities->get_directory_files());
    	}
    	return $indexation_authorities->maj_by_step($step);
    }
} // fin de déclaration de la classe netbase