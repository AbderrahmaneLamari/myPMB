<?php
namespace Pmb\DSI\Models\Filter\Entities\Record\RecordFilterLastDiffusion;

use Pmb\DSI\Models\Filter\Entities\Record\RecordFilter;

class RecordFilterLastDiffusion extends RecordFilter
{
    public static $fields = [
        "field_nb_days" => [
            "type" => "number",
            "required" => true
        ]
    ];
	public function __construct(array $data, int $entityId = 0)
	{
		parent::__construct($data, $entityId);
	}

    public function filter() : array {
        $filteredData = [];

        $lastDiffusionDate = $this->getLastDiffusionDate();

        //Si on n'a pas de dernière diffusion on renvoie tout
        if(is_null($lastDiffusionDate)) {
            return $this->data;
        }
        $nbDays = new \DateInterval("P". $this->fieldsValues->field_nb_days ."D");
        $lastDiffusionDate->add($nbDays);

        foreach ($this->data as $id => $item) {
            $notice = new \notice($id);
            $date = \DateTime::createFromFormat("d/m/Y H:i:s", $notice->create_date);

            if($date->format("U") > $lastDiffusionDate->format("U")) {
                $filteredData[$id] = $item;
            }
        }

        return $filteredData;
    }
}