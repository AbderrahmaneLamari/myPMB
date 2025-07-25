<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationController.php,v 1.11 2023/02/20 14:07:00 qvarin Exp $
namespace Pmb\Animations\Opac\Controller;

use Pmb\Common\Opac\Controller\Controller;
use Pmb\Animations\Opac\Views\AnimationsView;
use Pmb\Animations\Opac\Models\RegistrationModel;
use Pmb\Common\Helper\HashModel;
use Pmb\Animations\Models\AnimationModel;
use Pmb\Animations\Models\RegistredPersonModel;

class RegistrationController extends Controller
{

    public function proceed($action = "")
    {
        switch ($action) {
            case "save":
                return $this->saveRegistrationAction($this->data);
            case "add":
                return $this->addRegistrationAction(intval($this->data->id_animation), intval($this->data->id_empr), $this->data->numDaughtersAnimation);
            case "view":
                return $this->viewRegistrationAction();
            case "list":
                return $this->listRegistrationAction($this->data->empr_id);
            case "delete":
                return $this->deleteRegistrationAction(intval($this->data->id_registration), $this->data->hash, intval($this->data->id_person));
            default:
                return "";
        }
    }

    public function saveRegistrationAction()
    {
        if (!empty($_SESSION['registrationList'])) {
            // Si registrationList est rempli c'est que l'utilisateur est d�j� inscript
            return array("success" => true);
        } else {
            $result = $this->check_captcha($this->data->captcha_code);
            if ($result['success']) {
                return RegistrationModel::addRegistration($this->data);
            } else {
                return $result;
            }
        }
    }

    public function viewRegistrationAction()
    {
        global $pmb_gestion_devise;

        if (empty($_SESSION['registrationList'])) {
            return $this->notAllowedAction();
        } else {
            $ids = $_SESSION['registrationList'];
            unset($_SESSION['registrationList']);
            $animView = new AnimationsView("animations/registration", [
                "action" => "save",
                "formData" => [
                    "registrationList" => RegistrationModel::getRegistrationByList($ids),
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
                    ],
                    'globals' => [
                        'pmbDevise' => html_entity_decode($pmb_gestion_devise)
                    ]
                ]
            ]);
            print $animView->render();
        }
    }

    public function addRegistrationAction(int $id_animation, int $id_empr = 0, string $numDaughtersAnimation = "")
    {
        global $msg;
        
        $animationModel = new AnimationModel($id_animation);
        // Inscription multiple mais aucune animations selectionne, on refuse
        if ($animationModel->checkChildrens() && empty($numDaughtersAnimation)) {
            print $msg['not_allowed'];
            print "<script>window.location='./index.php?lvl=animation_see&id=".intval($animationModel->id)."'</script>";
            return;
        }
        
        $formData = RegistrationModel::getFormData($id_animation, $numDaughtersAnimation);
        $formData['idEmpr'] = $id_empr;
        
        if (!empty($id_empr) && !empty($formData['listDaughters'])) {
            foreach ($formData['listDaughters'] as $animation) {
                $animation->emprAlreadyRegistred($id_empr);
            }
        }

        $animView = new AnimationsView("animations/registration", [
            "registration" => RegistrationModel::getNewRegistration($id_animation, $id_empr),
            "formData" => $formData,
            "action" => "add"
        ]);
        $animView->use_captcha();
        print $animView->render();
    }

    public function listRegistrationAction(int $emprId)
    {
        $view = new AnimationsView('animations/empr', [
            'registrations' => RegistrationModel::getEmprRegistrationsList($emprId),
            'action' => 'list'
        ]);
        print $view->render();
    }

    public function deleteRegistrationAction(int $idRegistration, string $hash, int $idPerson = 0)
    {
        try {
            $registrationModel = new RegistrationModel($idRegistration);
        } catch (\Exception $e) {
            // On n'as r�ussi � r�cup�rer l'inscription
            return $this->notAllowedAction();
        }

        $param = $registrationModel->idRegistration.$registrationModel->date.$registrationModel->numAnimation;

        $hashModel = new HashModel();

        // Le hash est valide et correspond bien � l'inscription
        if (false === $hashModel->verifeHash($hash, $param) && $registrationModel->hash !== $hash) {
            return $this->notAllowedAction();
        }

        try {
            $registredPersonModel = new RegistredPersonModel($idPerson);
        } catch (\Exception $e) {
            // On n'as r�ussi � r�cup�rer la personne inscrite
            return $this->notAllowedAction();
        }

        $isContact = false;
        // La personne de contact n'est pas forcement inscripte pour l'animation
        if (empty($idPerson) || ($registrationModel->numEmpr === $registredPersonModel->numEmpr)) {
            // si c'est la personne de contact qui ce d�sinscrit on supprime tout
            $isContact = true;
        }

        if (empty($registrationModel->idRegistration)) {
            return $this->notAllowedAction();
        }

        $registrationModel->delete($isContact, $idPerson);
        $view = new AnimationsView('animations/registration', [
            'action' => 'delete',
            "formData" => array(
                'animation' => new AnimationModel($registrationModel->numAnimation)
            )
        ]);
        print $view->render();
    }

    protected function notAllowedAction()
    {
        global $msg;

        print $msg['not_allowed'];
        print "<script>window.location='./index.php'</script>";
        return false;
    }
}