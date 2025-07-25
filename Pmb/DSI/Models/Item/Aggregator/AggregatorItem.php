<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AggregatorItem.php,v 1.1.2.2 2023/11/15 15:40:21 rtigero Exp $
namespace Pmb\DSI\Models\Item\Aggregator;

use Pmb\DSI\Models\Item\RootItem;
use Pmb\DSI\Models\Item\SimpleItem;

class AggregatorItem extends RootItem
{

	public const TYPE = 0;

	public function getSearchInput()
	{
		$searchInput = "";
		$userModifiedItem = 0;
		//Besoin de tester que pour le parent car le test est d�j� r�cursif
		if (! $this->numParent) {
			$userModifiedItem = $this->getUserModifiedItem();
		}

		if ($userModifiedItem != 0) {
			$child = RootItem::getInstance($userModifiedItem);
			return $child->getSearchInput();
		}
		foreach ($this->childs as $child) {
			$searchInput .= $child->getSearchInput();
		}
		return $searchInput;
	}

	public function getData()
	{
		$data = array();
		foreach ($this->childs as $child) {
			if ($child instanceof SimpleItem) {
				if (! isset($data[$child::TYPE])) {
					$data[$child::TYPE] = array();
				}
				$data[$child::TYPE] = array_merge($data[$child::TYPE], $child->getData());
			} else {
				$results = $child->getData();
				foreach ($results as $type => $result) {
					if (! isset($data[$type])) {
						$data[$type] = array();
					}
					$data[$type] = array_merge($data[$type], $result);
				}
			}
		}
		return $data;
	}

	public function getResults()
	{
		if (empty($this->results)) {
			$this->results = array();
			foreach ($this->childs as $child) {
				if ($child instanceof SimpleItem) {
					if (! isset($this->results[$child::TYPE])) {
						$this->results[$child::TYPE] = array();
					}
					$this->results[$child::TYPE] = array_merge($this->results[$child::TYPE], $child->getResults());
				} else {
					$results = $child->getResults();
					foreach ($results as $type => $result) {
						if (! isset($this->results[$type])) {
							$this->results[$type] = array();
						}
						$this->results[$type] = array_merge($this->results[$type], $result);
					}
				}
			}
		}
		return $this->results;
	}

	public function getNbResults()
	{
		$this->getResults();
		$count = 0;
		foreach ($this->results as $type) {
			$count += count($type);
		}
		return $count;
	}

	protected function getUserModifiedItem()
	{
		foreach ($this->childs as $child) {
			if (! empty($child->settings->userModifiedItem)) {
				return $child->id;
			}
			if (method_exists($child, "getUserModifiedItem")) {
				return $child->getUserModifiedItem();
			}
		}
		return 0;
	}
}

