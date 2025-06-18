<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: VueJsView.php,v 1.1.2.2 2021/06/11 10:32:28 qvarin Exp $

namespace Pmb\Common\Opac\Views;

use Pmb\Common\Views\VueJsView as ViewVueJs;
use Pmb\Common\Helper\Helper;


class VueJsView extends ViewVueJs
{
    protected $path = "./includes/templates/vuejs/";
    
    protected $distPath = "./includes/javascript/vuejs/";
    
    public function render()
    {
        $this->distPath = $this->distPath;
        $content = "";
        if(file_exists($this->path.$this->name."/".basename($this->name).".html")){
            $content = file_get_contents($this->path.$this->name."/".basename($this->name).".html");
        }
        
        $explodedName = explode('/', $this->name);
        $length = count($explodedName);
        $moduleName = Helper::camelize($explodedName[$length-1]."Data");
        
        $content.= "<script type='text/javascript'>var \$".$moduleName." = ".\encoding_normalize::json_encode($this->data).";</script>";
        $content.= "<script type='text/javascript' src='".$this->distPath.$this->name.".js'></script>";
        return $content;
    }
}

