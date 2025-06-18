<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Helper.php,v 1.6 2021/03/12 13:44:23 jlaurent Exp $

namespace Pmb\Common\Helper;

class Helper
{
    public static function camelize(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $string))));
    }
    
    public static function array_camelize_key(array $array): array
    {
        $new_array = [];
        foreach ($array as $key => $value) {
            $key = lcfirst(str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9\x7f-\xff]++/', ' ', $key))));
            $new_array[$key] = $value;
        }
        return $new_array;
    }
    
    public static function array_camelize_key_recursive(array $array): array
    {
        return array_map(function($item) {
            if (is_array($item)) {
                $item = self::array_camelize_key_recursive($item);
            }
            return $item;
        }, self::array_camelize_key($array));
    }
    
    public static function array_change_key_case_recursive(array $array): array
    {
        return array_map(function($item) {
            if (is_array($item)) {
                $item = self::array_change_key_case_recursive($item);
            }
            return $item;
        }, array_change_key_case($array));
    }
    
    /**
     * Test la validité d'un email
     *
     * @param string $mail
     * @return boolean
     */
    public static function isValidMail(string $mail): string
    {
        $regex = "/(^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\"\.+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$)/";
        return pmb_preg_match($regex, $mail);
    }
    
    /**
     * Test la validité d'un numéro de téléphone
     *
     * @param string $phone
     * @return boolean
     */
    public static function isValidPhone(string $phone): string
    {
        $phoneTemp = preg_replace("/[\W\s]/", '', $phone);
        if (is_numeric($phoneTemp)) {
            return true;
        }
        return false;
    }
    
    /**
     * Récupère les informations de l'utilisateur en Gestion
     * 
     * @param int $id
     * @return array $user
     */
    
    public static function getUser(int $id) {
        $user = array();
        
        $query = "SELECT * FROM users WHERE userid = $id";
        $result = pmb_mysql_query($query);

        if (pmb_mysql_num_rows($result)) {
            $user = pmb_mysql_fetch_assoc($result,0,0);
        }
        
        return $user;
    }
    
    /**
     * Récupère les informations de l'utilisateur en Gestion
     * 
     * @param int $id
     * @return array $user
     */
    
    public static function getUsers() {
        $users = array();
        
        $query = "SELECT * FROM users";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_assoc($result)){
                $users[] = $row;
            }
        }
        return $users;
    }
}