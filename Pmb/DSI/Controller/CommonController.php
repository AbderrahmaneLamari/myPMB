<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CommonController.php,v 1.8.4.5 2023/07/20 07:51:30 rtigero Exp $
namespace Pmb\DSI\Controller;

use Pmb\Common\Views\VueJsView;
use Pmb\Common\Controller\Controller;
use Pmb\Common\Helper\Helper;
use Pmb\Common\Helper\HelperEntities;
use notice;
use emprunteur;
use Pmb\DSI\Models\DSIParserDirectory;
use search;

class CommonController extends Controller
{

    protected const VUE_NAME = "";

    public function proceed()
    {
        $method = Helper::camelize("{$this->data->action}_Action");
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        $this->defaultAction();
    }

    /**
     * Recuperation fil d'Arianne
     *
     * @return string
     */
    protected function getBreadcrumb()
    {
        global $msg;
        return "{$msg['dsi_menu']}";
    }

    /**
     * Generation vue
     *
     * @param array $data
     */
    protected function render(array $data = [])
    {
        global $pmb_url_base, $opac_url_base;
        $vueJsView = new VueJsView(static::VUE_NAME, array_merge(Helper::toArray($this->data), [
            "breadcrumb" => $this->getBreadcrumb(),
            "url_webservice" => $pmb_url_base . "rest.php/dsi/",
            "url_base" => $pmb_url_base,
            "opac_url_base" => $opac_url_base
        ], Helper::toArray($data)));
        print $vueJsView->render();
    }

    /**
	 * R�cup�re les messages d'un module
	 *
	 * @param string $moduleName
	 * @return void
	 */
	public function getAdditionnalMessages(string $moduleName = "")
	{
        $msg = array();
        $parser = DSIParserDirectory::getInstance();
        $catalog = $parser->getCatalog();
        if(! array_key_exists($moduleName, $catalog)) {
            $this->ajaxError("no module");
        }

        foreach($catalog[$moduleName] as $class) {
            $className =  explode("\\", $class);
            $className = $className[count($className) - 1];
            $msg[$className] = $class::getMessages();
        }
		$this->ajaxJsonResponse($msg);
	}
}