<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: WatchSortInteresting.php,v 1.1.2.4 2023/11/10 13:51:20 rtigero Exp $
namespace Pmb\DSI\Models\Sort\Entities\ItemWatch\WatchSortInteresting;

use Pmb\DSI\Models\Sort\RootSort;

class WatchSortInteresting extends RootSort
{

	protected $field = "item_interesting";

	protected $fieldType = "string";

	protected $direction;

	public function __construct($data = null)
	{
		$this->type = static::TYPE_QUERY;
		if (in_array($data->direction, static::DIRECTIONS)) {
			$this->direction = $data->direction;
		}
	}
}