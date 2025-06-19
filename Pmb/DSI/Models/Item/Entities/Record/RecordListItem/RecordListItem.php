<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RecordListItem.php,v 1.5.2.6 2023/11/28 11:37:33 rtigero Exp $

namespace Pmb\DSI\Models\Item\Entities\Record\RecordListItem;

use Pmb\DSI\Models\Item\SimpleItem;

class RecordListItem extends SimpleItem
{
    public const TYPE = TYPE_NOTICE;
    public function getTree($parent = true)
    {
        $msg = static::getMessages();
        $tree = [
            [
                'var' => "records",
                'desc' => $msg['tree_records_desc'],
                'children' => [
                    [
                        'var' => "records[i].content",
                        'desc'=> $msg['tree_record_content_desc'],
                    ],
                ],
            ],
        ];
        return $parent ? array_merge($tree, parent::getTree()) : $tree;
    }

    public function getLabels(array $ids)
    {
        $records = [];
        foreach ($ids as $id) {
            $title = @\notice::get_notice_title($id);
            if (!empty($title)) {
                $records[$id] = $title;
            }
        }

        return $records;
    }
}
