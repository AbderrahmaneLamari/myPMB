<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationTypesModel.php,v 1.2 2022/01/18 09:44:24 qvarin Exp $
namespace Pmb\Animations\Models;

use Pmb\Common\Models\Model;
use Pmb\Animations\Orm\AnimationTypesOrm;

class AnimationTypesModel extends Model
{

    protected $ormName = "\Pmb\Animations\Orm\AnimationTypesOrm";

    public $hasAnimations = false;

    public static function getAnimationTypesList(): array
    {
        $animationTypes = AnimationTypesOrm::findAll();
        return self::toArray($animationTypes);
    }

    public static function delete($id)
    {
        if ($id != 1) {
            $animationTypes = AnimationTypesOrm::findById($id);
            $animations = $animationTypes->animations;
            if (empty($animations)) {
                $animationTypes->delete();
                return true;
            }
        }
        return false;
    }

    public static function save(object $data)
    {
        if (! empty($data->id)) {
            $types = new AnimationTypesOrm($data->id);
        } else {
            $types = new AnimationTypesOrm();
        }
        if (! empty($data->label)) {
            $result = AnimationTypesOrm::find('label', $data->label);
            if ((count($result) == 1 && $types->{AnimationTypesOrm::$idTableName} === $result[0]->{AnimationTypesOrm::$idTableName}) || empty($result)) {
                $types->label = $data->label;
                $types->save();
            }
        }
    }

    public function getEditAddData()
    {
        $this->hasAnimations = $this->hasAnimations();
        return $this;
    }

    public function hasAnimations()
    {
        $query = "select 1 from anim_animations where num_type = " . $this->id;
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result) > 0) {
            return true;
        }
        return false;
    }

    public static function checkExistType($label)
    {
        if (! empty(AnimationTypesOrm::find('label', $label))) {
            return true;
        }
        return false;
    }
}