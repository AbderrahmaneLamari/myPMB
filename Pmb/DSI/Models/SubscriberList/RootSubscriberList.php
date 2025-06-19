<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RootSubscriberList.php,v 1.16.2.16 2023/12/08 09:30:04 jparis Exp $
namespace Pmb\DSI\Models\SubscriberList;

use Pmb\Common\Helper\HelperEntities;
use Pmb\DSI\Models\DSIParserDirectory;
use Pmb\DSI\Models\Root;
use Pmb\DSI\Models\SubscriberList\Subscribers\Subscriber;

class RootSubscriberList extends Root
{

	public const SUBSCRIBER_LIST_TYPE_LOCAL = 1;

	public const SUBSCRIBER_LIST_TYPE_SOURCE = 2;

	public const SUBSCRIBER_TYPE_MANUAL = 1;

	public const SUBSCRIBER_TYPE_SOURCE = 2;

	public const SUBSCRIBER_TYPE_IMPORT = 3;

	protected function __construct()
	{}

	public function check($data)
	{
		return true;
	}

	public static function getDiffusionSubscribers(int $idDiffusion, int $idSubscriberList)
	{
		$subscribers = new \stdClass();
		$source = new SourceSubscriberList($idSubscriberList);
		$subscribers->source = $source;
		$subscribers->lists = new DiffusionSubscriberList($idDiffusion);
		$subscribers->source->filterSource($subscribers->lists);
		$subscribers->nbSubscribers = self::getNbSubscribers($subscribers);
		$subscribers->name = $subscribers->source->name;
		return $subscribers;
	}

	public static function getProductSubscribers(int $idProduct, int $idSubscriberList)
	{
		$subscribers = new \stdClass();
		$source = new SourceSubscriberList($idSubscriberList);
		$subscribers->source = $source;
		$subscribers->lists = new ProductSubscriberList($idProduct);
		$subscribers->source->filterSource($subscribers->lists);
		$subscribers->nbSubscribers = self::getNbSubscribers($subscribers);
		$subscribers->name = $subscribers->source->name;
		return $subscribers;
	}

	public static function getSubscriberList(int $idSubscriberList = 0)
	{
		$subscribers = new \stdClass();
		$source = new SourceSubscriberList($idSubscriberList);
		$subscribers->source = $source;
		$subscribers->lists = $source->getSubscribersFromDatabase();
		$subscribers->source->filterSource($subscribers->lists);
		$subscribers->nbSubscribers = self::getNbSubscribers($subscribers);
		$subscribers->name = $subscribers->source->name;
		return $subscribers;
	}

	public static function getSourceSubscriberList(int $idSubscriberList = 0)
	{
		return new SourceSubscriberList($idSubscriberList);
	}

	/**
	 * Filtre la liste d'abonnes selon le canal passe en parametre
	 *
	 * @param array $requirements
	 *        	Champs necessaires au canal
	 * @param array $subscribers
	 *        	Tableau des abonnes
	 */
	public static function filterList($requirements = [], $subscribers = [])
	{
		$filteredSubscribers = array();
		$requirements = array_keys($requirements);
		foreach ($subscribers as $subscriber) {
			foreach ($requirements as $requirement) {
				if (isset($subscriber->settings->$requirement) && !empty($subscriber->settings->$requirement)) {
					$filteredSubscribers[] = $subscriber;
				}
			}
		}
		return $filteredSubscribers;
	}

	protected function fetchSubscribers()
	{
		$this->subscribers = array();
	}

	/**
	 * Permet de comparer deux abonnes
	 *
	 * @param Subscriber $subscriber
	 * @param Subscriber $otherSubscriber
	 * @return boolean
	 */
	public static function isSameSubscriber(Subscriber $subscriber, Subscriber $otherSubscriber)
	{
		if (($subscriber->name != $otherSubscriber->name)) {
			return false;
		}
		return true;
	}

	/**
	 * Retourne une liste d'abonnes filtree (utilise pour les listes locales ou les sources)
	 */
	public function getSubscribersToSend()
	{
		//Derivate
	}

	/**
	 * Retourne la liste d'abonnes prete pour l'envoi (liste locale + source)
	 */
	public static function getSubscriberListToSend($subscriberList, $requirements = [])
	{
		if (empty($subscriberList) || !is_object($subscriberList)) {
			return [];
		}

		$subscribersLists = [];
		$subscribersSource = [];

		if (isset($subscriberList->lists) && method_exists($subscriberList->lists, "getSubscribersToSend")) {
			$subscribersLists = $subscriberList->lists->getSubscribersToSend();
		}

		if (isset($subscriberList->source) && method_exists($subscriberList->source, "getFormatedSubscribers")) {
			$subscriberList->source->getFormatedSubscribers($subscriberList->lists);
			$subscribersSource = $subscriberList->source->getSubscribersToSend();
		}

		return static::filterList($requirements, array_merge($subscribersLists, $subscribersSource));
	}

	public static function getAllSubscriberLists()
	{
		$list = [];
		$source = new SourceSubscriberList();
		foreach ($source->ormName::findAll() as $element) {
			$list[] = self::getSubscriberList($element->{$source->ormName::$idTableName});
		}
		return $list;
	}

	/**
	 * Retourne le nombre d'abonnes d'une subscriber list
	 *
	 * @param \stdClass $subscriberList
	 * @return int
	 */
	public static function getNbSubscribers($subscriberList = null)
	{
		if (!isset($subscriberList)) {
			return 0;
		}

		$nbSubscribers = 0;
		if ($subscriberList->source && is_array($subscriberList->source->subscribers)) {
			$nbSubscribers += count($subscriberList->source->subscribers);
		}
		if ($subscriberList->lists && is_array($subscriberList->lists->subscribers)) {
			$nbSubscribers += count(array_filter($subscriberList->lists->subscribers, function ($sub) {
				return $sub->updateType == Subscriber::UPDATE_TYPE_SUBSCRIBER;
			}));
		}
		return $nbSubscribers;
	}

	public static function deleteSubscriberList($subscriberList)
	{
		try {
			$subscriberList->source->delete();
			foreach ($subscriberList->lists->subscribers as $subscriber) {
				$subscriber->delete();
			}
		} catch (\Exception $e) {
			return [
				"error" => true,
				"errorMessage" => $e->getMessage()
			];
		}

		return [
			"error" => false
		];
	}

	public static function unsubscribe($entity, int $idEmpr, string $emprType)
	{
		$subscriberList = $entity->subscriberList;
		switch ($emprType) {
			case "pmb":
				//Ici pas de souci si l'emprunteur est deja desinscrit, la source est deja filtree ici
				foreach ($subscriberList->source->subscribers as $subscriber) {
					if ($subscriber->getIdEmpr() == $idEmpr) {
						$subscriberToDb = Subscriber::getInstance(Subscriber::FROM_DIFFUSION);
						$subscriberToDb->setFromForm($subscriber);
						$subscriberToDb->setEntity($entity->id);
						$subscriberToDb->unsubscribeFromSubscriber();
						$subscriberToDb->create();
						return;
					}
				}
				//Si on n'a toujours pas trouvé on regarde dans les listes
				foreach ($subscriberList->lists->subscribers as $subscriber) {
					if ($subscriber->getIdEmpr() == $idEmpr) {
						$subscriber->unsubscribeFromSubscriber();
						$subscriber->update();
						return;
					}
				}
				break;
			case "other":
				foreach ($subscriberList->lists->subscribers as $subscriber) {
					if ($subscriber->getIdSubscriber() == $idEmpr) {
						$subscriberToDb = Subscriber::getInstance(Subscriber::FROM_DIFFUSION, $subscriber->getIdSubscriber());
						//Si l'updateType est different de 0 on est deja desinscrit
						if($subscriber->updateType != Subscriber::UPDATE_TYPE_SUBSCRIBER) {
							return;
						}
						$subscriberToDb->setFromForm($subscriber);
						$subscriberToDb->setEntity($entity->id);
						$subscriberToDb->unsubscribeFromSubscriber();
						$subscriberToDb->update();
						return;
					}
				}
				break;
			default:
				break;
		}
	}
	public static function getSources($id = 0)
	{
		$entity = HelperEntities::get_subscriber_entities()[$id];
		return static::getFormatedManifests("Pmb".DIRECTORY_SEPARATOR."DSI".DIRECTORY_SEPARATOR."Models".DIRECTORY_SEPARATOR."Source".DIRECTORY_SEPARATOR."Subscriber".DIRECTORY_SEPARATOR."Entities".DIRECTORY_SEPARATOR.$entity.DIRECTORY_SEPARATOR);
	}

	protected static function getFormatedManifests($namespace)
	{
		$manifests = DSIParserDirectory::getInstance()->getManifests($namespace);
		foreach ($manifests as $manifest) {
			$message = $manifest->namespace::getMessages();
			$subscriberListTypeList[] = [
				"id" => $manifest->id,
				"namespace" => $manifest->namespace,
				"name" => $message['name']
			];
		}

		return $subscriberListTypeList;
	}

	public static function exportSubscriberList($id)
    {
		$subscriberList = static::getSubscriberList($id);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $subscriberList->name . '.dsi"');
        
        $subscriberList->source->id = 0;
        $subscriberList->source->numModel = 0;
        
        echo serialize(\encoding_normalize::utf8_normalize($subscriberList));
    }
}