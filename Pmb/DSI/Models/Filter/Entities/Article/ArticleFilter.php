<?php
namespace Pmb\DSI\Models\Filter\Entities\Article;

use Pmb\DSI\Models\Filter\RootFilter;

class ArticleFilter extends RootFilter
{
	protected function __construct(array $data, int $entityId)
	{
		parent::__construct($data, $entityId);
	}
}