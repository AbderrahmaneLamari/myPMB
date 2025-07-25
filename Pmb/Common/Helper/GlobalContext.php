<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: GlobalContext.php,v 1.3.4.3 2023/10/11 09:45:18 dbellamy Exp $

namespace Pmb\Common\Helper;

class GlobalContext
{
    /**
     * Recuperation d'une valeur globale
     *
     * @param string $name
     * @return NULL|mixed
     */
    public static function get(string $name)
    {
        global ${$name};
        global ${"pmb_$name"};
        global ${"opac_$name"};

        if (defined('GESTION') && isset(${"pmb_$name"})) {
            return ${"pmb_$name"};
        }
        if (!defined('GESTION') && isset(${"opac_$name"})) {
            return ${"opac_$name"};
        }

        return ${$name} ?? null;
    }

    public static function msg(string $code, string $default = "")
    {
        global $msg;
        return (!empty($msg) && !empty($msg[$code])) ? $msg[$code] : $default;
    }

    public static function charset()
    {
        global $charset;
        return $charset;
    }

    public static function urlBase()
    {
        global $use_opac_url_base;
        global $opac_url_base;

        $use_opac_url_base = intval($use_opac_url_base);
        if ($use_opac_url_base) {
            return $opac_url_base;
        }
        return static::get("url_base");
    }

    public static function imgCacheUrl()
    {
        global $use_opac_url_base;
        global $opac_img_cache_url;

        $use_opac_url_base = intval($use_opac_url_base);
        if ($use_opac_url_base) {
            return $opac_img_cache_url;
        }
        return static::get("img_cache_url");
    }
}

