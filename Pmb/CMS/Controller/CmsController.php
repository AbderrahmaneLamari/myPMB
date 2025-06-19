<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CmsController.php,v 1.23.4.1 2023/09/12 10:03:42 jparis Exp $
namespace Pmb\CMS\Controller;

use Pmb\CMS\Views\CmsView;
use Pmb\Common\Helper\Portal;
use Pmb\CMS\Models\PortalModel;
use Pmb\CMS\Models\PageModel;
use Pmb\CMS\Models\LayoutModel;
use Pmb\CMS\Semantics\HtmlSemantic;
use Pmb\CMS\Semantics\RootSemantic;
use Pmb\CMS\Models\ConditionModel;

class CmsController
{

    /**
     *
     * @var \cms_modules_parser
     */
    protected $parser;

    public function __construct()
    {
        $this->parser = new \cms_modules_parser();
    }

    public function proceedAction(string $action)
    {
    	if ($action == "clean_cache") {
    		\cms_cache::clean_cache();
    	} elseif ($action == "clean_cache_img") {
    		\cms_cache::clean_cache_img();
    	}
    }

    public function proceed()
    {
        $portal = PortalModel::getPortal();
        
        $vueJsView = new CmsView("portal/build", [
            "gabarits" => ! empty($portal->getGabaritLayouts()) ? $this->formatList($portal->getGabaritLayouts()) : [],
            "pages" => ! empty($portal->getPages()) ? $this->formatList($portal->getPages()) : [],
            "frames" => $portal->getFrameList(),
            "modules" => $this->parser->get_modules_list(),
            "cms_build_info" => $this->generate_cms_build_info(),
            "portal" => [
                "version_num" => $portal->version->id,
            	"types" => Portal::getTypeList([Portal::PAGES['pixel'], Portal::PAGES['result_docnum']]),
                "sub_types" => Portal::getSubTypeList(),
                "database" => LOCATION
            ],
            "portals" => self::formatPortalList(),
        	"semantic" => HtmlSemantic::getSemanticList(),
        	"class_semantic" => RootSemantic::getClassSemanticList(),
        	"conditions" => ConditionModel::getConditionList(),
        ]);
        print $vueJsView->render();
    }

    private function generate_cms_build_info()
    {
        $cms_build_info = array();
        $cms_build_info['input'] = 'index.php';
        $cms_build_info['session'] = [];
        $cms_build_info['post'] = [];
        $cms_build_info['get'] = [];
        $cms_build_info['lvl'] = 'index';
        $cms_build_info['tab'] = null;
        $cms_build_info['log'] = [];
        $cms_build_info['infos_notice'] = null;
        $cms_build_info['infos_expl'] = null;
        $cms_build_info['nb_results_tab'] = null;
        $cms_build_info['search_type_asked'] = '';

        return rawurlencode(serialize(pmb_base64_encode($cms_build_info)));
    }

    /**
     *
     * @param array $list
     * @return string[]
     */
    private function formatList(array $list): array
    {
        $parsedList = array();
        foreach ($list as $element) {
            $serialize = $element->serialize(true);
            if ($element instanceof LayoutModel) {
            	$serialize['layouts_list'] = $element->getLayoutsList();
            }
            if ($element instanceof PageModel) {
            	$serialize['page_layout']['layouts_list'] = $element->getPageLayout()->getLayoutsList();
            }
            if (method_exists($element, "generateTree")) {
            	$serialize['tree'] = [];
            }
            $parsedList[] = $serialize;
        }
        return $parsedList;
    }

    public static function formatPortalList(): array
    {
        global $msg;

        $formattedPortals = [];
        $portals = PortalModel::getPortals();

        foreach($portals as $portal) {
            $versions = [];
            foreach($portal->fetchVersions() as $version) {
                $versions[] = [
                    "id" => $version->id,
                    "name" => $version->name,
                    "last_version_num" => $version->last_version_num,
                    "create_at" => $version->create_at
                ];
            }

            //Tri les versions par la date la plus récente
            usort($versions, function ($a, $b) use ($portal) {
                if ($a['id'] == $portal->version->id) {
                    return -1; // Met la version correspondante en premier
                } elseif ($b['id'] == $portal->version->id) {
                    return 1;
                } else {
                    // Si les IDs ne correspondent pas, trie par date la plus récente
                    $dateA = strtotime($a['create_at']);
                    $dateB = strtotime($b['create_at']);
                    return $dateB - $dateA;
                }
            });

            // Conversion des dates
            foreach ($versions as &$version) {
                $date = strtotime($version['create_at']);
                $dateFr = date($msg['portal_format_date'], $date);
                $version['create_at'] = $dateFr;
            }

            $formattedPortals[] = [
                "id" => $portal->getId(),
                "name" => $portal->getName(),
                "version_num" => $portal->version->id,
                "versions" => $versions
            ];

        }

        return $formattedPortals;
    }
}