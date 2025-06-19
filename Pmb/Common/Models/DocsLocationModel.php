<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DocsLocationModel.php,v 1.6 2022/12/14 15:06:04 gneveu Exp $

namespace Pmb\Common\Models;

use Pmb\Common\Orm\DocsLocationOrm;

class DocsLocationModel extends Model
{
    protected $ormName = "\Pmb\Common\Orm\DocsLocationOrm";
    
    public static function getLocationList()
    {
        $animationsList = DocsLocationOrm::findAll();
        return self::toArray($animationsList);
    }
    
    public static function delete($id) {
        if ($id != 1) {
            $animationStatus = new DocsLocationOrm($id);
            $animationStatus->delete();
            return true;
        }
        return false;
    }
    
    public static function getInfosLocs($animation){
        
        $infosLoc = "";
        $first = true;
        foreach ($animation->location as $loc){
            $location = new DocsLocationOrm($loc['id']);
            $infosLoc .= !$first ? "<br><br>" : "";
            $infosLoc .= $location->location_libelle ? $location->location_libelle . "<br>" : '' ;
            $infosLoc .= $location->name ? $location->name . "<br>" : '' ;
            $infosLoc .= $location->adr1 ? $location->adr1 . "<br>" : '' ;
            $infosLoc .= $location->adr2 ? $location->adr2 . "<br>" : '' ;
            $infosLoc .= $location->cp ? $location->cp . "<br>" : '' ;
            $infosLoc .= $location->town ? $location->town : '';
            
            $first = false;
        }
        
        return $infosLoc;
    }
    
    public static function getLocationAnimation ($animationId) {
        $query = "
            SELECT * FROM docs_location 
            JOIN anim_animation_locations 
            ON docs_location.idlocation = anim_animation_locations.num_location 
            WHERE num_animation = " . intval($animationId);
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            return pmb_mysql_fetch_assoc($result);
        }
        return null;
    }
}