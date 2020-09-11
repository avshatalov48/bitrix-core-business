<?php
namespace Bitrix\Sender\Integration\Crm\Connectors\Personalize;

use Bitrix;
use CCrmStatus;

class Company extends BasePersonalize
{

	public static function getEntityFields($entityType)
	{
		\Bitrix\Main\Localization\Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components/bitrix/crm.'.strtolower($entityType).'.edit/component.php'
		);

		$arResult = array(
			'ID' => array(
				'Name' => GetMessage('CRM_FIELD_ID'),
				'Type' => 'int',
				'Filterable' => true,
				'Editable' => false,
				'Required' => false,
				'personalizeCode' => 'ID'
			),
			'TITLE' => array(
				'Name' => GetMessage('CRM_FIELD_TITLE_COMPANY'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => true,
				'personalizeCode' => 'NAME'
			),
			'COMPANY_TYPE' => array(
				'Name' => GetMessage('CRM_FIELD_COMPANY_TYPE'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('COMPANY_TYPE'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'INDUSTRY' => array(
				'Name' => GetMessage('CRM_FIELD_INDUSTRY'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('INDUSTRY'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'EMPLOYEES' => array(
				'Name' => GetMessage('CRM_FIELD_EMPLOYEES'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('EMPLOYEES'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'REVENUE' => array(
				'Name' => GetMessage('CRM_FIELD_REVENUE'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
		);
		$arResult += array(
			'COMMENTS' => array(
				'Name' => GetMessage('CRM_FIELD_COMMENTS'),
				'Type' => 'text',
				'Filterable' => false,
				'Editable' => true,
				'Required' => false,
			),
			'EMAIL' => array(
				'Name' => GetMessage('CRM_FIELD_EMAIL'),
				'Type' => 'email',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
				'personalizeCode' => 'EMAIL'
			),
			'PHONE' => array(
				'Name' => GetMessage('CRM_FIELD_PHONE'),
				'Type' => 'phone',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
				'personalizeCode' => 'PHONE'
			),
			'WEB' => array(
				'Name' => GetMessage('CRM_FIELD_WEB'),
				'Type' => 'web',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'IM' => array(
				'Name' => GetMessage('CRM_FIELD_MESSENGER'),
				'Type' => 'im',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'FULL_ADDRESS' => array(
				'Name' => GetMessage('CRM_FIELD_ADDRESS'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_LEGAL' => array(
				'Name' => GetMessage('CRM_FIELD_ADDRESS_LEGAL'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'BANKING_DETAILS' => array(
				'Name' => GetMessage('CRM_FIELD_BANKING_DETAILS'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			"OPENED" => array(
				"Name" => GetMessage("CRM_FIELD_OPENED"),
				"Type" => "bool",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
//			"ORIGINATOR_ID" => array(
//				"Name" => GetMessage("CRM_FIELD_ORIGINATOR_ID"),
//				"Type" => "string",
//				"Filterable" => true,
//				"Editable" => true,
//				"Required" => false,
//			),
			"CONTACT_ID" => array(
				"Name" => GetMessage("CRM_FIELD_CONTACT_ID"),
				"Type" => "UF:crm",
				"Options" => array('CONTACT' => 'Y'),
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => true,
			),
			"DATE_CREATE" => array(
				"Name" => GetMessage("CRM_COMPANY_EDIT_FIELD_DATE_CREATE"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"DATE_MODIFY" => array(
				"Name" => GetMessage("CRM_COMPANY_EDIT_FIELD_DATE_MODIFY"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
		);

		$arResult += parent::getEntityFields($entityType);

		return $arResult;
	}
}
