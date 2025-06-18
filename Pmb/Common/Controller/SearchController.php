<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchController.php,v 1.6 2020/09/14 09:03:43 btafforeau Exp $

namespace Pmb\Common\Controller;

use Pmb\Common\Models\SearchModel;

class SearchController
{

    public function proceed($action = "", $data = [])
    {
        switch ($action) {
            case "search":
                return $this->searchAction($data['what'], $data['globalsSearch'], $data['labelId']);
                break;
            default:
                throw new \Exception("action required");
                break;
        }
    }
    
    public function searchAction(string $what, array $globalsSearch, string $labelId)
    {
        switch ($what) {
            case 'animations':
                $searchModel = new SearchModel();
                return $searchModel->makeSearch($globalsSearch, $labelId, 'search_fields_animations');
                break;
            default:
                throw new \Exception("what required");
                break;
        }
    }
}