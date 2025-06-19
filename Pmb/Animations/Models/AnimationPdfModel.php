<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationPdfModel.php,v 1.3.6.1 2023/04/12 07:05:30 gneveu Exp $
namespace Pmb\Animations\Models;

use Pmb\Animations\Orm\RegistrationOrm;

class AnimationPdfModel
{

    public static function renderRegistrationList(int $id)
    {
        global $include_path;

        $template = "";
        $template_path = $include_path . '/templates/animations/printRegistrationList.tpl.html';

        if (file_exists($include_path . '/templates/animations/printRegistrationList_subst.tpl.html')) {
            $template_path = $include_path . '/templates/animations/printRegistrationList_subst.tpl.html';
        }

        if (file_exists($template_path)) {

            $animation = new AnimationModel($id);
            $animation->getFetchAnimation();

            $allQuotas = AnimationModel::getAllQuotas($animation->idAnimation);

            $registrationListOrm = RegistrationOrm::find("num_animation", $animation->idAnimation);
            $registrationList = array();
            foreach ($registrationListOrm as $registrationOrm) {
                $registrationModel = new RegistrationModel($registrationOrm->id_registration);
                $registrationModel->fetchRegistrationListPerson();
                $registrationList[] = $registrationModel;
            }

            $h2o = \H2o_collection::get_instance($template_path);
            $template = $h2o->render(array(
                'animation' => $animation,
                'registrationList' => $registrationList,
                'allQuotas' => $allQuotas,
                'summaryPrice' => $animation->getSummaryPerson()
            ));
        }
        return [
            "template" => $template,
            "title" => $animation->name . " " . $animation->event->startDate,
            "fileName" => strtolower(preg_replace("/\W/", "_", $animation->name . "_" . $animation->event->startDate)) . ".pdf"
        ];
    }
}