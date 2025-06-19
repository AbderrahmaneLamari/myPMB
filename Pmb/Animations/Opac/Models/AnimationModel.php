<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationModel.php,v 1.1 2023/02/15 15:05:02 qvarin Exp $

namespace Pmb\Animations\Opac\Models;

use Pmb\Animations\Models\AnimationModel as AnimationModelGestion;
use Pmb\Common\Models\CustomFieldModel;

class AnimationModel extends AnimationModelGestion
{
    public function fetchCustomFields()
    {
        if (! empty($this->customFields)) {
            return $this->customFields;
        }
        $this->customFields = CustomFieldModel::getAllCustomFields('anim_animation', $this->id, true);
        $this->gotCustomFieldsValues = false;
        foreach ($this->customFields as $field) {
            if (! empty($field['customField']['values'])) {
                $this->gotCustomFieldsValues = true;
            }
        }
    }
}