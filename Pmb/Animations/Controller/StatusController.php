<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: StatusController.php,v 1.5 2021/04/01 15:13:11 qvarin Exp $
namespace Pmb\Animations\Controller;

use Pmb\Common\Views\VueJsView;
use Pmb\Animations\Models\AnimationStatusModel;

class StatusController
{
    
    
    /**
     *
     * @var string
     */
    public $action = "list";

    public function proceed(string $action = "", $data = null )
    {
        global $id;
        $this->action = $action;
        switch ($action) {
            default:
            case "list":
                return $this->listAction();
            case "delete":
                return $this->deleteAction(intval($data->id));
            case "save":
                return $this->saveAction($data);
            case "add":
                return $this->addAction();
            case "edit":
                return $this->editAction($id);
            case "check":
                return $this->checkExistStatusAction($data->label);
        }
    }

    public function listAction()
    {
        $status = AnimationStatusModel::getAnimationStatusList();
        $newVue = new VueJsView("animations/status", [
            "status" => $status,
            "action" => $this->action
        ]);
        print $newVue->render();
    }

    public function saveAction(object $data)
    {
        return AnimationStatusModel::save($data);
    }

    public function deleteAction(int $id)
    {
    	if($id && 1 != $id) {
    	    AnimationStatusModel::delete($id);
    	}
    }

    public function addAction()
    {
        $this->showForm(new AnimationStatusModel());
    }

    public function editAction(int $id)
    {
        if ($id == 0) {
            $this->action = "list";
            return $this->listAction();
        }
        
        try {
            $status = new AnimationStatusModel($id);
        } catch (\Exception $e) {
            $this->action = "list";
            return $this->listAction();
        }
        
        $this->showForm($status);
    }
    
    public function checkExistStatusAction(string $label){
        return AnimationStatusModel::checkExistStatus($label);
    }
    
    private function showForm(AnimationStatusModel $status)
    {
        $newVue = new VueJsView("animations/status", [
            "status" => $status->getEditAddData(),
            "action" => $this->action,
            'img' => [
                'plus' => get_url_icon('plus.gif'),
                'minus' => get_url_icon('minus.gif'),
                'expandAll' => get_url_icon('expand_all'),
                'collapseAll' => get_url_icon('collapse_all'),
                'tick' => get_url_icon('tick.gif'),
                'error' => get_url_icon('error.png'),
                'patience' => get_url_icon('patience.gif'),
                'sort' => get_url_icon('sort.png'),
                'iconeDragNotice' => get_url_icon('icone_drag_notice.png')
            ]
        ]);
        print $newVue->render();
    }
}