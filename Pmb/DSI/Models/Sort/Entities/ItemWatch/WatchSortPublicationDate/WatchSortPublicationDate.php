<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: WatchSortPublicationDate.php,v 1.1.2.4 2023/11/10 13:51:20 rtigero Exp $
namespace Pmb\DSI\Models\Sort\Entities\ItemWatch\WatchSortPublicationDate;

use Pmb\DSI\Models\Sort\RootSort;

class WatchSortPublicationDate extends RootSort
{

	protected $field = "item_publication_date";

	protected $fieldType = "date";

	protected $direction;

	public function __construct($data = null)
	{
		$this->type = static::TYPE_QUERY;
		if (in_array($data->direction, static::DIRECTIONS)) {
			$this->direction = $data->direction;
		}
	}
}