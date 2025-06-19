<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: Product.php,v 1.12.2.4 2023/04/04 14:20:44 jparis Exp $
namespace Pmb\DSI\Models;

use Pmb\Common\Helper\GlobalContext;
use Pmb\DSI\Models\Root;
use Pmb\Common\Helper\Helper;
use Pmb\DSI\Models\SubscriberList\RootSubscriberList;
use Pmb\DSI\Orm\EventProductOrm;
use Pmb\DSI\Models\Event\RootEvent;

class Product extends Root implements CRUD
{

	public const TAG_TYPE = 7;

	protected $ormName = "Pmb\DSI\Orm\ProductOrm";

	public $name = "";
	public $settings = "";
	public $status = "";

	public $subscriberList = null;
	public $numSubscriberList = 0;
	public $tags = null;

	public $diffusions = null;
	
	protected $subscribers = null;
	protected $idProduct = 0;
	protected $numStatus = 0;
	protected $events = null;

	public $productDiffusions = array();

	protected $lastDiffusion;

	public function __construct(int $id = 0)
	{
		$this->id = $id;
		$this->read();
	}

	public function create()
	{
		$orm = new $this->ormName();
		$orm->name = $this->name;
		$orm->settings = json_encode($this->settings);
		$orm->num_status = $this->status;
		$orm->num_subscriber_list = isset($this->numSubscriberList) ? $this->numSubscriberList : 0;
		$orm->save();
		$this->id = $orm->{$this->ormName::$idTableName};
		$this->{Helper::camelize($this->ormName::$idTableName)} = $orm->{$this->ormName::$idTableName};
	}

	public function check(object $data)
	{
		if (empty($data->name) || ! is_string($data->name)) {
			return [
				'error' => true,
				'errorMessage' => 'msg:data_errors'
			];
		}

		$fields = [
			'name' => $data->name
		];
		if (! empty($data->id)) {
			$fields[$this->ormName::$idTableName] = [
				'value' => $data->id,
				'operator' => '!='
			];
		}

		$result = $this->ormName::finds($fields);
		if (! empty($result)) {
			return [
				'error' => true,
				'errorMessage' => 'msg:product_duplicated'
			];
		}

		return [
			'error' => false,
			'errorMessage' => ''
		];
	}

	public function setFromForm(object $data)
	{
		$this->name = $data->name;
		$this->settings = $data->settings;
		$this->status = intval($data->numStatus);
		$this->numSubscriberList = $data->numSubscriberList;
	}

	public function read()
	{
		$this->fetchData();
		$this->fetchRelations();
		$this->tags = $this->getEntityTags();
	}

	public function update()
	{
		$orm = new $this->ormName($this->id);
		$orm->name = $this->name;
		$orm->settings = json_encode($this->settings);
		$orm->num_status = $this->status;
		$orm->num_subscriber_list = $this->numSubscriberList;
		$orm->save();
	}

	public function delete()
	{
		try {
			$orm = new $this->ormName($this->id);

			//Suppression des liens
			if($orm->num_subscriber_list != 0) {
		        $subscriberList = RootSubscriberList::getProductSubscribers($this->id, $orm->num_subscriber_list);
		        $subscriberList->lists->delete();
			}

			foreach ($orm->events as $productEvent) {
			    $event = RootEvent::getInstance($productEvent->num_event);
			    $event->delete();				    
			}

			$this->removeEntityTags();
			
			$orm->delete();
		} catch (\Exception $e) {
			return [
				'error' => true,
				'errorMessage' => $e->getMessage()
			];
		}

		$this->id = 0;
		$this->{Helper::camelize($orm::$idTableName)} = 0;
		$this->settings = "";
		$this->name = "";
		$this->status = "";
		$this->tags = null;
		$this->productDiffusions = array();

		return [
			'error' => false,
			'errorMessage' => ""
		];
	}

	public function fetchRelations() {
		$this->fetchSubscriberList();
		$this->fetchEvents();
		$this->fetchDiffusions();
	}

	protected function fetchSubscriberList() 
	{
		$this->subscriberList = RootSubscriberList::getProductSubscribers($this->id, $this->numSubscriberList);
	}
	
	protected function fetchEvents()
	{
	    $events = EventProductOrm::finds(["num_product" => $this->id]);
	    $this->events = array();
	    foreach($events as $event) {
	        $this->events[] = RootEvent::getInstance($event->num_event);
	    }
	}

	protected function fetchDiffusions()
	{
		$this->diffusions = array();

		foreach($this->productDiffusions as $productDiffusion) {
			$this->diffusions[] = new Diffusion($productDiffusion->num_diffusion);
		}
	}

	public function getLastDiffusion()
	{
		$productDiffusions = end($this->productDiffusions);

		if(empty($productDiffusions) || !$productDiffusions) {
			$this->lastDiffusion = GlobalContext::msg('dsi_diffusion_never_send');
			return $this->lastDiffusion;
		}

		$timestamp = strtotime($productDiffusions->last_diffusion);
		if($timestamp < 0) {
			$this->lastDiffusion = GlobalContext::msg('dsi_diffusion_never_send');
			return $this->lastDiffusion;
		}

		$date = new \DateTime($productDiffusions->last_diffusion);

		$this->lastDiffusion = $date->format(GlobalContext::msg('dsi_format_date'));
		return $this->lastDiffusion;
	}
}
