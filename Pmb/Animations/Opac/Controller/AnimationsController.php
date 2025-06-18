<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationsController.php,v 1.12 2021/03/26 14:22:14 qvarin Exp $

namespace Pmb\Animations\Opac\Controller;

use Pmb\Common\Opac\Controller\Controller;
use Pmb\Animations\Models\AnimationModel;
use Pmb\Animations\Opac\Views\AnimationsView;
use Pmb\Animations\Opac\Models\RegistrationModel;

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
        
        try {
            $animation = new AnimationModel($id);
            $registration = new RegistrationModel(RegistrationModel::getIdRegistrationFromEmprAndAnimation($emprId, $id));
        } catch (\Exception $e) {
            $animation = new AnimationModel(0);
            $registration = new RegistrationModel(0);
        }
        
        $animation->getViewData();
        if ($animation->hasChildrens) {
            foreach ($animation->childrens as $children) {
                $id = RegistrationModel::getIdRegistrationFromEmprAndAnimation($emprId, $children->id);
                $children->alreadyRegistred = false;
                if ($id != 0) {
                    $children->alreadyRegistred = true;
                }
            }
        }

        $H2o = \H2o_collection::get_instance("$base_path/includes/templates/animations/common/animation_display.tpl.html");
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
            'animations' => ['render' => $animationTemplate]
        ]);
        print $view->render();
    }
    
    public function AnimationSeeAllAction()
    {
        global $base_path;
        
        $H2o = \H2o_collection::get_instance("$base_path/includes/templates/animations/common/animations_list.tpl.html");
        $animationTemplate = $H2o->render([
            'animations' => AnimationModel::getAnimationsList(),
            'formData' => [
                'registrationAllowed' => RegistrationModel::registrationAllowed()
            ]
        ]);
        
        $view = new AnimationsView('animations/animations', [
            'animations' => ['render' => $animationTemplate],
            'action' => "list"
        ]);
        print $view->render();
    }
}