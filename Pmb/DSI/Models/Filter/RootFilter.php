<?php
namespace Pmb\DSI\Models\Filter;

use Pmb\DSI\Models\DiffusionHistory;
use Pmb\DSI\Models\Root;
use Pmb\DSI\Orm\DiffusionHistoryOrm;

class RootFilter extends Root
{

	public $data = [];

	public $entityId = 0;

	public static $fields = [];

	public $fieldsValues = null;

	protected function __construct(array $data, int $entityId)
	{
		$this->data = $data;
		$this->entityId = $entityId;
	}

	public function setFieldsValues($fieldsValues)
	{
		$this->fieldsValues = $fieldsValues;
	}

	/**
	 * Doit retourner un tableau du format [["label" => "label", "value" => "value"]]
	 * pour alimenter un sélecteur
	 * A dériver dans les sous classes
	 *
	 * @return array
	 */
	public static function getOptions()
	{
		return array();
	}


	/**
	 * Retourne la date de la derniere diffusion envoyée (statut SENT)
	 * @return \DateTime | null
	 */
    protected function getLastDiffusionDate() : ?\DateTime {
        $params = [
            "num_diffusion" => $this->entityId,
            "state" => DiffusionHistory::SENT
        ];
        $historyDates = DiffusionHistoryOrm::finds($params, "date DESC");
		
		$dateStr = null;
		if(!empty($historyDates)) {
			$dateStr = $historyDates[0]->date;
		}
        
        if(!is_null($dateStr)) {
            return new \DateTime($dateStr);
        }
        return null;
    }

	/**
	 * Permet de tester si un filtre doit s'afficher ou non
	 * @return bool
	 */
	public static function selfCheck()
	{
		return true;
	}
}

