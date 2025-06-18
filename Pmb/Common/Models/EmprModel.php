<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: EmprModel.php,v 1.5 2021/03/19 10:00:56 qvarin Exp $
namespace Pmb\Common\Models;

use Pmb\Common\Orm\EmprOrm;

class EmprModel extends Model
{
    protected $ormName = "\Pmb\Common\Orm\EmprOrm";

    public static function getEmprByCB(string $cb)
    {
        $emprOrmInstances = EmprOrm::find('empr_cb', stripslashes($cb));
        if (!empty($emprOrmInstances)) {
            $id_empr = $emprOrmInstances[0]->id_empr;
            if (!empty($id_empr)) {
                return new EmprModel($id_empr);
            }
        }
        return new EmprModel(0);
    }
}