<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: parameter.class.php,v 1.1.2.1 2023/12/20 10:26:00 gneveu Exp $
if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
    die("no access");

class parameter
{

    public static function update($type_param, $sstype_param, $valeur_param)
    {
        if (empty($type_param) || empty($sstype_param)) {
            return false;
        }

        $varGlobal = $type_param . "_" . $sstype_param;
        global ${$varGlobal};

        if (! isset(${$varGlobal})) {
            return false;
        }

        // on enregistre dans la variable globale
        ${$varGlobal} = $valeur_param;

        // puis dans la base
        $query = "update parametres set valeur_param='" . addslashes($valeur_param) . "' where type_param='" . addslashes($type_param) . "' and sstype_param='" . addslashes($sstype_param) . "'";
        pmb_mysql_query($query);
    }
}

