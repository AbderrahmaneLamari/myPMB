<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RecordList.php,v 1.5.2.5 2023/12/01 11:29:26 rtigero Exp $
namespace Pmb\DSI\Models\Source\Item\Entities\Record\RecordList;

use Pmb\DSI\Models\Source\Item\ItemSource;

class RecordList extends ItemSource
{

	public $selector = null;

	public function __construct(\stdClass $selectors = null)
	{
		if (! empty($selectors->selector->namespace)) {
			$this->selector = new $selectors->selector->namespace($selectors->selector);
			if (! empty($selectors->limit)) {
				//Limite utilisée pour les DSI privées par exemple
				$this->selector->setLimit($selectors->limit);
			}
		}
	}

	public function getData()
	{
		if ($this->selector) {
			return $this->selector->getData();
		}

		return [];
	}

	public function getResults()
	{
		if ($this->selector) {
			return $this->selector->getResults();
		}

		return [];
	}
}

