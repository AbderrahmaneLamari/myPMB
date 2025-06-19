<?php
namespace Pmb\DSI\Models\Filter\Entities\Article\ArticleFilterCreatedAfterDiffusion;

use Pmb\DSI\Models\Filter\Entities\Article\ArticleFilter;

class ArticleFilterCreatedAfterDiffusion extends ArticleFilter
{
    public function __construct(array $data, int $entityId = 0)
	{
		parent::__construct($data, $entityId);
	}
    
    public function filter() : array {
        $filteredData = [];

        $lastDiffusionDate = $this->getLastDiffusionDate();

        if(!is_null($lastDiffusionDate)) {
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

