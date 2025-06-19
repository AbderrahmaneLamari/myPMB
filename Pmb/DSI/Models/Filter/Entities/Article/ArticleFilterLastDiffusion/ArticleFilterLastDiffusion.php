<?php
namespace Pmb\DSI\Models\Filter\Entities\Article\ArticleFilterLastDiffusion;

use Pmb\DSI\Models\Filter\Entities\Article\ArticleFilter;

class ArticleFilterLastDiffusion extends ArticleFilter
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

        if(!is_null($lastDiffusionDate)) {
            $nbDays = new \DateInterval("P". $this->fieldsValues->field_nb_days ."D");
            $lastDiffusionDate->add($nbDays);

            foreach ($this->data as $id => $item) {
                $article = new \cms_article($id);

                $date = \DateTime::createFromFormat("Y-m-d H:i:s", $article->create_date);

                if($date->format("U") > $lastDiffusionDate->format("U")) {
                    $filteredData[$id] = $item;
                }
            }
        }

        return $filteredData;
    }
}