<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ProductStatus.php,v 1.2.4.1 2023/05/10 14:14:47 qvarin Exp $

namespace Pmb\DSI\Models;

class ProductStatus extends Status
{
	protected $ormName = "Pmb\DSI\Orm\ProductStatusOrm";
	public $idProductStatus = 0;
}

