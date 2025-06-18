<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_segment_external_sort.class.php,v 1.1.4.3 2021/08/03 09:20:33 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once "$class_path/fields/sort_fields.class.php";
require_once "$class_path/search_universes/search_segment_sort.class.php";

class search_segment_external_sort extends search_segment_sort{

	public function sort_data($data, $offset = 0, $limit = 0, $query_searcher = '') {
	    $query = $this->appliquer_tri($this->num_segment, $query_searcher, 'notice_id', $offset, $limit);
	    $res = pmb_mysql_query($query);
	    if ($res && pmb_mysql_num_rows($res)) {
	        $this->result = array();
	        while ($row = pmb_mysql_fetch_object($res)) {
	            $this->result[] = $row->notice_id ;
	        } 
	    }	
	    return $this->result;
	}
	
	/**
	 * Genere la requete select d'un element table
	 */
	public function genereRequeteUpdate($desTable, $nomTable, $nomChp, $nomColonneTempo) {
	    
	    $query = "SELECT rid, source_id FROM external_count JOIN $nomTable ON rid = notice_id";
	    $result = pmb_mysql_query($query);
	    $infos = [];
	    
	    if (pmb_mysql_num_rows($result)) {
	        while($row = pmb_mysql_fetch_assoc($result)) {
	            $table_name = "entrepot_source_".$row["source_id"];
	            if (!isset($infos[$table_name])) {
	                $infos[$table_name] = [];
	            }
	            $infos[$table_name][] = $row["rid"];
	        }
	    }
	    
	    $query = "";
	    foreach ($infos as $name => $ids) {
	        if ($query) {
	            $query .= " UNION ";
	        }
	        $query .= "SELECT recid AS notice_id, ".$this->ajoutIfNull($desTable["TABLEFIELD"][0])." AS $nomChp FROM $name ";
	        //
	        //On ajout les éventuelles liaisons
	        //
	        if(isset($desTable["LINK"])) {
	            for ($x = 0; $x < count($desTable["LINK"]); $x++) {
	                $query .= static::genereRequeteLinks($desTable, $nomTable, $desTable["LINK"][$x], $name, "notice_id");
	            }
	        }
	        $query .= " WHERE ";
	        //si on a un filtre supplementaire
	        if (isset($desTable["FILTER"])) {
	            if (isset($desTable["FILTER"][0]["GLOBAL"])) {
	                global ${$desTable["FILTER"][0]["GLOBAL"]};
	                $desTable["FILTER"][0]['value'] = str_replace('!!' . $desTable["FILTER"][0]["GLOBAL"] . '!!', ${$desTable["FILTER"][0]["GLOBAL"]}, $desTable["FILTER"][0]['value']);
	            }
	            $query .= " " . $desTable["FILTER"][0]['value'];
	        }
	        
	        $query .= " AND recid IN (".implode(',', $ids).")";
	        
	        //On applique la restriction ORDER BY
	        //Utilisé pour les types de langues ou d'auteurs, ...
	        if (isset($desTable["ORDERBY"])) {
	            $query .= " ORDER BY ".$this->ajoutIfNull($desTable["ORDERBY"][0]);
	        }
	        //Si l'on a un group by on passe par une sous-requete pour que le groupement soit fait après le tri (Cas des Auteurs : C'est l'auteur principal qui doit être utilisé pour le tri)
	        if (isset($desTable["GROUPBY"])) {
	            if (isset($desTable["ORDERBY"])) {
	                // Si ORDER BY, on passe par une table temporaire car sinon il n'est pas pris en compte par le group by
	                $sql = "DROP TEMPORARY TABLE IF EXISTS ".$nomTable."_groupby";
	                pmb_mysql_query($sql);
	                $temporary2_sql = "CREATE TEMPORARY TABLE ".$nomTable."_groupby ENGINE=MyISAM (".$query.")";
	                
	                pmb_mysql_query($temporary2_sql);
	                pmb_mysql_query("alter table ".$nomTable."_groupby add index(notice_id)");
	                
	                $query = "SELECT * FROM ".$nomTable."_groupby";
	                $query .= " GROUP BY ".$desTable["GROUPBY"][0]["value"];
	            } else {
	                $query = "SELECT * FROM (".$query.") AS asubquery";
	                $query .= " GROUP BY ".$desTable["GROUPBY"][0]["value"];
	            }
	        }
	    }
	    //
	    //On met le tout dans une table temporaire
	    //
	    $sql = "DROP TEMPORARY TABLE IF EXISTS ".$nomTable."_update";
	    pmb_mysql_query($sql);
	    $temporary2_sql = "CREATE TEMPORARY TABLE ".$nomTable."_update ENGINE=MyISAM (SELECT * FROM (".$query.") AS temp)";
	    
	    pmb_mysql_query($temporary2_sql);
	    pmb_mysql_query("alter table ".$nomTable."_update add index(notice_id)");
	    
	    //
	    //Et on rempli la table tri_tempo avec les éléments de la table temporaire
	    //
	    $requete = "UPDATE $nomTable, ".$nomTable."_update
                    SET $nomTable.$nomChp  = ".$nomTable."_update.$nomChp
                    WHERE $nomTable.notice_id =  ".$nomTable."_update.notice_id
                    AND ".$nomTable."_update.$nomChp IS NOT NULL
                    AND ".$nomTable."_update.$nomChp != ''";
	    return $requete;
	}
	
}