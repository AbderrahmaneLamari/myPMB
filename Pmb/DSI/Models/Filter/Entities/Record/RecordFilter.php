<?php
namespace Pmb\DSI\Models\Filter\Entities\Record;

use Pmb\DSI\Models\Filter\RootFilter;

class RecordFilter extends RootFilter
{
	protected function __construct(array $data, int $entityId)
	{
		parent::__construct($data, $entityId);
	}
}

