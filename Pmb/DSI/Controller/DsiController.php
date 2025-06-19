<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DsiController.php,v 1.2.2.2 2023/05/25 13:04:57 qvarin Exp $

namespace Pmb\DSI\Controller;

use Pmb\Common\Controller\Controller;
use Pmb\Common\Helper\Helper;

class DsiController extends Controller
{

	public function proceed()
	{
		$classname = $this->foundController();
		if (!empty($classname) && class_exists($classname)) {
			$controller = new $classname($this->data);
			return $controller->proceed();
		}
		$this->defaultAction();
	}
	
	private function foundController() 
	{
		if (empty($this->data->categ)) {
			return "";
		}
		
		$explode = explode("\\", static::class);
		array_pop($explode);
		$explode[] = Helper::pascalize("{$this->data->categ}_controller");
		return implode("\\", $explode);
	}

	protected function defaultAction()
	{
		global $include_path, $lang;
        switch($this->data->categ) {
            case 'docwatch' :
                include_once("./dsi/docwatch/main.inc.php");
                break;
            case 'fluxrss' :
                include_once('./dsi/rss/main.inc.php');
                break;
            default :
				$filepath = "$include_path/messages/help/$lang/dsi2.txt";
				if (file_exists($filepath)) {			
					include($filepath);
				}
                break;
		}
	}
}

