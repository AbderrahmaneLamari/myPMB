<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ArticleListItem.php,v 1.1.2.7 2023/11/28 11:37:33 rtigero Exp $

namespace Pmb\DSI\Models\Item\Entities\Article\ArticleListItem;

use Pmb\DSI\Models\Item\SimpleItem;

class ArticleListItem extends SimpleItem
{
    public const TYPE = TYPE_CMS_ARTICLE;

    public function getTree($parent = true)
    {
        $msg = static::getMessages();
        $data = \cms_editorial::get_format_data_structure("article", false);
        $tree = [
            [
				'var' => "articles",
				'desc' => $msg['tree_articles_desc'],
				'children' => $this->prefix_var_tree($data, "articles[i]")
            ]
        ];
        return $parent ? array_merge($tree, parent::getTree()) : $tree;
    }

    public function getLabels(array $ids)
    {
        $aricles = [];
        foreach ($ids as $id) {
            $article = new \cms_article($id);
            if(!empty($article->title)) {
                $aricles[$id] = $article->title;
            }
        }
        return $aricles;
    }
}
