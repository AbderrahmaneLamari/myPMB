<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_animationslist_datasource_animations_by_type.class.php,v 1.1.2.1 2022/01/19 12:53:55 qvarin Exp $
use Pmb\Animations\Orm\AnimationOrm;

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) {
    die("no access");
}

class cms_module_animationslist_datasource_animations_by_type extends cms_module_common_datasource_animations_list
{

    public function __construct($id = 0)
    {
        parent::__construct($id);
        $this->sortable = true;
        $this->limitable = true;
    }

    /*
     * On défini les sélecteurs utilisable pour cette source de donnée
     */
    public function get_available_selectors()
    {
        return array(
            "cms_module_animationslist_selector_type"
        );
    }

    /*
     * Récupération des données de la source...
     */
    public function get_datas()
    {
        $selector = $this->get_selected_selector();
        if (! empty($selector) && $selector->get_value()) {

            $data = array(
                "title" => "",
                "animations" => array()
            );
            $animations = array();
            $num_type = intval($selector->get_value());

            $animationsOrm = AnimationOrm::find("num_type", $num_type);

            $index = count($animationsOrm);
            for ($i = 0; $i < $index; $i ++) {
                $animations[] = $animationsOrm[$i]->id_animation;
            }

            $data['animations'] = $this->filter_datas('animations', $animations);
            if (! count($data['animations'])) {
                return false;
            }

            $data = $this->sort_animations($data['animations']);
            $data['title'] = "";

            return $data;
        }
        return false;
    }
}