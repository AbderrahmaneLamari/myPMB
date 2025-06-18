<?php
// +-------------------------------------------------+
// ï¿½ 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: AnimationModel.php,v 1.100.2.2 2022/01/18 09:12:46 qvarin Exp $

namespace Pmb\Animations\Models;

use Pmb\Common\Models\Model;
use Pmb\Animations\Orm\AnimationOrm;
use Pmb\Animations\Orm\EventOrm;
use Pmb\Common\Models\DocsLocationModel;
use Pmb\Autorities\Models\CategoryModel;
use Pmb\Autorities\Models\ConceptModel;
use Pmb\Common\Models\CustomFieldModel;
use Pmb\Animations\Orm\RegistrationOrm;
use Pmb\Animations\Orm\RegistredPersonOrm;
use Pmb\Animations\Orm\MailingAnimationOrm;
use Pmb\Animations\Orm\AnimationTypesOrm;

class AnimationModel extends Model
{

    protected $ormName = "\Pmb\Animations\Orm\AnimationOrm";

    public static function getAnimationsList($basicInfos = false)
    {
        $animationsList = AnimationOrm::findAll();

        foreach ($animationsList as $key => $animation) {

            $anim = new AnimationModel($animation->id_animation);
            $evt = $anim->fetchEvent();
            $today = new \DateTime();
            $evenementDate = new \DateTime($evt->endDate);

            if ($today > $evenementDate) {
                unset($animationsList[$key]);
                continue;
            }
            if (! empty($evt)) {
                $anim->event = $anim->getFormatDate($evt);
            }

            if ($basicInfos == false) {
                $anim->fetchParent();
                $anim->fetchStatus();
                $anim->fetchType();
                $anim->fetchLocation(true);
                $anim->fetchQuotas();
                $anim->checkChildrens();
                $anim->prices = PriceModel::getPrices($animation->id_animation);
            }

            $animationsList[$key] = $anim;
        }
        
        usort($animationsList, function ($a, $b) {
            
            if (empty($a->event->rawStartDate)) {
                $a->event = $a->getFormatDate($a->event);
            }
            if (empty($b->event->rawStartDate)) {
                $b->event = $b->getFormatDate($b->event);
            }
            
            if ($a->event->rawStartDate == $b->event->rawStartDate) {
                return 0;
            }
            return ($a->event->rawStartDate < $b->event->rawStartDate) ? - 1 : 1;
        });
        
        return array_values($animationsList);
    }

    public static function getAnimation(int $id)
    {
        $anim = new AnimationModel($id);

        $evt = $anim->fetchEvent();
        if ($evt) {
            $anim->event = $anim->getFormatDate($evt);
        }

        $anim->fetchParent();
        $anim->fetchStatus();
        $anim->fetchLocation(true);
        $anim->fetchQuotas();
        
        $anim->prices = PriceModel::getPrices($id);
        return $anim;
    }

    public static function deleteAnimation(int $id, bool $delChildrens = false )
    {
        // Revoir la suppression d'un animation
        // Si on la supprime ou si on la passe dans un statut spï¿½cial
        
        $results = AnimationOrm::find('num_parent', $id);
        
        if (!empty($results) && $delChildrens == false) {
            return false;
        }
        
        $animation = new AnimationOrm($id);
        if ($animation->num_event) {
            $evt = new EventOrm($animation->num_event);
            $evt->delete();
        }

        if ($delChildrens) {
            $childrens = AnimationOrm::find('num_parent', $animation->id_animation);
            if (!empty($childrens) && count($childrens)) {
                foreach ($childrens as $animation_children) {
                    AnimationModel::deleteAnimation($animation_children->id_animation, $delChildrens);
                }
            }
        }
        
        AnimationModel::deleteLocationList($animation->id_animation);
        PriceModel::deleteAnimationPrices($animation->id_animation);
        RegistrationModel::deleteAnimationRegistration($animation->id_animation);

        $animation->delete();
    }

    public static function addAnimation(object $data)
    {
        global $thesaurus_concepts_active;
        
        $animation = new AnimationOrm();
        if (empty($data) || empty($data->name)) {
            return false;
        }

        $animation->name = $data->name;
        $animation->global_quota = $data->globalQuota;
        $animation->internet_quota = $data->internetQuota;
        $animation->allow_waiting_list = $data->allowWaitingList;
        $animation->auto_registration = $data->autoRegistration;

        $animation->expiration_delay = 1;
        $animation->registration_required = false;

        $animation->num_status = $data->numStatus;
        $animation->num_parent = $data->numParent;
        $animation->num_cart = $data->numCart;
        $animation->comment = $data->comment;
        $animation->description = $data->description;
        
        $data->numType = intval($data->numType);
        if (!AnimationTypesOrm::exist($data->numType)) {
            $data->numType = AnimationOrm::DEFAULT_TYPE;
        }
        $animation->num_type = $data->numType;
 
        $animation->num_event = EventModel::addEvent($data);

        $animation->save();
        
        if (! empty($data->categories)) {
            CategoryModel::updateAnimationCategories($data->categories, $animation->id_animation);
        }
        
        if (! empty($data->concepts) && $thesaurus_concepts_active) {
            ConceptModel::updateAnimationConcepts($data->concepts, $animation->id_animation);
        }

        if (! empty($data->location)) {
            AnimationModel::insertLocationList($data->location, $animation->id_animation);
        }
        
        if (! empty($data->mailingType)) {
            AnimationModel::insertMailingTypeList($data->mailingType, $animation->id_animation);
        }
        
        if (! empty($data->prices)) {
            PriceModel::updatePriceList($data->prices, 0, $animation->id_animation);
        }
        
        if (! empty($data->customFields)) {
            CustomFieldModel::updateCustomFields($data->customFields, $animation->id_animation, 'anim_animation');
        }
        return $animation->toArray();
    }

    public static function updateAnimation(int $id, object $data)
    {
        $animation = new AnimationOrm($id);
        if (! empty($data->name)) {
            $animation->name = $data->name;
        }
        
        if (isset($data->globalQuota)) {
            $animation->global_quota = $data->globalQuota;
        }
        
        if (isset($data->allowWaitingList)) {
            $animation->allow_waiting_list = $data->allowWaitingList;
        }
        
        if (isset($data->autoRegistration)) {
            $animation->auto_registration = $data->autoRegistration;
        }
        
        if (isset($data->internetQuota)) {
            $animation->internet_quota = $data->internetQuota;
        }
        
        if (isset($data->expirationDelay)) {
            $animation->expiration_delay = $data->expirationDelay;
        }
        
        if (isset($data->registrationRequired)) {
            $animation->registration_required = $data->registrationRequired;
        }
        
        if (isset($data->numStatus)) {
            $animation->num_status = $data->numStatus;
        }
        
        if (isset($data->numType)) {
            $data->numType = intval($data->numType);
            if (!AnimationTypesOrm::exist($data->numType)) {
                $data->numType = AnimationOrm::DEFAULT_TYPE;
            }
            $animation->num_type = $data->numType;
        }
        
        if (isset($data->numParent)) {
            $animation->num_parent = $data->numParent;
        }
        
        if (isset($data->numCart)) {
            $animation->num_cart = $data->numCart;
        }
        
        if (isset($data->comment)) {
            $animation->comment = $data->comment;
        }
        
        if (isset($data->numParent)) {
            $animation->description = $data->description;
        }
        
        if (isset($data->numEvent)) {
            $animation->num_event = EventModel::updateEvent($data->numEvent, $data);
        }
        
        if (isset($data->categories)) {
            CategoryModel::updateAnimationCategories($data->categories, $id);
        }
        
        if (isset($data->concepts)) {
            ConceptModel::updateAnimationConcepts($data->concepts, $id);
        }
        
        if (! empty($data->prices)) {
            PriceModel::updatePriceList($data->prices, 0, $id);
        }
        
        if (! empty($data->location)) {
            AnimationModel::insertLocationList($data->location, $id);
        }
        
        if (! empty($data->mailingType)) {
            AnimationModel::insertMailingTypeList($data->mailingType, $id);
        }
        
        if (! empty($data->customFields)) {
            CustomFieldModel::updateCustomFields($data->customFields, $id, 'anim_animation');
        }
        
        $animation->save();
        return $animation->toArray();
    }

    public static function getGlobalsSearch($searchFields)
    {
        $searchGlobals = [];
        $dateDone = false;
        foreach ($searchFields as $searchField => $searchValue) {
            if (is_array($searchValue)) {
                if (empty($searchValue[0])) {
                    continue;
                }
            } elseif (empty($searchValue)) {
                continue;
            }
            switch ($searchField) {
                case 'tlc':
                    $searchGlobals['f_1'] = [
                        'BOOLEAN' => $searchValue
                    ];
                    break;
                case 'dateStart':
                case 'dateEnd':
                    if (! $dateDone) {
                        if ($searchFields->dateEnd != '' && $searchFields->dateStart && !$searchFields->inputSearchExactDate) {
                            $searchGlobals['f_2'] = [
                                'BETWEEN' => [
                                    $searchValue,
                                    $searchFields->dateEnd
                                ]
                            ];
                        } else {
                            if ($searchFields->inputSearchExactDate) {
                                $searchGlobals['f_2'] = [
                                    'EQ' => $searchValue
                                ];
                            } elseif ($searchFields->dateEnd) {
                                $searchGlobals['f_2'] = [
                                    'LT' => $searchFields->dateEnd
                                ];
                            } else {
                                $searchGlobals['f_2'] = [
                                    'GT' => $searchValue
                                ];
                            }
                        }
                        $dateDone = true;
                    }
                    break;
                default:
                    break;
            }
        }
        return $searchGlobals;
    }

    public static function getFormData($id = 0)
    {
        global $pmb_gestion_devise, $thesaurus_concepts_active;
        
        return [
            'locations' => DocsLocationModel::getLocationList(),
            'mailingTypes' => MailingTypeModel::getMailingsTypeListForAnimation(),
            'status' => AnimationStatusModel::getAnimationStatusList(),
            'types' => AnimationTypesModel::getAnimationTypesList(),
            'priceType' => PriceTypeModel::getPricesTypeList(),
            'img' => [
                'plus' => get_url_icon('plus.gif'),
                'minus' => get_url_icon('minus.gif'),
                'expandAll' => get_url_icon('expand_all'),
                'collapseAll' => get_url_icon('collapse_all'),
                'tick' => get_url_icon('tick.gif'),
                'error' => get_url_icon('error.png'),
                'patience' => get_url_icon('patience.gif'),
                'sort' => get_url_icon('sort.png'),
                'iconeDragNotice' => get_url_icon('icone_drag_notice.png')
            ],
            'globals' => [
                'conceptsActive' => html_entity_decode($thesaurus_concepts_active),
                'pmbDevise' => html_entity_decode($pmb_gestion_devise)
            ]
        ];
    }
    
    public function fetchMailings(bool $duplicate = false)
    {
        if (! empty($this->mailings)) {
            return $this->mailings;
        }
        $this->mailings = MailingAnimationModel::getMailings($this->id, $duplicate);
        return $this->mailings;
    }
    

    public function fetchParent()
    {
        if (! empty($this->parent)) {
            return $this->parent;
        }
        
        $num_parent = 0;
        if (!empty($this->numParent)) {
            $num_parent = $this->numParent;
        }
        
        $this->parent = new AnimationModel($num_parent);
        return $this->parent;
    }
    
    public function fetchPrices(bool $duplicate = false)
    {
        if (! empty($this->prices)) {
            return $this->prices;
        }
        $this->prices = PriceModel::getPrices($this->id, $duplicate);
        return $this->prices;
    }

    public function fetchEvent()
    {
        if (! empty($this->event)) {
            return $this->event;
        }
        
        $num_event = 0;
        if (!empty($this->numEvent)) {
            $num_event = $this->numEvent;
        }
        
        $this->event = new EventModel($num_event);
        return $this->event;
    }

    public function fetchStatus()
    {
        if (! empty($this->status)) {
            return $this->status;
        }
        
        $num_status = 0;
        if (!empty($this->numStatus)) {
            $num_status = $this->numStatus;
        }
        
        $this->status = new AnimationStatusModel($num_status);
        return $this->status;
    }
    
    public function fetchType()
    {
        if (! empty($this->type)) {
            return $this->type;
        }
        
        $num_type = AnimationOrm::DEFAULT_TYPE;
        if (!empty($this->numType) && AnimationTypesOrm::exist(intval($this->numType))) {
            $num_type = $this->numType;
        }
        
        $this->type = new AnimationTypesModel($num_type);
        return $this->type;
    }

    public function fetchLocation($withInformations = false)
    {
        if (! empty($this->location)) {
            return $this->location;
        }
        
        $this->location = [];
        $query = "SELECT * FROM anim_animation_locations WHERE num_animation = $this->id";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                if (! empty($row->num_location)) {
                    if ($withInformations) {
                        $loc = new DocsLocationModel($row->num_location);
                        $this->location[] = self::toArray($loc);
                    } else {
                        $this->location[] = $row->num_location;
                    }
                }
            }
        }
        return $this->location;
    }
    
    public function fetchMailingType($withInformations = false)
    {
        if (empty($this->mailing)) {
            $this->fetchMailings();
        }
        
        if (empty($this->mailingType)) {
            $this->mailingType = array();
            if (!empty($this->mailing)) {
                foreach ($this->mailing as $mailing) {
                    if ($withInformations) {
                        $this->mailingType[] = new MailingTypeModel($mailing->num_mailing_type);
                    } else {
                        $this->mailingType[] = $mailing->num_mailing_type;
                    }
                }
            }
        }
        
        return $this->mailingType;
    }
    
    public function fetchConcepts()
    {
        if (! empty($this->concepts)) {
            return $this->concepts;
        }
        
        $this->concepts = [];
        $query = "SELECT * FROM index_concept WHERE num_object = $this->id AND type_object = '" . TYPE_ANIMATION . "'";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $this->concepts[] = ConceptModel::getConcept($row->num_concept);
            }
        }
    }
    
    public function fetchCategories()
    {
        if (! empty($this->categories)) {
            return $this->categories;
        }
        
        $this->categories = [];
        $query = "SELECT * FROM anim_animation_categories WHERE num_animation = $this->id ORDER BY ordre_categorie";
        $result = pmb_mysql_query($query);
        if (pmb_mysql_num_rows($result)) {
            while ($row = pmb_mysql_fetch_object($result)) {
                $this->categories[] = CategoryModel::getCategory($row->num_noeud);
            }
        }
    }
    
    public function fetchCustomFields()
    {
        if (! empty($this->customFields)) {
            return $this->customFields;
        }
        $this->customFields = CustomFieldModel::getAllCustomFields('anim_animation', $this->id);
        $this->gotCustomFieldsValues = false;
        foreach ($this->customFields as $field) {
            if (! empty($field['customField']['values'])) {
                $this->gotCustomFieldsValues = true;
            }
        }
    }

    public function getFormatDate($event)
    {
        $evt = new \stdClass();
        
        if (!empty($event->rawStartDate)) {
            // event est déjà formaté
            return $event;
        }
        
        $sd = new \DateTime($event->startDate ?? 'now');
        $ed = new \DateTime($event->endDate ?? 'now');
        $date = new \DateTime();

        $evt->startDate = $sd->format("d/m/Y");
        $evt->endDate = $ed->format("d/m/Y");

        $evt->startHour = $sd->format("H:i");
        $evt->endHour = $ed->format("H:i");

        $evt->rawStartDate = $event->startDate ?? $sd;
        $evt->rawEndDate = $event->endDate ?? $ed;
        
        $evt->dateExpired = false;
        if ($ed < $date) {
            $evt->dateExpired = true;
        }
        
        return $evt;
    }

    public function formatEventDateHTML()
    {
        $this->event->startHour = '';
        if (!empty($this->event->startDate)) {
            $sd = new \DateTime($this->event->startDate);
            $this->event->startDate = $sd->format("Y-m-d");
            $this->event->startHour = $sd->format("H:i");
        }
        
        $this->event->endHour = '';
        if (!empty($this->event->endDate)) {
            $ed = new \DateTime($this->event->endDate);
            $this->event->endDate = $ed->format("Y-m-d");
            $this->event->endHour = $ed->format("H:i");
        }

        return $this->event;
    }

    public static function insertLocationList(array $locations, int $idAnimation = 0)
    {
        $query = "DELETE FROM `anim_animation_locations` WHERE `anim_animation_locations`.`num_animation` = $idAnimation";
        pmb_mysql_query($query);

        foreach ($locations as $locationId) {
            $query = "INSERT INTO `anim_animation_locations` (`num_animation`, `num_location`) VALUES ($idAnimation, $locationId)";
            pmb_mysql_query($query);
        }
    }
    
    public static function insertMailingTypeList(array $mailingType, int $idAnimation = 0)
    {
        $animationModel = new AnimationModel($idAnimation);
        $animationModel->fetchMailings();
        
        $already_create = array();
        if ($animationModel->mailing) {
            foreach ($animationModel->mailing as $mailing) {
                if (in_array($mailing->num_mailing_type, $mailingType)) {
                    $already_create[] = $mailing->num_mailing_type;
                } else {
                    $mailing->delete();
                }
            }
        }
        
        foreach ($mailingType as $mailingTypeId) {
            if (!empty($mailingTypeId) && intval($mailingTypeId) != 0 && !in_array($mailingTypeId, $already_create)) {
                $mailingOrm = new MailingAnimationOrm();
                $mailingOrm->num_animation = intval($idAnimation);
                $mailingOrm->num_mailing_type = intval($mailingTypeId);
                $mailingOrm->save();
            }
        }
    }

    public static function deleteLocationList(int $id)
    {
        $query = "DELETE FROM `anim_animation_locations` WHERE `anim_animation_locations`.`num_animation` = $id";
        pmb_mysql_query($query);
    }
    
    public static function saveParentChild($data) {
        $animation = new AnimationOrm($data->idChildren);
        if (0 === $data->idParent) {
            $animation->num_parent = 0;
        }else{
            $animation->num_parent = $data->idParent;
        }
        $animation->save();
    }
    
    public static function getAnimationsDNDList(){
        
        $animationsList = AnimationOrm::findAll();
        
        $list = [];
        $listToUnset = [];
        
        $animTemps = new AnimationModel();
        
        foreach ($animationsList as $key => $animation) {
            $newAnimation = new \stdClass();
            $event = new EventModel($animation->num_event);
            $newAnimation->id = $animation->id_animation;
            $newAnimation->key = $key;
            $newAnimation->name = $animation->name;
            $newAnimation->event = $animTemps->getFormatDate($event);
            $newAnimation->numParent = $animation->num_parent;
            $newAnimation->nested = [];
            
            $list[$animation->id_animation] = $newAnimation;
        }
        
        foreach ($list as $id => $anim){
            if (!empty($anim->numParent)) {
                if (isset($list[$anim->numParent])) {
                    $list[$anim->numParent]->nested[] = $anim;
                    $listToUnset[] = $list[$id];
                } 
            }
        }
        
        foreach ($listToUnset as $animation){
            unset($list[$animation->id]);
        }
        
        usort($list, function ($a, $b) {
            if ($a->event->startDate == $b->event->startDate) {
                return 0;
            }
            return ($a->event->startDate < $b->event->startDate) ? - 1 : 1;
        });
        
        return self::toArray(array_values($list));
    }
    
    public function getEditAddData(bool $duplicate = false)
    {
        $this->fetchEvent();
        if (!empty($this->numEvent)) {
            $this->formatEventDateHTML();
        }
        $this->fetchPrices($duplicate);
        $this->fetchLocation();
        $this->fetchMailingType();
        $this->fetchConcepts();
        $this->fetchCategories();
        $this->fetchCustomFields();
        $this->fetchParent();
        //$this->allowWaitingList = 0;

        //TODO : Revoir la duplication en passant par une mÃ©thode dans l'ORM pour supprimer les relations
        if ($duplicate) {
            //Remise a ZÃ©ro des id et numAnimation
            $this->idAnimation = 0;
            $this->id = 0;
            $this->event->id = 0;
            $this->event->idEvent = 0;
            $this->numEvent = 0;
            $this->hasChildrens = false;
        } else {
            $this->hasChildrens = $this->checkChildrens();
        }
        
        
        return $this;
    }
    
    public function checkChildrens()
    {
        if ($this->idAnimation) {
            $results = AnimationOrm::find('num_parent', $this->idAnimation);
            if (!empty($results) && count($results)) {
                $this->hasChildrens = true;
                return true;
            }
        }
        $this->hasChildrens = false;
        return false;
    }
    
    public function getViewData()
    {
        $this->fetchEvent();
        if (! empty($this->event)) {
            $this->event = $this->getFormatDate($this->event);
        }
        $this->fetchParent();
        $this->fetchPrices();
        $this->fetchLocation(true);
        $this->fetchStatus();
        $this->fetchType();
        $this->fetchConcepts();
        $this->fetchCategories();
        $this->fetchCustomFields();
        $this->fetchQuotas();
        
        $this->hasChildrens = $this->checkChildrens();
        if ($this->hasChildrens) {
            $this->childrens = $this->getDaughterList($this->idAnimation);
        }
        
        return $this;
    }
    
    public function getSimpleSearchData()
    {
        $this->fetchEvent();
        if (! empty($this->event)) {
            $this->event = $this->getFormatDate($this->event);
        }
        $this->fetchLocation(true);
        $this->fetchStatus();
        $this->fetchType();
        $this->fetchQuotas();
        $this->checkChildrens();
        
        return $this;
    }
    
    public function fetchQuotas() {
        if (! empty($this->allQuotas)) {
            return $this->allQuotas;
        }
        
        $this->allQuotas = AnimationModel::getAllQuotas($this->id);
        $this->hasQuotas = false;

        if ($this->globalQuota >= 0 || $this->internetQuota >= 0) {
            $this->hasQuotas = true;
        }
        
        $this->internetAvailable = false;
        if ($this->allQuotas['availableQuotas']['global'] >= $this->allQuotas['availableQuotas']['internet']) {
            $this->internetAvailable = true;
        }
        
        return $this->allQuotas;
    }
    
    public static function getBaseQuotas($id){
        $animations = new AnimationOrm($id);
        $quotas = [
            "global" => intval($animations->global_quota),
            "internet" => intval($animations->internet_quota),
        ];
        return $quotas;
    }
    
    public static function getAllQuotas($id){
        //Renvoie le nombre de place reserves
        $registrations = RegistrationModel::getRegistrationPlaceForAnimation($id);

        //Renvoie le nombre de place reserves sur liste d'attente
        $waitingList = RegistrationModel::getRegistrationWaitingList($id);
        
        //Renvoie le quotas de place de l'animation
        $quotasAnimation = AnimationModel::getBaseQuotas($id);
        
        //calcul et formatage de donnees des places reservees
        $globalReservedPlace = 0;
        $internetReservedPlace = 0;
        if (count($registrations)>0){
            $result = self::countNbRegistredPerson($registrations);
            $globalReservedPlace = $result['global'];
            $internetReservedPlace = $result['internet'];
        }
        
        //calcul et formatage de donnees des places sur liste d'attente
        $globalWaitingList = 0;
        $internetWaitingList = 0;
        if (count($waitingList)>0){
            $result = self::countNbRegistredPerson($waitingList);
            $globalWaitingList = $result['global'];
            $internetWaitingList = $result['internet'];
        }
        
        $availablePlace = [];
        $availablePlace['global'] = 0;
        $availablePlace['internet'] = 0;
        
        //On y passe seulement dans le cas ou le quotas est limite
        if ($quotasAnimation['global']>0){
            $availablePlace['global'] = $quotasAnimation['global'] - $globalReservedPlace - $internetReservedPlace;
        }
        if ($quotasAnimation['internet']>0){
            $availablePlace['internet'] = $quotasAnimation['internet'] - $internetReservedPlace;
        }
        
        //Dans le cas ou il n'y a plus de places global disponibles, le nombre de places internet disponbibles est remis a 0
        if ($quotasAnimation['global']>0 && $availablePlace['global'] == 0){
            $availablePlace['internet'] = 0;
        } else if ($quotasAnimation['global']>0 && $availablePlace['global'] < $availablePlace['internet']) {
            //Dans le cas ou le compteur de place restante global est inferieure au compteur de place restante sur internet
            $availablePlace['internet'] = $availablePlace['global'];
        }
        
        $quotas = [];
        $quotas['animationQuotas'] = $quotasAnimation;
        $quotas['availableQuotas'] = $availablePlace;
        $quotas['reserved'] = [
            'global' => $globalReservedPlace,
            'internet' => $internetReservedPlace
        ];
        $quotas['waitingList'] = [
            'global' => $globalWaitingList,
            'internet' => $internetWaitingList
        ];
        return $quotas;
    }
    
    protected static function countNbRegistredPerson($registredList){
        $global = 0;
        $internet = 0;
        if ($registredList['global']){
            foreach ($registredList['global'] as $registredGlobal){
                $global += intval($registredGlobal->nb_registered_persons);
            }
        }
        if ($registredList['internet']){
            foreach ($registredList['internet'] as $registredInternet){
                $internet += intval($registredInternet->nb_registered_persons);
            }
        }
        return [
            "global" => $global,
            "internet" => $internet  
        ];
    }
    
    public static function getDaughterList($idAnimation)
    {
        $daugtherList = [];
        $daughterORM = AnimationOrm::find('num_parent', $idAnimation);
        foreach ($daughterORM as $orm) {
            $id_animation = $orm->id_animation;
            if (!empty($id_animation) && $id_animation != 0) {
                $daugtherList[] = self::getAnimation($id_animation);
            }
        }
        
        return $daugtherList;
    }
    
    public function getSummaryPerson() {
        $priceTab = array();
        
        $registrationList = RegistrationOrm::find('num_animation', $this->id);
        foreach ($registrationList as $registration){
            $personList = RegistredPersonOrm::find('num_registration', $registration->id_registration);
            foreach ($personList as $person){
                $personModel = new RegistredPersonModel($person->id_person);
                $price = $personModel->fetchPrice();
                if (empty($priceTab[$price->name])) {
                    $priceTab[$price->name] = 0;
                }
                $priceTab[$price->name] = $priceTab[$price->name] + 1;
            }
        }
        return $priceTab;        
    }
    
    public static function deleteAnimationCartNum($numCart){
        $animationOrm = AnimationOrm::find('num_cart', $numCart)[0];
        if ($animationOrm != null){
            $animationOrm->num_cart = 0;
            $animationOrm->save();
        }
        
        return true;
    }
    
    public static function getIdAnimationFromNumCaddie($idEmprCaddie){
        $animationOrm = AnimationOrm::find("num_cart", $idEmprCaddie);
        return $animationOrm[0]->id_animation;
    }
    
    public static function getAnimationForMailing(int $id)
    {
        $anim = new AnimationModel($id);
        $anim->fetchEvent();
        $anim->fetchLocation(true);
        $anim->fetchMailings();
        
        return $anim;
    }
    
    public static function getAllAnimations(){
        return AnimationOrm::findAll();
    }
    
    public function getCmsStructure(string $prefixVar = "", bool $children = false)
    {
        global $msg;
        
        $cmsStructure = parent::getCmsStructure($prefixVar, $children);
        
        if (! empty($cmsStructure[0]['children'])) {
            foreach ($cmsStructure[0]['children'] as $key => $props) {
                if ($props['var'] == $prefixVar.".event") {
                    $event = $this->getFormatDate(null);
                    if (!empty($event)) {
                        $cmsStructure[0]['children'][$key]['children'] = array();
                        foreach ($event as $propName => $value) {
                            $length = count($cmsStructure[0]['children'][$key]['children']);
                            $cmsStructure[0]['children'][$key]['children'][$length]['var'] = $props['var'].".".$propName;
                            
                            $msgVar = str_replace(".", "_",  $props['var'].".".$propName);
                            switch (true) {
                                case isset($msg['cms_module_common_datasource_desc_'.$msgVar]):
                                    $desc = $msg['cms_module_common_datasource_desc_'.$msgVar];
                                    break;
                                    
                                case isset($msg[$msgVar]):
                                    $desc = $msg[$msgVar];
                                    break;
                                    
                                default:
                                    $desc = addslashes($msgVar);
                                    break;
                            }
                            $cmsStructure[0]['children'][$key]['children'][$length]['desc'] = $desc;
                        }
                    }
                }
            }
        }
        
        return $cmsStructure;
    }
    
    public function getCmsData()
    {
        $data = parent::getCmsData();
        
        foreach ($data as $var => $value) {
            if ($var == "event") {
                $event = $this->getFormatDate(null);
                if (!empty($event)) {
                    $data[$var] = $event;
                }
            }
        }
        
        return $data;
    }
    
    public function emprAlreadyRegistred(int $id_empr)
    {
        if (!empty($this->emprAlreadyRegistred)) {
            return $this->emprAlreadyRegistred;
        }
        
        $registrationList = RegistrationModel::getEmprRegistrationsList($id_empr);
        foreach ($registrationList as $registration) {
            if ($registration->animation->id == $this->id){
                $this->emprAlreadyRegistred = true;
                return true;
            }
        }
        
        $this->emprAlreadyRegistred = false;
        return false;
    }
}