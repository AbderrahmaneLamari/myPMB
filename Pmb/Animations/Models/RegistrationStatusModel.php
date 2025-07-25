<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationStatusModel.php,v 1.8.6.1 2023/11/14 09:39:54 gneveu Exp $
namespace Pmb\Animations\Models;

use Pmb\Common\Models\Model;
use Pmb\Animations\Orm\RegistrationStatusOrm;

class RegistrationStatusModel extends Model
{

    protected $ormName = "\Pmb\Animations\Orm\RegistrationStatusOrm";

    public static function getRegistrationStatuses()
    {
        $registrationStatuses = RegistrationStatusOrm::findAll();
        return self::toArray($registrationStatuses);
    }

    public static function getRegistrationStatus(int $id)
    {
        $registrationStatus = new RegistrationStatusOrm($id);
        return $registrationStatus->toArray();
    }

    public static function deleteRegistration(int $id)
    {
        $registrationStatus = new RegistrationStatusOrm($id);
        $registrationStatus->delete();
    }

    public static function addRegistration(object $data)
    {
        $registrationStatus = new RegistrationStatusOrm();
        if (empty($data->name)) {
            return false;
        }

        $registrationStatus->name = $data->name;

        $registrationStatus->save();
        return $registrationStatus->toArray();
    }

    public static function updateRegistration(int $id, object $data)
    {
        $registrationStatus = new RegistrationStatusOrm($id);

        if (! empty($data->name)) {
            $registrationStatus->name = $data->name;
        }
        $registrationStatus->save();
    }

    public function fetchRegistration()
    {
        if ($this->registration) {
            return $this->registration;
        }
        $this->registration = array();

        $registrationStatusOrm = new RegistrationStatusOrm($this->id);
        foreach ($registrationStatusOrm->registration as $registration) {
            $this->registration[] = new RegistrationModel($registration->id_registration);
        }
        return $this->registration;
    }
}