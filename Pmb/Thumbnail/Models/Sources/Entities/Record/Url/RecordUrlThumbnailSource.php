<?php
// +-------------------------------------------------+
// ï¿½ 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RecordUrlThumbnailSource.php,v 1.4.2.5 2023/11/27 10:13:59 tsamson Exp $

namespace Pmb\Thumbnail\Models\Sources\Entities\Record\Url;

use Pmb\Thumbnail\Models\Sources\Entities\Common\Url\UrlThumbnailSource;
use Pmb\Common\Helper\GlobalContext;

class RecordUrlThumbnailSource extends UrlThumbnailSource
{
    /**
     * 
     * @var string
     */
    const BASE64_STR = "base64,";
    /**
     * 
     * {@inheritDoc}
     * @see \Pmb\Thumbnail\Models\Sources\RootThumbnailSource::getImage()
     */
    public function getImage(int $object_id) : string
    {
        if (!$object_id) {
            return '';
        }
        
        $query = "SELECT thumbnail_url FROM notices WHERE notice_id = {$object_id}";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            $thumbnail_url = trim(pmb_mysql_result($result, 0, 0));
            if (!empty($thumbnail_url)) {
                $image = $this->loadImageWithCurl($thumbnail_url);
                if (!empty($image)) {
                    return $image;
                }
                //cas particulier si l'url opac est inaccessible (ex: mypmb)
                if (strpos($thumbnail_url, GlobalContext::get("opac_url_base")) !==  false) {
                    $thumbnail_url = str_replace(GlobalContext::get("opac_url_base"), GlobalContext::get("pmb_url_internal")."opac_css/", $thumbnail_url);
                    $image = $this->loadImageWithCurl($thumbnail_url);
                    if (!empty($image)) {
                        return $image;
                    }
                }
                //image stockee en base64 en base
                $ind = strpos($thumbnail_url, self::BASE64_STR);
                if (!empty($ind)) {
                    return base64_decode(substr($thumbnail_url, $ind + strlen(self::BASE64_STR)));
                }
            }
        }
        
        $rep_id = GlobalContext::get("pmb_notice_img_folder_id");
        $query = "SELECT repertoire_path FROM upload_repertoire WHERE repertoire_id ='".$rep_id."'";
        $result = pmb_mysql_query($query);
        if(pmb_mysql_num_rows($result)){
            $row = pmb_mysql_fetch_array($result,PMB_MYSQL_NUM);
            $thumbnail_path = $row[0]."img_".$object_id;
            if (file_exists($thumbnail_path)) {
                $content = file_get_contents($thumbnail_path);
                if (!empty($content)) {
                    return $content;
                }
            }
        }
        return '';
    }
    
    /**
     * Dérivation de setParameters pour ne plus manipuler un tableau
     * {@inheritDoc}
     * @see \Pmb\Thumbnail\Models\Sources\RootThumbnailSource::setParameters()
     */
    public function setParameters(array $settings) : void
    {
        $this->settings = $settings[0];
    }
}