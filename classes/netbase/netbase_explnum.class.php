<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: netbase_explnum.class.php,v 1.1.2.3 2023/12/27 16:06:28 tsamson Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/explnum.class.php");

class netbase_explnum {
    
    const MAX_FILE_SIZE = 150000000;
		
    public static function gen_docnum_thumbnail() {
        $nb_thumbnails = 0;
		$query = "SELECT explnum_id FROM explnum WHERE (explnum_vignette = '' OR explnum_vignette IS NULL)";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			while($row = pmb_mysql_fetch_assoc($result)) {
			    $expl = new explnum($row["explnum_id"]);
			    $file_exists = file_exists($expl->explnum_rep_path.$expl->explnum_nomfichier);
			    if ($file_exists && filesize($expl->explnum_rep_path.$expl->explnum_nomfichier) < self::MAX_FILE_SIZE) {
			        $vign = reduire_image($expl->explnum_rep_path.$expl->explnum_nomfichier);
			        if ($vign) {
			            $expl->explnum_vignette = $vign;
			            $expl->save();
			            $nb_thumbnails++;
			        }
			    }
			}
		}
		return $nb_thumbnails;
	}
} // fin de déclaration de la classe netbase_records
