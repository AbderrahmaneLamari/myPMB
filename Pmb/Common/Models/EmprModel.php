<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: EmprModel.php,v 1.6.2.1 2023/09/21 13:59:34 gneveu Exp $
namespace Pmb\Common\Models;

use Pmb\Common\Orm\EmprOrm;

class EmprModel extends Model
{

    protected $ormName = "\Pmb\Common\Orm\EmprOrm";

    public static function getEmprByCB(string $cb)
    {
        $emprOrmInstances = EmprOrm::find('empr_cb', stripslashes($cb));
        if (! empty($emprOrmInstances)) {
            $id_empr = $emprOrmInstances[0]->id_empr;
            if (! empty($id_empr)) {
                return new EmprModel($id_empr);
            }
        }
        return new EmprModel(0);
    }

    public static function ValidBarcode(string $barcode)
    {
        if (empty($barcode)) {
            return false;
        }

        $emprOrmInstances = EmprOrm::find('empr_cb', stripslashes($barcode));
        return ! empty($emprOrmInstances);
    }

    public static function getBarcode($idEmpr)
    {
        $empr = EmprOrm::find('id_empr', $idEmpr);
        return $empr[0]->empr_cb;
    }
}