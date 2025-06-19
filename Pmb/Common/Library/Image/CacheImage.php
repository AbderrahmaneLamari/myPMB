<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CacheImage.php,v 1.3.2.7 2023/12/19 10:27:04 qvarin Exp $
namespace Pmb\Common\Library\Image;

use Pmb\Common\Helper\GlobalContext;

abstract class CacheImage
{

    /**
     * Permet de supprimer une image du repertoire de cache
     *
     * @param string $filename
     * @return bool
     */
    public static function delete(string $filename): bool
    {
        return self::enabled() ? unlink(GlobalContext::get("img_cache_folder") . $filename) : false;
    }

    /**
     * Permet de recuperer une image du repertoire de cache
     *
     * @param string $filename
     * @param bool $needResoure
     * @return NULL|string|resource
     */
    public static function fetch(string $filename, bool $needResoure = true)
    {
        if (self::exists($filename)) {
            $img = file_get_contents(GlobalContext::get("img_cache_folder") . $filename);
            return $needResoure ? imagecreatefromstring($img) : $img;
        }
        return null;
    }

    /**
     * Permet de savoir si une image est existante dans le repertoire de cache
     *
     * @param string $filename
     * @return bool
     */
    public static function exists(string $filename): bool
    {
        return self::enabled() ? is_file(GlobalContext::get("img_cache_folder") . $filename) : false;
    }

    /**
     * Permet de savoir si le repertoire de cache est configure
     *
     * @return bool
     */
    public static function enabled(): bool
    {
        return ! empty(GlobalContext::get("img_cache_folder")) && is_dir(GlobalContext::get("img_cache_folder"));
    }

    /**
     * Genere l'URL d'accès pour une image en cache si le parametre img_cache_url est configure
     * @param string $filename
     * @return string
     */
    public static function generateUrl(string $filename) : string
    {
        if (self::exists($filename) && ! empty(GlobalContext::imgCacheUrl())) {
            return GlobalContext::imgCacheUrl() . $filename;
        }
        return "";
    }

	/**
     * Genere l'URL absolue pour une image en cache si le parametre img_cache_folder est configure
     *
     * @return string|null
     */
    public static function generateAbsoluteUrl(string $filename)
    {
        if (self::exists($filename)) {
            return GlobalContext::get("img_cache_folder") . $filename;
        }
        return null;
    }

    /**
     * Permet de ajoute une image dans le repertoire de cache
     *
     * @param string $filename
     * @param resource $image
     * @return bool
     */
    public static function add(string $filename, $image) : bool
    {
        global $use_opac_url_base;

        if (self::enabled() && !empty($image)) {
            switch (GlobalContext::get("img_cache_type")) {
                case "png" :
                    return imagepng($image, GlobalContext::get("img_cache_folder") . $filename) === true;
                default :
                    // On force le PNG dans un contexte extérieur
                    if($use_opac_url_base) {
                    	return imagepng($image, GlobalContext::get("img_cache_folder") . $filename) === true;
                    }
                    return imagewebp($image, GlobalContext::get("img_cache_folder") . $filename) === true;
            }
        }
        return false;
    }

    /**
     * Permet de supprimer les toutes images du repertoire de cache
     *
     * @return bool
     */
    public static function clearCache(): bool
    {
        if (! self::enabled()) {
            return false;
        }
        $resources = glob(GlobalContext::get("img_cache_folder") . LOCATION."*");
        $success = true;
        foreach ($resources as $resource) {
            if (is_dir($resource)) {
                continue;
            }
            $success &= unlink($resource);
        }
        return $success;
    }

    /**
     * Genere le nom du fichier en cache
     *
     * @param string $entityType
     * @param int $objectId
     * @return string
     */
    public static function generateFilename(string $entityType, int $objectId) : string
    {
        global $use_opac_url_base;

        $extension = "webp";
        switch (GlobalContext::get("img_cache_type")) {
            case "png" :
                $extension = "png";
                break;
            case "webp" :
            default :
                $extension = "webp";
                break;
        }
        // On force le PNG dans un contexte extérieur
        if($use_opac_url_base) {
            $extension = "png";
        }
        return LOCATION ."_". $entityType ."_". $objectId . ".".$extension;
    }
}