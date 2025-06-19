<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ElectreThumbnailSource.php,v 1.2.4.2 2023/10/27 13:55:14 tsamson Exp $
namespace Pmb\Thumbnail\Models\Sources\Entities\Common\Electre;

use Pmb\Thumbnail\Models\Sources\RootThumbnailSource;

class ElectreThumbnailSource extends RootThumbnailSource
{
    /**
     * cache non autorise
     * @var boolean
     */
    protected $allowCache = false;
}

