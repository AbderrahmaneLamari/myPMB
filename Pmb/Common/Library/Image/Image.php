<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Image.php,v 1.3.2.5 2023/12/19 10:27:04 qvarin Exp $

namespace Pmb\Common\Library\Image;

use Pmb\Common\Helper\GlobalContext;
use Pmb\Common\Helper\HTML;

class Image
{
    public const MIMETYPE = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
        "image/webp",
    ];

    public static function isValid(string $image)
    {
        $finfo = new \finfo();
        $mimeType = $finfo->buffer($image, FILEINFO_MIME_TYPE);

        $img = @imagecreatefromstring($image);
        return in_array($mimeType, static::MIMETYPE, true) && !empty($img);
    }

    public static function resize(string $image, ?int $maxSize = null)
    {
        if (empty($image) || !static::isValid($image)) {
            return null;
        }

        if (!$maxSize) {
            $maxSize = GlobalContext::get("notice_img_pics_max_size") ?? (GlobalContext::get("pmb_notice_img_pics_max_size") ?? 150);
        }

        $img = imagecreatefromstring($image);
        if (empty($img)) {
            return null;
        }

        $redim = false;
        if (imagesx($img) >= imagesy($img)) {
            if (imagesx($img) <= $maxSize) {
                $largeur = imagesx($img);
                $hauteur = imagesy($img);
            } else {
                $redim = true;
                $largeur = $maxSize;
                $hauteur = ($largeur * imagesy($img)) / imagesx($img);
            }
        } else {
            if (imagesy($img) <= $maxSize) {
                $hauteur = imagesy($img);
                $largeur = imagesx($img);
            } else {
                $redim = true;
                $hauteur = $maxSize;
                $largeur = ($hauteur * imagesx($img)) / imagesy($img);
            }
        }

        $imgResized = imagecreatetruecolor($largeur, $hauteur);
        $white = imagecolorallocate($imgResized, 255, 255, 255);
        imagefilledrectangle($imgResized, 0, 0, $largeur, $hauteur, $white);
        if ($redim) {
            imagecopyresampled($imgResized, $img, 0, 0, 0, 0, $largeur, $hauteur, imagesx($img), imagesy($img));
        } else {
            imagecopyresampled($imgResized, $img, 0, 0, 0, 0, $largeur, $hauteur, $largeur, $hauteur);
        }

        return $imgResized;
    }

    public static function format(string $image, ?int $maxSize = null, string $watermark = "")
    {
        $image = Image::resize($image, $maxSize);
        if (empty($image)) {
            return null;
        }

        // Copyright
        if ($watermark) {
            $white = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 1, (imagesx($image) / 3), (imagesy($image) / 1.1), $watermark, $white);
        }

        return $image;
    }

    public static function print($image)
    {
        switch (GlobalContext::get("img_cache_type")) {
            case "png":
                return static::printPNG($image);

            case "webp":
            default:
                return static::printWebP($image);
        }
    }

    public static function printPNG($image)
    {
        if (empty($image)) {
            return null;
        }

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        return true;
    }

    public static function printSVG($image)
    {
        if (empty($image)) {
            return null;
        }

        header('Content-Type: image/svg+xml');
        print $image;
        return true;
    }

    public static function printWebP($image)
    {
        if (empty($image)) {
            return null;
        }

        header('Content-Type: image/webp');
        imagewebp($image);
        imagedestroy($image);
        return true;
    }

    /**
     * Recupere les imges pour y ajouter l'attribut loading="lazy"
     *
     * @param string $html
     * @return string
     */
    public static function transformHTML(string $html): string
    {
        $html = HTML::cleanHTML($html);

        $dom = new \DOMDocument();
        if (@$dom->loadHTML($html)) {

            $imgList = $dom->getElementsByTagName("img");
            for ($i = 0; $i < $imgList->length; $i++) {

            	/**
                 * @var \DOMElement $img
                 */
                $img = $imgList->item($i);
                if (!$img->hasAttribute("loading")) {
                    $img->setAttribute("loading", "lazy");
                }
            }

            return $dom->saveHTML();
        }
        return $html;
    }
}
