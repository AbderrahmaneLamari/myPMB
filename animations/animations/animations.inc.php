<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: animations.inc.php,v 1.7.2.1 2023/03/10 08:54:12 gneveu Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php"))
    die("no access");
    
use Pmb\Animations\Controller\AnimationsController;
use Pmb\Common\Controller\SearchController;
use Pmb\Animations\Models\AnimationModel;

global $action, $data, $image;

$data = encoding_normalize::json_decode(encoding_normalize::utf8_normalize(stripslashes($data)));
if (isset($image) && "undefined" != $image) {
    $data->image = $image;
}

switch ($action) {
    case 'search':
        $searchController = new SearchController();
        $animationIds = $searchController->proceed($action, [
            'globalsSearch' => AnimationModel::getGlobalsSearch($data->searchFields),
            'what' => 'animations',
            'labelId' => 'id_animation'
        ]);
        $data->animationIds = $animationIds;
        $AnimationsController = new AnimationsController($data);
        $result = $AnimationsController->proceed($action);
        break;
    default:
        $AnimationsController = new AnimationsController($data);
        $result = $AnimationsController->proceed($action);
        break;
}

ajax_http_send_response(encoding_normalize::utf8_normalize($result));