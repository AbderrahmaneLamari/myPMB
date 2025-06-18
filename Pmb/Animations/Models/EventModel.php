<?php
// +-------------------------------------------------+
// ï¿½ 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: EventModel.php,v 1.15 2020/09/08 10:11:24 gneveu Exp $

namespace Pmb\Animations\Models;

use Pmb\Common\Models\Model;
use Pmb\Animations\Orm\EventOrm;

class EventModel extends Model
{
    protected $ormName = "\Pmb\Animations\Orm\EventOrm";
    
    public static function getEvents()
    {
        $events = EventOrm::findAll();
        return self::toArray($events);
    }
    
    public static function getEvent(int $id)
    {
        $event = new EventOrm($id);
        return $event->toArray();
    }
    
    public static function deleteEvent(int $id)
    {
        $event = new EventOrm($id);
        $event->delete();
    }
    
    public static function addEvent(object $data)
    {
        $event = new EventOrm();
        if (empty($data->event->startDate) || empty($data->event->endDate)) {
            return false;
        }
        if (empty($data->event->startHour) || empty($data->event->endHour)) {
            $data->event->startHour = "00:00";
            $data->event->endHour = "00:00";
        }
        //TODO : Gérer un DateTime Helper
        $event->start_date = $data->event->startDate." ".$data->event->startHour;
        $event->end_date = $data->event->endDate." ".$data->event->endHour;

        if (!empty($data->num_config)) {
            $event->num_config = $data->num_config;
        }
        $event->save();
        return $event->id_event;
    }
    
    public static function updateEvent(int $id, object $data)
    {
        $event = new EventOrm($id);
        
        if (!empty($data->event->startDate)) {
            $event->start_date = $data->event->startDate." ".$data->event->startHour;
        }
        if (!empty($data->event->endDate)) {
            $event->end_date = $data->event->endDate." ".$data->event->endHour;
        }
        if (!empty($data->num_config)) {
            $event->num_config = $data->num_config;
        }
        $event->save();
        return $event->id_event;
    }
}