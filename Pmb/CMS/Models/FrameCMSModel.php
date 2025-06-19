<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FrameCMSModel.php,v 1.5.2.2 2023/09/13 08:38:25 gneveu Exp $
namespace Pmb\CMS\Models;

class FrameCMSModel extends FrameAbstractModel
{

    public $originClassCss = "";

    public static function getHashCadre(string $tagId)
    {
        $idCadre = substr($tagId, strrpos($tagId, "_") + 1);
        $idCadre = intval($idCadre);

        $cadreName = str_replace("_$idCadre", "", $tagId);

        return call_user_func([
            $cadreName,
            "get_hash_cache"
        ], $tagId, $idCadre);
    }

    public function clearCache()
    {
        $hash = static::getHashCadre($this->getSemantic()->getIdTag());
        if (! empty($hash)) {
            pmb_mysql_query("DELETE FROM cms_cache_cadres WHERE cache_cadre_hash ='$hash'");
        }
    }

    public function getName()
    {
        $tagId = $this->getSemantic()->getIdTag();
        $idCadre = substr($tagId, strrpos($tagId, "_") + 1);
        $idCadre = intval($idCadre);

        $cmsCadre = \cms_modules_parser::get_module_class_by_id($idCadre);
        return $cmsCadre ? $cmsCadre->name : "";
    }

    public function setName($name)
    {
        $this->name = "";
    }

    public function getOriginClassCss()
    {
        $tagId = $this->getSemantic()->getIdTag();
        $idCadre = substr($tagId, strrpos($tagId, "_") + 1);
        $query = "SELECT cadre_css_class FROM cms_cadres WHERE id_cadre = $idCadre";
        $result = pmb_mysql_query($query);
        return (pmb_mysql_num_rows($result) ? pmb_mysql_result($result, 0, 0): "");
    }

    public function setOriginClassCss($originClassCss)
    {
        // $this->originClassCss = $originClassCss;
    }
}