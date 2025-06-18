<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: CustomFieldModel.php,v 1.19 2020/10/02 15:24:05 qvarin Exp $

namespace Pmb\Common\Models;

use Pmb\Common\Models\CustomFieldTypes\CustomFieldQueryListModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldListModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldTextModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldQueryAuthModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldDateBoxModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldCommentModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldURLModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldMarclistModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldTextI18nModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldQualifiedTextI18nModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldDateIntervalModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldDateFlotModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldResolveModel;
use Pmb\Common\Models\CustomFieldTypes\CustomFieldHTMLModel;

class CustomFieldModel extends Model
{
    protected $ormName = "\Pmb\Animations\Orm\CustomFieldOrm";
    
    public static function getAllCustomFields($prefix, $idObject = 0)
    {
        $pperso = new \parametres_perso($prefix);
        $pperso->get_values($idObject);
        $customFieldsTab = [];
        $i = 0;
        foreach ($pperso->t_fields as $idCustomField => $customField) {
            $customField['ID'] = $idCustomField;
            $customField['VALUES'] = (isset($pperso->values[$idCustomField])) ? $pperso->values[$idCustomField] : [];
            switch ($customField['OPTIONS'][0]['FOR']) {
                case 'text':
                    $customFieldsTab[$i]['customField'] = CustomFieldTextModel::getTextInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldTextModel::findTextValues($customField);
                    break;
                case 'list':
                    $customFieldsTab[$i]['customField'] = CustomFieldListModel::getListInformations($customField, $prefix, $idCustomField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldListModel::findListValues($customField, $prefix, $idCustomField);
                    break;
                case 'query_list':
                    $customFieldsTab[$i]['customField'] = CustomFieldQueryListModel::getQueryListInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQueryListModel::findQueryListValues($customField);
                    break;
                case 'query_auth':
                    $customFieldsTab[$i]['customField'] = CustomFieldQueryAuthModel::getQueryAuthInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQueryAuthModel::findQueryAuthValues($customField);
                    break;
                case 'date_box':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateBoxModel::getDateBoxInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateBoxModel::findDateBoxValues($customField);
                    break;
                case 'comment':
                    $customFieldsTab[$i]['customField'] = CustomFieldCommentModel::getCommentInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldCommentModel::findCommentValues($customField);
                    break;
                case 'url':
                    $customFieldsTab[$i]['customField'] = CustomFieldURLModel::getURLInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldURLModel::findURLValues($customField);
                    break;
                case 'marclist':
                    $customFieldsTab[$i]['customField'] = CustomFieldMarclistModel::getMarclistInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldMarclistModel::findMarclistValues($customField);
                    break;
                case 'text_i18n':
                    $customFieldsTab[$i]['customField'] = CustomFieldTextI18nModel::getTextI18nInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldTextI18nModel::findTextI18NValues($customField);
                    break;
                case 'q_txt_i18n':
                    $customFieldsTab[$i]['customField'] = CustomFieldQualifiedTextI18nModel::getQualifiedTextI18nInformations($customField, $prefix, $idCustomField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQualifiedTextI18nModel::findQualifiedTextI18NValues($customField);
                    break;
                case 'date_inter':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateIntervalModel::getDateIntervalInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateIntervalModel::findDateIntervalValues($customField);
                    break;
                case 'date_flot':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateFlotModel::getDateFlotInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateFlotModel::findDateFlotValues($customField);
                    break;
                case 'resolve':
                    $customFieldsTab[$i]['customField'] = CustomFieldResolveModel::getResolveInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldResolveModel::findResolveValues($customField);
                    break;

                    
                case 'html':
                    // TODO : Le v-model n'est pas fonctionnel, ainsi que la duplication ! 
                    $customFieldsTab[$i]['customField'] = CustomFieldHTMLModel::getHTMLInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldHTMLModel::findHTMLValues($customField);
                    break;
                case 'external':
                    // TODO : N'est pas fonctionnel !
                    $customFieldsTab[$i]['customField'] = CustomFieldValueModel::getExternalInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldValueModel::findExternalValues($customField, $prefix, $idCustomField, $idObject);
                    break;
                default:
                    break;
            }
            $i++;
        }
        return $customFieldsTab;
    }
    
    public static function getAllCustomsFieldPriceType($prefix, int $idObject = 0)
    {
        $pperso = new \animations_pricetype_parametres_perso($idObject);
        $customFieldsTab = [];
        $i = 0;
        foreach ($pperso->t_fields as $idCustomField => $customField) {
            $customField['ID'] = $idCustomField;
            $customField['VALUES'] = (isset($pperso->values[$idCustomField])) ? $pperso->values[$idCustomField] : [];
            switch ($customField['OPTIONS'][0]['FOR']) {
                case 'text':
                    $customFieldsTab[$i]['customField'] = CustomFieldTextModel::getTextInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldTextModel::findTextValues($customField);
                    break;
                case 'list':
                    $customFieldsTab[$i]['customField'] = CustomFieldListModel::getListInformations($customField, $prefix, $idCustomField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldListModel::findListValues($customField, $prefix, $idCustomField);
                    break;
                case 'query_list':
                    $customFieldsTab[$i]['customField'] = CustomFieldQueryListModel::getQueryListInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQueryListModel::findQueryListValues($customField);
                    break;
                case 'query_auth':
                    $customFieldsTab[$i]['customField'] = CustomFieldQueryAuthModel::getQueryAuthInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQueryAuthModel::findQueryAuthValues($customField);
                    break;
                case 'date_box':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateBoxModel::getDateBoxInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateBoxModel::findDateBoxValues($customField);
                    break;
                case 'comment':
                    $customFieldsTab[$i]['customField'] = CustomFieldCommentModel::getCommentInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldCommentModel::findCommentValues($customField);
                    break;
                case 'url':
                    $customFieldsTab[$i]['customField'] = CustomFieldURLModel::getURLInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldURLModel::findURLValues($customField);
                    break;
                case 'marclist':
                    $customFieldsTab[$i]['customField'] = CustomFieldMarclistModel::getMarclistInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldMarclistModel::findMarclistValues($customField);
                    break;
                case 'text_i18n':
                    $customFieldsTab[$i]['customField'] = CustomFieldTextI18nModel::getTextI18nInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldTextI18nModel::findTextI18NValues($customField);
                    break;
                case 'q_txt_i18n':
                    $customFieldsTab[$i]['customField'] = CustomFieldQualifiedTextI18nModel::getQualifiedTextI18nInformations($customField, $prefix, $idCustomField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldQualifiedTextI18nModel::findQualifiedTextI18NValues($customField);
                    break;
                case 'date_inter':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateIntervalModel::getDateIntervalInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateIntervalModel::findDateIntervalValues($customField);
                    break;
                case 'date_flot':
                    $customFieldsTab[$i]['customField'] = CustomFieldDateFlotModel::getDateFlotInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldDateFlotModel::findDateFlotValues($customField);
                    break;
                case 'resolve':
                    $customFieldsTab[$i]['customField'] = CustomFieldResolveModel::getResolveInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldResolveModel::findResolveValues($customField);
                    break;

                    
                case 'html':
                    // TODO : Le v-model n'est pas fonctionnel, ainsi que la duplication ! 
                    $customFieldsTab[$i]['customField'] = CustomFieldHTMLModel::getHTMLInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldHTMLModel::findHTMLValues($customField);
                    break;
                case 'external':
                    // TODO : N'est pas fonctionnel !
                    $customFieldsTab[$i]['customField'] = CustomFieldValueModel::getExternalInformations($customField);
                    $customFieldsTab[$i]['customValues'] = CustomFieldValueModel::findExternalValues($customField, $prefix, $idCustomField, $idObject);
                    break;
                default:
                    break;
            }
            $i++;
        }
        return $customFieldsTab;
    }
    
    public static function updateCustomFields($customFields, $idObject, $prefix)
    {
        $pperso = new \parametres_perso($prefix);
        self::setGlobalsCustomFields($customFields);
        $pperso->rec_fields_perso($idObject);
        self::unsetGlobalsCustomFields($customFields);
    }
    
    public static function updateCustomFieldsPriceType($customFields, $idObject, $numPriceType)
    {
        $pperso = new \animations_pricetype_parametres_perso($numPriceType);
        self::setGlobalsCustomFields($customFields);
        $pperso->rec_fields_perso($idObject);
        self::unsetGlobalsCustomFields($customFields);
    }
    
    public static function setGlobalsCustomFields($customFields)
    {
        foreach ($customFields as $field) {
            $name = $field->customField->name;
            switch ($field->customField->type) {
                case 'text':
                    $value = CustomFieldTextModel::getTextGlobalValue($field->customValues);
                    break;
                case 'list':
                    $value = CustomFieldListModel::getListGlobalValue($field->customValues);
                    break;
                case 'query_list':
                    $value = CustomFieldQueryListModel::getQueryListGlobalValue($field->customValues);
                    break;
                case 'query_auth':
                    $value = CustomFieldQueryAuthModel::getQueryAuthGlobalValue($field->customValues);
                    break;
                case 'date_box':
                    $value = CustomFieldDateBoxModel::getDateBoxGlobalValue($field->customValues);
                    break;
                case 'comment':
                    $value = CustomFieldCommentModel::getCommentGlobalValue($field->customValues);
                    break;
                case 'url':
                    $value = CustomFieldURLModel::getURLGlobalValue($field->customValues);
                    break;
                case 'marclist':
                    $value = CustomFieldMarclistModel::getMarclistGlobalValue($field->customValues);
                    break;
                case 'text_i18n':
                    $value = CustomFieldTextI18nModel::getTextI18nGlobalValue($field->customValues);
                    break;
                case 'q_txt_i18n':
                    $value = CustomFieldQualifiedTextI18nModel::getQualifiedTextI18nGlobalValue($field->customValues);
                    break;
                case 'date_inter':
                    $value = CustomFieldDateIntervalModel::getDateIntervalGlobalValue($field->customValues);
                    break;
                case 'date_flot':
                    $value = CustomFieldDateFlotModel::getDateFlotGlobalValue($field->customValues);
                    break;
                case 'resolve':
                    $value = CustomFieldResolveModel::getResolveGlobalValue($field->customValues);
                    break;
                case 'html':
                    $value = CustomFieldHTMLModel::getHTMLGlobalValue($field->customValues);
                    break;
                default:
                    $value = [];
                    break;
            }
            global ${$name};
            ${$name} = $value;
        }
    }
    
    public static function unsetGlobalsCustomFields($customFields)
    {
        foreach ($customFields as $field) {
            $name = $field->customField->name;
            global ${$name};
            unset(${$name});
        }
    }

    
}