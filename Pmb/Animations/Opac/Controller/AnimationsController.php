<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationsController.php,v 1.16.2.1 2023/09/19 14:26:26 jparis Exp $
namespace Pmb\Animations\Opac\Controller;

use Pmb\Common\Opac\Controller\Controller;
use Pmb\Animations\Opac\Models\AnimationModel;
use Pmb\Animations\Opac\Views\AnimationsView;
use Pmb\Animations\Opac\Models\RegistrationModel;
use Pmb\Animations\Orm\AnimationOrm;

class AnimationsController extends Controller
{

    public function proceed($categ = '')
    {
        switch ($categ) {
            case 'see':
                return $this->AnimationSeeAction($this->data->id, $this->data->empr_id);
            case 'list':
                return $this->AnimationSeeAllAction();
            default:
                return '';
        }
    }

    public function AnimationSeeAction(int $id, int $emprId)
    {
        global $base_path, $pmb_gestion_devise;

        if (AnimationOrm::exist($id)) {
            $animation = new AnimationModel($id);
            $registration = new RegistrationModel(RegistrationModel::getIdRegistrationFromEmprAndAnimation($emprId, $id));
        } else {
            return $this->AnimationSeeAllAction();
        }

        $animation->getViewData();
        if ($animation->hasChildrens) {
            foreach ($animation->childrens as $children) {
                $idRegistration = RegistrationModel::getIdRegistrationFromEmprAndAnimation($emprId, $children->id);
                $children->alreadyRegistred = false;
                if ($idRegistration != 0) {
                    $children->alreadyRegistred = true;
                }
            }
        }

        $template_path = "$base_path/includes/templates/animations/common/animation_display.tpl.html";
        if (file_exists("$base_path/includes/templates/animations/common/animation_display_subst.tpl.html")) {
            $template_path = "$base_path/includes/templates/animations/common/animation_display_subst.tpl.html";
        }
        
        $H2o = \H2o_collection::get_instance($template_path);

        usort($animation->childrens, function ($a, $b) {
            $startDateA = strtotime($a->event->rawStartDate);
            $startDateB = strtotime($b->event->rawStartDate);
            
            return $startDateA - $startDateB;
        });
        
        $animationTemplate = $H2o->render([
            'animation' => $animation,
            'registration' => $registration->getViewData($emprId),
            'formData' => [
                'registrationAllowed' => RegistrationModel::registrationAllowed(),
                'globals' => [
                    'pmbDevise' => html_entity_decode($pmb_gestion_devise)
                ]
            ]
        ]);

        $view = new AnimationsView('animations/animations', [
            'animations' => [
                'render' => $animationTemplate
            ]
        ]);
        print $view->render();
    }

    public function AnimationSeeAllAction()
    {
        global $base_path;

        $template_path = "$base_path/includes/templates/animations/common/animations_list.tpl.html";
        if (file_exists("$base_path/includes/templates/animations/common/animations_list_subst.tpl.html")) {
            $template_path = "$base_path/includes/templates/animations/common/animations_list_subst.tpl.html";
        }

        $H2o = \H2o_collection::get_instance($template_path);
        $animationTemplate = $H2o->render([
            'animations' => AnimationModel::getAnimationsList(),
            'formData' => [
                'registrationAllowed' => RegistrationModel::registrationAllowed()
            ]
        ]);

        $view = new AnimationsView('animations/animations', [
            'animations' => [
                'render' => $animationTemplate
            ],
            'action' => "list"
        ]);
        print $view->render();
    }
}