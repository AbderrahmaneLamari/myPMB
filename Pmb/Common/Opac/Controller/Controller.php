<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Controller.php,v 1.1 2021/03/11 13:41:40 qvarin Exp $

namespace Pmb\Common\Opac\Controller;

class Controller
{

    public $data;

    public function __construct(object $data = null)
    {
        if (empty($data)) {
            $this->data = new \stdClass();
        } else {
            $this->data = $data;
        }
    }

    protected function check_captcha(string $value)
    {
        global $base_path, $msg;
        
        require_once ($base_path . "/includes/securimage/securimage.php");

        $flag = true;
        $message = "";

        // Captcha
        $securimage = new \Securimage();
        if (! $securimage->check($value)) {
            $flag = false;
            $message = $msg['animation_registration_verifcode_mandatory'];
        }

        // Remove random value
        $_SESSION['image_random_value'] = '';

        return array(
            "success" => $flag,
            "message" => $message
        );
    }
}