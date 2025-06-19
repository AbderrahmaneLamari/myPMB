<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: NavbarView.php,v 1.4 2022/11/09 09:43:43 qvarin Exp $
namespace Pmb\Common\Views;

use H2o_collection;

class NavbarView
{

    const TEMPLATE_PATH = "./includes/templates/navbar/";

    const CLASSIC = "navbar";

    const PAGINATOR = "paginator";

    const PAGINATOR_PERIO = "paginatorPerio";

    const NB_PER_PAGE_SELECTOR = "selector";

    const DISTANCE = 5;

    protected $page;

    protected $distance = self::DISTANCE;

    protected $total;

    protected $nbPerPage;

    protected $url;

    protected $nbPerPageCustomUrl;

    protected $action;

    protected $script;

    protected $infos = [];

    protected $customs = [];
    
    protected $initialized = false;

    protected $onsubmit = "return test_form(form)";

    public function __construct($page, $total, $nb_per_page, $url, $nb_per_page_custom_url = '', $action = '')
    {
        $this->page = intval($page);
        $this->total = intval($total);
        $this->nbPerPage = intval($nb_per_page);
        $this->url = preg_replace("/(page=)\d+/", "$1!!page!!", $url);
        $this->nbPerPageCustomUrl = $nb_per_page_custom_url;
        $this->action = $action;
    }

    public function setCustoms(string $customs)
    {
        $this->customs = explode(',', trim($customs));
    }

    public function setDistance(int $d)
    {
        $this->distance = $d;
    }

    protected function init()
    {
        if($this->initialized){
            return;
        }
        global $script_test_form, $msg;
        $this->nbPages = ceil($this->total / $this->nbPerPage);
        $start = $this->page - $this->distance;
        if ($start < 1) {
            $start = 1;
        }
        $end = $this->page + $this->distance;
        if ($end > $this->nbPages) {
            $end = $this->nbPages;
        }
        for ($i = $start; $i <= $end; $i ++) {
            $this->pages[] = $i;
        }
        $customUrl = str_replace("!!page!!", "1", $this->url);
        if (strpos($customUrl, 'javascript:') !== false) {
            $customUrl = $this->nbPerPageCustomUrl . ";" . $customUrl;
        } else {
            $customUrl = 'javascript:document.location="'.$customUrl . $this->nbPerPageCustomUrl.'"';
        }
        $this->script = str_replace("!!tests!!", test_field_value_comp('form', 'page', GREATER, $this->nbPages, $msg["page_too_high"]) . "\n" . test_field_value_comp('form', 'page', LESSER, 1, $msg["page_too_low"]), $script_test_form);

        if ($this->action == "") {
            $this->action = $this->url;
            $this->action = str_replace("&page=!!page!!", "", $this->action);
            $this->action = str_replace("page=!!page!!&", "", $this->action);
            $this->action = str_replace("page=!!page!!", "", $this->action);
        }
        $last = ((($this->page - 1) * $this->nbPerPage) + $this->nbPerPage);
        if($last > $this->total){
            $last = $this->total;
        }
        $this->infos = [
            'current' => [
                'page' => $this->page,
                'nbPerPage' => $this->nbPerPage,
                'previous' => $this->page - 1,
                'next' => $this->page + 1,
                'elems' => [
                    'first' => ((($this->page - 1) * $this->nbPerPage) + 1),
                    'last' => $last
                ]
            ],
            'total' => $this->total,
            'nbPages' => $this->nbPages,
            'url' => $this->url,
            'action' => $this->action,
            'script' => $this->script,
            'pages' => $this->pages,
            'onsubmit' => $this->onsubmit,
            'custom' => [
                'url' => $customUrl,
                'customs' => $this->customs
            ]
        ];
        $this->initialized=true;
    }

    public function render($what = self::CLASSIC)
    {
        $this->init();
        
        $filename = self::TEMPLATE_PATH . $what . "_subst.html";
        if (!is_file($filename)) {
	        $filename = self::TEMPLATE_PATH . $what . ".html";
        }
        
        $tpl = H2o_collection::get_instance($filename);
        return $tpl->render($this->infos);
    }
    
    public function getNavigator(){
        return $this->render();
    }
    
    public function getPaginator()
    {
        return $this->render(self::PAGINATOR);
    } 

    public function getPaginatorPerio()
    {
        return $this->render(self::PAGINATOR_PERIO);
    } 

    public function getNbPerPageSelector()
    {
        return $this->render(self::NB_PER_PAGE_SELECTOR);
    }
    
    public function setOnsubmit($onsubmit)
    {
        $this->onsubmit = $onsubmit;
    }
}

