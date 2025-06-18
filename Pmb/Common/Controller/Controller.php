<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Controller.php,v 1.2 2021/03/11 09:54:57 qvarin Exp $

namespace Pmb\Common\Controller;

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
}