<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RecordSortPublicationYear.php,v 1.1.2.3 2023/11/10 13:51:20 rtigero Exp $
namespace Pmb\DSI\Models\Sort\Entities\Record\RecordSortPublicationYear;

use Pmb\DSI\Models\Sort\RootSort;

class RecordSortPublicationYear extends RootSort
{

	protected $field = "year";

	protected $fieldType = "integer";

	protected $direction;

	public function __construct($data = null)
	{
		$this->type = static::TYPE_QUERY;
		if (in_array($data->direction, static::DIRECTIONS)) {
			$this->direction = $data->direction;
		}
	}
}