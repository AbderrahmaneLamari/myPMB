<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: TypesController.php,v 1.2 2022/01/18 09:44:21 qvarin Exp $
namespace Pmb\Animations\Controller;

use Pmb\Common\Views\VueJsView;
use Pmb\Animations\Models\AnimationTypesModel;
use Pmb\Animations\Orm\AnimationTypesOrm;

class TypesController
{

    /**
     *
     * @var string
     */
    public $action = "list";

    public function proceed(string $action = "", $data = null)
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
                return $this->checkExistTypeAction($data->label);
        }
    }

    public function listAction()
    {
        $types = AnimationTypesModel::getAnimationTypesList();
        $newVue = new VueJsView("animations/types", [
            "types" => $types,
            "action" => $this->action
        ]);
        print $newVue->render();
    }

    public function saveAction(object $data)
    {
        return AnimationTypesModel::save($data);
    }

    public function deleteAction(int $id)
    {
        if ($id && 1 != $id) {
            AnimationTypesModel::delete($id);
        }
    }

    public function addAction()
    {
        $this->showForm(new AnimationTypesModel());
    }

    public function editAction(int $id)
    {
        if (!AnimationTypesOrm::exist($id)) {
            $this->action = "list";
            return $this->listAction();
        }

        $types = new AnimationTypesModel($id);
        $this->showForm($types);
    }

    public function checkExistTypeAction(string $label)
    {
        return AnimationTypesModel::checkExistType($label);
    }

    private function showForm(AnimationTypesModel $type)
    {
        $newVue = new VueJsView("animations/types", [
            "types" => $type->getEditAddData(),
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