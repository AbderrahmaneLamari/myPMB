<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: VueJsView.php,v 1.4.2.1 2021/06/11 10:32:29 qvarin Exp $

namespace Pmb\Common\Views;

class VueJsView
{
    protected $name = "";
    
    protected $data = [];
    
    protected $path = "./includes/templates/vuejs/";
    
    protected $distPath = "./javascript/vuejs/";

    public function __construct(string $name, $data = [], $path = "")
    {
        $this->name = $name;
        $this->data = $data;
        if(!empty($path)){
            $this->path = $path;
        }
        
    }
    
    public function render()
    {
        $this->distPath = $this->distPath;
        $content = "";
        if(file_exists($this->path.$this->name."/".basename($this->name).".html")){
            $content = file_get_contents($this->path.$this->name."/".basename($this->name).".html");
        }
        $content.= "<script type='text/javascript'>var \$data = ".\encoding_normalize::json_encode($this->data).";</script>";
        $content.= "<script type='text/javascript' src='".$this->distPath.$this->name.".js'></script>";
        return $content;
    }
}

