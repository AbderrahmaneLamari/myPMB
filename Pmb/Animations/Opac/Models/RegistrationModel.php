<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: RegistrationModel.php,v 1.16.2.1 2021/09/17 14:30:38 gneveu Exp $
namespace Pmb\Animations\Opac\Models;

use Pmb\Animations\Models\RegistrationModel as Registration;
use Pmb\Animations\Models\AnimationModel;
use Pmb\Animations\Models\RegistredPersonModel;
use Pmb\Animations\Orm\RegistredPersonOrm;
use Pmb\Animations\Orm\RegistrationOrm;
use Pmb\Common\Helper\Helper;
use Pmb\Common\Models\EmprModel;
use Pmb\Common\Helper\HashModel;
use Pmb\Animations\Models\MailingTypeModel;
use Pmb\Animations\Models\MailingAnimationModel;
use Pmb\Common\Models\MailtplModel;
use Pmb\Animations\Orm\MailingTypeOrm;

class RegistrationModel extends Registration
{
    public static function getNewRegistration(int $numAnimation, int $id_empr = 0)
    {
        $registration = new RegistrationModel();
        
        $registration->numAnimation = $numAnimation;
        $registration->barcode = '';
        $registration->numOrigin = 1;
        if (!empty($id_empr) && $id_empr > 0) {
            $emprModel = new EmprModel($id_empr);
            $registration->barcode = $emprModel->emprCb;
            $registration->name = $emprModel->emprNom;
            $registration->email = $emprModel->emprMail;
            $registration->phoneNumber =  "";
            if (!empty($emprModel->emprTel1)) {
                $registration->phoneNumber =  $emprModel->emprTel1;
            } elseif (!empty($emprModel->emprTel2)) {
                $registration->phoneNumber =  $emprModel->emprTel2;
            }
        }
        $registration->registrationListPerson = array();
    
        return $registration;
    }
    
    public static function getRegistrationByList(array $registrationList)
    {
        $result = array();
        foreach ($registrationList as $registrationId) {
            $registration = new RegistrationModel($registrationId);
            $registration->fetchRegistrationListPerson();
            $registration->fetchAnimation();
            $registration->animation->fetchEvent();
            $registration->animation->fetchPrices();
            $emprModel = new EmprModel($registration->numEmpr);
            $registration->barcode = $emprModel->emprCb;
            if (! empty($registration->animation->event)) {
                $registration->animation->event = $registration->animation->getFormatDate($registration->animation->event);
            }
            $result[] = $registration;
        }
        return $result;
    }
    
    public function fetchAnimation()
    {
        if (! empty($this->animation)) {
            return $this->animation;
        }
        $this->animation = null;
        if (! empty($this->numAnimation)) {
            $this->animation = new AnimationModel($this->numAnimation);
            $this->animation->fetchQuotas();
            $this->animation->description = nl2br($this->animation->description);
        }
        return $this->animation;
    }
    
    public static function addRegistration(object $data)
    {
        global $msg;
        
        if (!self::registrationAllowed()) {
            return array("success" => false, "animations" => array(), "message" => $msg['animation_registration_unauthorized']);
        }
        
        if (empty($data->name) || empty($data->numAnimation) || empty($data->phoneNumber)) {
            return array("success" => false, "animations" => array());
        }
        
        if (empty($data->animationsSelected) && empty(AnimationModel::getDaughterList($data->numAnimation))) {
            $data->animationsSelected[] = $data->numAnimation;
        }
        
        $registrationList = array();
        $animationList = array();
        
        // Dans le cas de base (simple) on dit que l'on doit passer par une reservation, puis une confirmation en Gestion ICI on gére la reservation
        $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_REGISTRATION);
        
        foreach ($data->animationsSelected as $idAnimation) {
            $anim = new AnimationModel($idAnimation);
            $anim->fetchQuotas();
            
            if (($anim->globalQuota >= 0 && $anim->internetQuota >= 0) && ($anim->allQuotas['availableQuotas']['internet'] || $anim->internetQuota == 0) && ($anim->allQuotas['availableQuotas']["global"] >= count($data->registrationListPerson))) {
                // On peut s'inscrire
                if ($anim->autoRegistration) {
                    // Validation automatique
                    $data->numRegistrationStatus = self::VALIDATED;
                    $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_CONFIRMATION);
                } else {
                    $data->numRegistrationStatus = self::PENDING_VALIDATION;
                }
            } elseif ($anim->allowWaitingList) {
                //Dans le cas d'une inscription local (et qu'il n'y à plus de place), on inscrit sur liste d'attente
                if ($anim->allQuotas['availableQuotas']["global"] < count($data->registrationListPerson)) {
                    $data->numRegistrationStatus = self::WAITING_LIST;
                }
            } else {
                continue;
            }
            
            $registration = new RegistrationOrm();
            $registration->nb_registered_persons = count($data->registrationListPerson);
            $registration->name = $data->name;
            $registration->num_animation = $idAnimation;
            $animationList[] = $idAnimation;
            $registration->date = date('Y-m-d H:i:s');
            
            if (! empty($data->phoneNumber) && Helper::isValidPhone($data->phoneNumber)) {
                $registration->phone_number = $data->phoneNumber;
            }
            if (! empty($data->email) && Helper::isValidMail($data->email)) {
                $registration->email = $data->email;
            }
            if (! empty($data->numRegistrationStatus)) {
                $registration->num_registration_status = $data->numRegistrationStatus;
            }
            
            if (! empty($data->barcode)) {
                $emprModel = EmprModel::getEmprByCB($data->barcode);
                $registration->num_empr = $emprModel->idEmpr;
            }
            
            if (! empty($data->numOrigin)) {
                $registration->num_origin = $data->numOrigin;
            }
            
            $registration->save();
            
            $param = $registration->{RegistrationOrm::$idTableName}.$registration->date.$registration->nb_registered_persons;
            $hashModel = new HashModel();
            
            $registration->hash = $hashModel->generateHash($param);
            $registration->save();
            
            $registrationList[] = $registration->id_registration;
            
            if (!empty($data->registrationListPerson)) {
                foreach ($data->registrationListPerson as $person){
                    $person->numRegistration = $registration->id_registration;
                    if (! empty($data->barcode)) {
                        $emprModel = EmprModel::getEmprByCB($person->barcode);
                        $person->numEmpr = $emprModel->idEmpr;
                    }
                    if (!empty($person->animations)) {
                        foreach ($person->animations as $numAnimation => $animations) {
                            if ($numAnimation == $idAnimation) {
                                $person->personCustomsFields = $animations->personCustomsFields;
                                $person->numAnimation = $animations->numAnimation;
                                $person->numPrice = $animations->numPrice;
                            }
                        }
                    }
                    $values = RegistredPersonModel::addRegistredPerson($person);
                    if (empty($values)) {
                        return array("success" => false, "animations" => $animationList);
                    }
                }
            }
            
            // Generation du mail pour l'animation et la personne de contact
            if (!empty($maillingTypeOrm) && !empty($maillingTypeOrm[0])) {
                $registrationModel = new RegistrationModel($registration->{RegistrationOrm::$idTableName});
                $template = MailtplModel::getMailtpl($maillingTypeOrm[0]->num_template);
                $temp = array();
                MailingAnimationModel::sendMail([$registrationModel], $idAnimation, $template, $temp, $maillingTypeOrm[0]->num_sender);
            }
        }
        
        $_SESSION['registrationList'] = $registrationList;
        
        $success = true;
        // La personne est inscrite a aucune animation
        if (empty($animationList)) {
            $success = false;
        }
        
        return array("success" => $success, "animations" => $animationList);
    }
    
    public static function registrationAllowed() 
    {
        global $opac_animations_only_empr, $id_empr;
        
        if ($opac_animations_only_empr && empty($id_empr)) {
            return false;
        }
        return true;
    }
    
    public function delete(bool $is_contact = false, int $id_registration_person = 0)
    {
        if (!empty($this->idRegistration)) {
            
            $maillingTypeOrm = MailingTypeOrm::find("periodicity", MailingTypeModel::MAILING_ANNULATION);

            if ($is_contact) {
                self::deleteRegistration($this->idRegistration);
            } else {
                
                $registredPerson = new RegistredPersonOrm($id_registration_person);
                $registration = new RegistrationOrm($registredPerson->num_registration);
                $registration->nb_registered_persons -= 1;
                $registration->save();
                $registredPerson->delete();
                
                // Generation du mail pour l'animation et la personne de contact
                // si $is_contact == true le mail est envoye dans la methode deleteRegistration
                if (!empty($maillingTypeOrm) && !empty($maillingTypeOrm[0])) {
                    $template = MailtplModel::getMailtpl($maillingTypeOrm[0]->num_template);
                    $temp = array();
                    MailingAnimationModel::sendMail([$this], $this->numAnimation, $template, $temp, $maillingTypeOrm[0]->num_sender);
                }
            }
            
            
        } else {
            return false;
        }
    }
}
