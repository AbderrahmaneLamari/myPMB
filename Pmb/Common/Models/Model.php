<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Model.php,v 1.17 2021/03/26 08:51:56 qvarin Exp $

namespace Pmb\Common\Models;

use Pmb\Common\Helper\Helper;

class Model
{
    public $id = 0;
    
    protected $ormName = "";
    
    protected $structure = [];
    
    public function __construct(int $id = 0)
    {
        $this->id = $id;
        $this->fetchData();
    }
    
    protected function fetchData()
    {
        if (!empty($this->ormName)) {
            $orm = $this->ormName::findById($this->id);
            
            $reflect = new \ReflectionClass($orm);
            $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
            
            foreach ($props as $prop) {
                if (!$prop->isStatic()) {
                    if (!method_exists($this, Helper::camelize("fetch_".$prop->getName()))) {
                        $this->structure[] = Helper::camelize($prop->getName());
                        $this->{Helper::camelize($prop->getName())} = $orm->{$prop->getName()};
                    }
                    //exemple d'appel a une methode par defaut du modele
                    //$this->{Helper::camelize($prop->getName())} = $this->{Helper::camelize("get_".$prop->getName())}();
                }
            }
            unset($this->ormName);
        }
    }
    
    public static function toArray($data)
    {
        $result = array();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::toArray($value);
            } elseif (is_object($value) && is_a($value, "\\Pmb\\Common\\Orm\\Orm")) {
                $result[$key] = $value->getInfos();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
    
    protected static function getRelations(array $relations, $object) : array
    {
        $tab_relations = [];
        if (!empty($relations)) {
            foreach ($relations as $property => $relation) {
                $orm = $object->{$property};
                if (!empty($orm)) {
                    if(is_object($orm)) {
                        $tab_relations[$property] = $orm->getInfos();
                        if (is_array($relation) && count($relation)) {
                            $tab_relations[$property] = array_merge($tab_relations[$property],static::getRelations($relation, $orm));
                        }
                    } elseif (is_array($orm)) {
                        for ($i = 0; $i < count($orm); $i++) {
                            $orm[$i] = $orm[$i]->getInfos();
                            if (is_array($relation) && count($relation)) {
                                // $orm[$i] devrait etre un objet dans ce cas :(
                                $tab_relations[$property] = array_merge($tab_relations[$property],static::getRelations($relation, $orm[$i]));
                            }
                        }
                        $tab_relations[$property] = $orm;
                    }
                }
            }
        }
        return $tab_relations;
    }
    
    public function getCmsStructure(string $prefixVar = "", bool $children = false) 
    {
        global $msg;
        
        $cmsStructure = array();
        
        if ($this->structure && !empty($this->structure)) {
            
            if (!$children) {
                $cmsStructure[0]['var'] = $msg['cms_module_common_datasource_main_fields'];
                $cmsStructure[0]['children'] = array();
            }

            foreach ($this->structure as $prop) {
                
                if (is_object($this->{$prop}) || is_array($this->{$prop}) || $this->{$prop} === NULL) {
                    continue;
                }
                
                $var = addslashes($prop);
                if (!empty($prefixVar)) {
                    $var = addslashes($prefixVar.".".$prop);
                }
                
                
                if (!$children) {
                    $length = count($cmsStructure[0]['children']);
                    $cmsStructure[0]['children'][$length]['var'] = $var;
                    $cmsStructure[0]['children'][$length]['desc'] = "";
                } else {
                    $length = count($cmsStructure);
                    $cmsStructure[$length]['var'] = $var;
                    $cmsStructure[$length]['desc'] = "";
                }
                
                $msgVar = str_replace(".", "_", $var);
                switch (true) {
                    case isset($msg['cms_module_common_datasource_desc_'.$msgVar]):
                        $desc = $msg['cms_module_common_datasource_desc_'.$msgVar];
                        break;
                        
                    case isset($msg[$msgVar]):
                        $desc = $msg[$msgVar];
                        break;
                        
                    default:
                        $desc = addslashes($msgVar);
                        break;
                }
                
                if (!$children) {
                    $cmsStructure[0]['children'][$length]['desc'] = $desc;
                } else {
                    $cmsStructure[$length]['desc'] = $desc;
                }
            }
            
            
            
            if (!$children) {
                $reflect = new \ReflectionClass($this);
                $methods = $reflect->getMethods();
                
                foreach ($methods as $method) {
                    if (substr($method->name,0,5) == "fetch") {
                        $prop = $this->{$method->name}();
                        
                        if (!empty($prop)) {
                            $key = strtolower(str_replace("fetch", "", $method->name));
                            
                            $baseVar = "";
                            if (!empty($prefixVar)) {
                                $baseVar = $prefixVar.".";
                            }
                            
                            $length = count($cmsStructure[0]['children']);
                            $cmsStructure[0]['children'][$length]['var'] = addslashes($baseVar.$key);
                            $cmsStructure[0]['children'][$length]['desc'] = "";
                            $cmsStructure[0]['children'][$length]['children'] = array();
                            
                            $baseVar .= $key;
                            $class = $prop;
                            
                            if (is_array($prop)) {
                                $baseVar .= '[i]';
                                $class = $prop[array_key_first($prop)];
                            }
                            
                            if (method_exists($class, "getCmsStructure")) {
                                $cmsStructure[0]['children'][$length]['children'] = $class->getCmsStructure($baseVar, true);
                            }
                            
                            if (empty($cmsStructure[0]['children'][$length]['children'])) {
                                unset($cmsStructure[0]['children'][$length]);
                            }
                        }
                    }
                }
            }
        }
        
        return $cmsStructure;
    }
    
    public function getCmsData()
    {
        $data = array();
        
        if (!empty($this->structure)) {
            foreach ($this->structure as $prop) {
                $data[addslashes($prop)] = $this->{$prop};
            }
        }
        
        $reflect = new \ReflectionClass($this);
        $methods = $reflect->getMethods();
        if (!empty($methods)) {
            foreach ($methods as $method) {
                if (substr($method->name,0,5) == "fetch") {
                    $prop = $this->{$method->name}();
                    if (!empty($prop)) {
                        $key = strtolower(str_replace("fetch", "", $method->name));
                        $data[addslashes($key)] = $prop;
                    }
                }
            }
        }
        
        return $data;
    }
}