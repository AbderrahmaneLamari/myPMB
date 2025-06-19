<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: LayoutContainerModel.php,v 1.6.4.1 2023/12/18 08:21:38 qvarin Exp $
namespace Pmb\CMS\Models;

class LayoutContainerModel extends LayoutNodeModel
{

    public static $nbInstance = 0;

    /**
     * @var LayoutContainerModel[]
     */
    public static $instances = array();

    /**
     *
     * @var \Pmb\CMS\Semantics\RootSemantic|null
     */
    protected $semantic = null;

    protected $parent = null;

    protected $gabaritId = null;

    public $isHidden = false;

    /**
     *
     * @return \Pmb\CMS\Semantics\RootSemantic|null
     */
    public function getSemantic()
    {
        return $this->semantic;
    }
}