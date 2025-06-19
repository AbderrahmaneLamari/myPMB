<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationController.php,v 1.27 2023/02/16 10:05:36 gneveu Exp $

namespace Pmb\Animations\Controller;

use Pmb\Animations\Models\RegistrationModel;
use Pmb\Animations\Views\AnimationsView;
use Pmb\Animations\Models\AnimationModel;
use Pmb\Common\Controller\Controller;
use Pmb\Animations\Models\RegistrationStatusModel;
use Pmb\Common\Models\DocsLocationModel;

class RegistrationController extends Controller
{

    public function proceed($action = "")
    {
        switch ($action) {
            case "add":
                return $this->addRegistrationAction();
            case "save":
                return $this->saveRegistrationAction($this->data);
            case "edit":
                return $this->editRegistrationAction(intval($this->data->id));
            case "delete":
                return $this->deleteRegistrationAction($this->data->id);
            case "validate":
                return $this->validateRegistrationAction(intval($this->data->id));
            case "validateListRegistration":
                return $this->validateListRegistrationAction($this->data->id);
            case "emprList":
                return $this->emprRegistrationsListAction(intval($this->data->emprId));
            case "list":
            default:
                return $this->listAction(intval($this->data->numStatus));
        }
    }

    public function listAction(int $numStatus)
    {
        $animView = new AnimationsView("animations/registration", [
            'registrationList' => RegistrationModel::getRegistrations(),
            'animationList' => AnimationModel::getAnimationsList(true),
            'statusRegistrationlist' => RegistrationStatusModel::getRegistrationStatuses(),
            'localisationList' => DocsLocationModel::getLocationList(),
            'selectedStatusRegistration' => $numStatus,
            'action' => 'list'
        ]);
        print $animView->render();
    }
    
    public function emprRegistrationsListAction(int $emprId)
    {
        $registrationList = RegistrationModel::getEmprRegistrationsList($emprId);
        return $registrationList;
    }

    public function addRegistrationAction()
    {
        global $numAnimation, $numDaughtersAnimation;
        
        $animTemp = new AnimationModel($numAnimation);
        if ($animTemp->checkChildrens() && empty($numDaughtersAnimation)) {
            return $this->listAction();
        }
        
        if (empty($numAnimation) && empty($numDaughtersAnimation)) {
            return $this->listAction();
        }
        
        $numDaughtersAnimation = isset($numDaughtersAnimation) ? $numDaughtersAnimation : '';
        
        $animView = new AnimationsView("animations/registration", [
            "registrationList" => RegistrationModel::getRegistrationList(0, $numAnimation),
            "formData" => RegistrationModel::getFormData(intval($numAnimation), $numDaughtersAnimation),
            'action' => 'add'
        ]);
        print $animView->render();
    }

    public function saveRegistrationAction(object $data)
    {
        if (! empty($data->id)) {
            $registrationId = RegistrationModel::updateRegistration(intval($data->id), $data);
        } else {
            $registrationId = RegistrationModel::addRegistration($data);
        }
        return intval($registrationId);
    }

    public function viewRegistrationAction(int $id)
    {
        $animView = new AnimationsView("animations/registration", [
            'registrationList' => RegistrationModel::getRegistration($id),
            'action' => "view"
        ]);
        print $animView->render();
    }

    public function editRegistrationAction(int $id)
    {
        if ($id == 0) {
            return $this->listAction();
        }
        
        try {
            $registration = new RegistrationModel($id);
        } catch (\Exception $e) {
            return $this->listAction();
        }
        
        $animTemp = new AnimationModel($registration->numAnimation);
        if ($animTemp->checkChildrens()) {
            return $this->listAction();
        }
        
        $animView = new AnimationsView("animations/registration", [
            'registrationList' => RegistrationModel::getRegistrationList($id),
            "formData" => RegistrationModel::getFormData($registration->numAnimation),
            'action' => "edit"
        ]);
        print $animView->render();
    }
    
    public function deleteRegistrationAction($id) {
        $ids = explode(',', $id);
        foreach ($ids as $registrationId) {
            RegistrationModel::deleteRegistration(intval($registrationId));
        }
    }
    
    public function validateRegistrationAction(int $id) {
        $registration = RegistrationModel::validateRegistration($id);
        return $registration;
    }

    public function validateListRegistrationAction($data) {
        $listIds = explode(',', $data);
        foreach ($listIds as $registrationId) {
            RegistrationModel::validateRegistration(intval($registrationId));
        }
    }
}