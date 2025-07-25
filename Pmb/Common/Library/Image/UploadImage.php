<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: UploadImage.php,v 1.1 2023/02/01 16:03:22 qvarin Exp $
namespace Pmb\Common\Library\Image;

class UploadImage
{

    /**
     * Extension autorisee pour l'upload
     *
     * @var array
     */
    public const ALLOWED_EXT = [
        'png',
        'jpeg',
        'jpg'
    ];

    /**
     * Correspond a une image creee par imagecreatefromstring
     *
     * @see https://www.php.net/manual/en/function.imagecreatefromstring
     * @var resource
     */
    protected $image;

    /**
     * Dossier de destination
     *
     * @var string
     */
    protected $dir;

    /**
     * Nom du fichier
     *
     * @var string
     */
    protected $filename;

    /**
     * Extension du fichier (calculer en fonction du binaire)
     *
     * @var string
     */
    protected $extension;

    /**
     *
     * @param string $dir
     * @param string $filename
     */
    public function __construct(string $dir, string $filename)
    {
        if (! is_dir($dir) || ! is_writable($dir)) {
            throw new \Exception("Not a directory or writable !");
        }
        $this->dir = $dir;
        $this->filename = $filename;
    }

    /**
     * Permet de charger une image binaire
     *
     * @param string $image
     * @return bool
     */
    public function setImageString(string $image)
    {
        $this->findExtension($image);
        $this->image = @imagecreatefromstring($image);
        if (! $this->image || ! $this->isValidImage()) {
            throw new \Exception("Not a image !");
        }

        return $this->encodeImage();
    }

    /**
     * Permet de trouver et definir l'extension du fichier en fonction du binaire
     *
     * @param string $image
     */
    protected function findExtension(string $image)
    {
        $finfo = new \finfo();
        $mimeType = $finfo->buffer($image, FILEINFO_MIME_TYPE);
        $this->extension = substr($mimeType, strrpos($mimeType, '/') + 1);
    }

    /**
     * Permet de charger une image a partir d'un fichier puis le supprime
     *
     * @param string $image
     * @return bool
     */
    public function setImagePath(string $image)
    {
        $content = file_get_contents($image);
        if (! $content) {
            throw new \Exception("Not a image !");
        }

        if ($this->setImageString($content)) {
            // suppression du fichier temporaire
            return unlink($image);
        }
        return false;
    }

    /**
     * Permet de verifier la validite de l'image
     *
     * @return bool
     */
    public function isValidImage()
    {
        $ext = substr($this->filename, strrpos($this->filename, '.') + 1);
        if (! in_array(strtolower($ext), UploadImage::ALLOWED_EXT)) {
            return false;
        }
        if (! in_array(strtolower($this->extension), UploadImage::ALLOWED_EXT)) {
            return false;
        }
        return true;
    }

    /**
     * Supprime toutes les metadonnees, en reencodant l'image
     *
     * @return bool
     */
    protected function encodeImage()
    {
        $success = false;
        if ($this->extension == 'jpeg') {
            $img = imagecreatefromjpeg($this->dir);
            $success = imagejpeg($img, $this->image, 100);
        } else {
            $img = imagecreatefrompng($this->dir);
            $success = imagepng($img, $this->image, 9);
        }
        imagedestroy($img);
        return $success;
    }

    /**
     * Permet de deplacer le fichier charge dans le dossier de destination
     *
     * @return bool
     */
    public function moveImage()
    {
        $newFile = $this->dir . DIRECTORY_SEPARATOR . $this->filename;
        if ($this->extension == 'jpeg') {
            imagejpeg($this->image, $newFile);
        } else {
            imagepng($this->image, $newFile);
        }
        return file_exists($newFile);
    }
}