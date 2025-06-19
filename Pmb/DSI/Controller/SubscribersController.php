<?php
namespace Pmb\DSI\Controller;

use Pmb\DSI\Models\Diffusion;
use Pmb\DSI\Models\SubscriberList\RootSubscriberList;
use Pmb\DSI\Models\SubscriberList\Subscribers\Subscriber;
use Pmb\DSI\Orm\SubscribersDiffusionOrm;

class SubscribersController extends CommonController
{

	public function delete($entityType = "")
	{
		$subscriber = Subscriber::getInstance($entityType, $this->data->id);
		$result = $subscriber->delete();

		if ($result['error']) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}
		$this->ajaxJsonResponse([
			'success' => true
		]);
		exit();
	}

	public function getEntity($entityType = "", $idSubscriber = 0)
	{
		return $this->ajaxJsonResponse(Subscriber::getInstance($entityType, $idSubscriber));
	}

	public function save($entityType, $entityId)
	{
		$this->data->id = intval($this->data->id);
		$subscriber = Subscriber::getInstance($entityType, $this->data->id);
		$subscriber->setFromForm($this->data);
		$subscriber->setEntity($entityId);
		$result = $subscriber->check($this->data);
		if (isset($result['error'])) {
			$this->ajaxError($result['errorMessage']);
			exit();
		}

		
		if (0 == $this->data->id) {
			$subscriber->create();
		} else {
			$subscriber->update();
		}
		$this->ajaxJsonResponse($subscriber);
		exit();
	}
	
	/**
	 * Ajoute les subscribers a partir d'une liste contenant une source
	 *
	 * @param number $idSubscriberList
	 */
	public function importSubscribers(string $entityType, int $idEntity = 0)
	{
		$subscribers = array();
		$error = false;
		if (! empty($this->data->subscribers)) {
			foreach ($this->data->subscribers as $subscriber) {
				$subscriberModel = Subscriber::getInstance($entityType);
				$subscriberModel->setFromForm($subscriber);
				$subscriberModel->setEntity($idEntity);
				$result = $subscriberModel->check($subscriber);
				if (isset($result['error'])) {
					$error = $result; 
					continue;
				}
				$subscriberModel->create();
				$subscribers[] = $subscriberModel;
			}
		}
		if($error && count($subscribers) == 0) {
			$this->ajaxJsonResponse($error);
		}
		$this->ajaxJsonResponse($subscribers);
	}
	
	/**
	 * Desinscrit un abonne issu d'une source
	 * @param string $entityType
	 * @param int $entityId
	 */
	public function unsubscribe(string $entityType, int $entityId)
	{
		$subscriber = Subscriber::getInstance($entityType, $this->data->id);
		$subscriber->setFromForm($this->data);
		$subscriber->setEntity($entityId);
		$subscriber->unsubscribe();
		
		if (0 == $this->data->id) {
			$subscriber->create();
		} else {
			$subscriber->update();
		}
		$this->ajaxJsonResponse($subscriber);
		exit();
	}
	
	/**
	 * Reinscrit un abonne desinscrit
	 * @param string $entityType
	 * @param int $entityId
	 */
	public function subscribe(string $entityType, int $entityId)
	{
		$subscriber = Subscriber::getInstance($entityType, $this->data->id);
		$result = $subscriber->subscribe();

		$this->ajaxJsonResponse($result);
		exit();
	}

	/**
	 * Inscription d'un abonné depuis l'OPAC
	 * @param string $entityType
	 * @param int $entityId
	 */
	public function subscribeFromOpac(string $entityType, int $entityId)
	{
		//On vérifie si on n'est pas sur un réabonnement

		$idSubscriber = 0;
		$searchSubscriber = SubscribersDiffusionOrm::finds([
			"num_diffusion" => $entityId,
			'settings' => [
				"value" => '%"idEmpr":'. $this->data->settings->idEmpr .'%',
				"operator" => "LIKE",
				"inter" => "AND"
				]
		]);
		if(count($searchSubscriber) == 1) {
			$idSubscriber = $searchSubscriber[0]->id_subscriber_diffusion;
		}
		$subscriber = Subscriber::getInstance($entityType, $idSubscriber);
		$subscriber->setFromForm($this->data);
		$subscriber->setEntity($entityId);
		if($idSubscriber == 0){
			//Nouvelle inscription ? Alors on met en manuel
			$subscriber->type = RootSubscriberList::SUBSCRIBER_TYPE_MANUAL;
			$subscriber->create();
		}
		
		$this->ajaxJsonResponse($subscriber->subscribe());
	}

	/**
	 * Désinscription d'un abonné depuis l'OPAC
	 * @param string $entityType
	 * @param int $entityId
	 */
	public function unsubscribeFromOpac(string $entityType, int $entityId)
	{
		$idEmpr = $this->data->settings->idEmpr;
		$diffusion = new Diffusion($entityId);
		$diffusion->fetchSubscriberList();
		//On regarde si l'abonné fait partie de la source
		foreach($diffusion->subscriberList->source->subscribers as $subscriber) {
			if($subscriber->getIdEmpr() == $idEmpr) {
				//Alors on le désinscrit de la source
				$type = RootSubscriberList::SUBSCRIBER_TYPE_SOURCE;
				$subscriber = Subscriber::getInstance($entityType, $this->data->id);
				$subscriber->type = $type;
				$subscriber->setFromForm($this->data);
				$subscriber->setEntity($entityId);
				$subscriber->unsubscribeFromSubscriber();
				//On ajoute donc une entree en base pour desinscrire d'une source
				if (0 == $this->data->id) {
					$subscriber->create();
				} else {
					$subscriber->update();
				}
				$this->ajaxJsonResponse($subscriber);
				exit();
			}
		}
		foreach($diffusion->subscriberList->lists->subscribers as $subscriber) {
			if($subscriber->getIdEmpr() == $idEmpr) {
				//On a récupéré l'entrée en base donc on change les propriétés
				//Pour désinscrire
				$subscriber->unsubscribeFromSubscriber();
				
				if (0 == $subscriber->id) {
					$subscriber->create();
				} else {
					$subscriber->update();
				}
				$this->ajaxJsonResponse($subscriber);
				exit();
			}
		}
	}
	
	/**
	 * Supprime tous les subscribers d'une entite en base
	 */
	public function emptySubscribers()
	{
		$subscriber = Subscriber::getInstance($this->data->entityType);
		$subscriber->setEntity($this->data->entityId);
		$empty = $subscriber->emptySubscribers();
		return $this->ajaxJsonResponse($empty);
	}
}

