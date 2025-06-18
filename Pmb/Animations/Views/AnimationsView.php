<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationsView.php,v 1.6 2021/03/11 13:41:40 qvarin Exp $

namespace Pmb\Animations\Views;

class AnimationsView
{

    private $name = "";

    protected $data = [];

    protected $path = "./includes/templates/vuejs/";

    protected $distPath = "./javascript/vuejs/";

    /**
     */
    public function __construct(string $name, $data = [], $path = "")
    {
        $this->name = $name;
        $this->data = $data;
        if (! empty($path)) {
            $this->path = $path;
        }
    }

    public function render()
    {
        global $pmb_javascript_office_editor, $javascript_path;
        $content = "";
        if (file_exists($this->path . $this->name . "/" . basename($this->name) . ".html")) {
            $content = file_get_contents($this->path . $this->name . "/" . basename($this->name) . ".html");
        }
        $content .= "<script type='text/javascript'>var \$data = " . \encoding_normalize::json_encode($this->data) . ";</script>";
        $content .= "<script type='text/javascript' src='" . $this->distPath . $this->name . ".js'></script>";
        $content .= "<script type='text/javascript' src='" . $javascript_path . "/tinyMCE_interface.js'></script>";
        $content .= $pmb_javascript_office_editor;
        return $content;
    }
}

