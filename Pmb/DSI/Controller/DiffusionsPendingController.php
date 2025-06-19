<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: DiffusionsPendingController.php,v 1.1.2.14 2023/07/28 11:54:16 qvarin Exp $

namespace Pmb\DSI\Controller;

use Pmb\Common\Helper\Helper;
use Pmb\Common\Helper\HelperEntities;
use Pmb\DSI\Models\Channel\RootChannel;
use Pmb\DSI\Models\ContentBuffer;
use Pmb\DSI\Models\Diffusion;
use Pmb\DSI\Models\DiffusionHistory;
use Pmb\DSI\Models\DSIParserDirectory;

class DiffusionsPendingController extends CommonController
{
    protected const VUE_NAME = "dsi/diffusionsPending";

    /**
     *
     * {@inheritDoc}
     * @see \Pmb\DSI\Controller\CommonController::getBreadcrumb()
     */
    protected function getBreadcrumb()
    {
        global $msg;
        return "{$msg['dsi_menu']} {$msg['menu_separator']} {$msg['dsi_sending_pending']}";
    }

    protected function defaultAction()
    {
        $entities = HelperEntities::get_entities_labels();
        array_walk($entities, function (&$item, $key) {
            $item = ["value" => $key, "label" => $item];
        });
        $entities = array_values($entities);

        $channels = [];
        $manifests = DSIParserDirectory::getInstance()->getManifests("Pmb/DSI/Models/Channel/");
        foreach ($manifests as $manifest) {
            $manifest->manually = intval($manifest->manually);
            $message = $manifest->namespace::getMessages();
            $channels[] = [
                "value" => RootChannel::IDS_TYPE[$manifest->namespace],
                "label" => $message['name'],
            ];
        }

        $diffusionInstance = new Diffusion();
        foreach ($diffusionInstance->getList() as $diffusion) {
            $diffusion->init();
        }

        $history = new DiffusionHistory();
        $list = $history->getPendingList();

        print $this->render([
            "list" => $list,
            "subscribers" => [],
            "entities" => $entities,
            "subscriberTypes" => HelperEntities::get_subscriber_entities(),
            "filters" => [
                ["label" => "msg:dsi_tag", "options" => []],
                ["label" => "msg:items_form_type", "options" => $entities],
                ["label" => "msg:diffusion_form_channel", "options" => $channels],
            ],
        ]);
    }

    public function updateHistoryState($state, $idHistory) {
        try {
            $history = new DiffusionHistory($idHistory);
            $history->state($state);
            $this->ajaxJsonResponse(Helper::toArray($history));
        } catch (\InvalidArgumentException $e) {
            $this->ajaxError($e->getMessage());
        }
    }

    public function saveContentBuffer($idHistory, $contentType) {
        $diffusionHistory = new DiffusionHistory($idHistory);
        foreach(Helper::toArray($this->data->data, "") as $key => $content) {
            $diffusionHistory->contentBuffer[$contentType][$key]->modified = true;
            $diffusionHistory->contentBuffer[$contentType][$key]->content = $content["content"];
            $diffusionHistory->saveContentBuffer();
        }

        $this->ajaxJsonResponse(['success' => true]);
    }

    public function resetContentBuffer($idHistory, $contentType) {
        $diffusionHistory = new DiffusionHistory($idHistory);
        $diffusionHistory->contentBuffer[$contentType] = [];

        switch($contentType) {
            case ContentBuffer::CONTENT_TYPES_SUBSCRIBER:
                $diffusionHistory->addContentSubscriberList($diffusionHistory->diffusion->subscriberList);
                break;
            case ContentBuffer::CONTENT_TYPES_ITEM:
                $diffusionHistory->addContentItem($diffusionHistory->diffusion->item);
                break;
            case ContentBuffer::CONTENT_TYPES_VIEW:
                $diffusionHistory->addContentView($diffusionHistory->diffusion->view);
                break;
        }
        $diffusionHistory->saveContentBuffer();
        $this->ajaxJsonResponse($diffusionHistory->contentBuffer[$contentType]);
    }
}
