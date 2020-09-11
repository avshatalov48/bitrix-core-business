<?php
namespace Bitrix\Sender\Integration\Crm\Connectors\Personalize;

use Bitrix;
use CCrmCurrencyHelper;
use CCrmStatus;

class Lead extends BasePersonalize
{
	public static function getEntityFields($entityType)
	{
		\Bitrix\Main\Localization\Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components/bitrix/crm.'.strtolower($entityType).'.edit/component.php'
		);
		$addressLabels = Bitrix\Crm\EntityAddress::getShortLabels();

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
				'Name' => GetMessage('CRM_FIELD_TITLE_LEAD'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => true,
				'personalizeCode' => 'NAME'
			),
			'STATUS_ID' => array(
				'Name' => GetMessage('CRM_FIELD_STATUS_ID'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('STATUS'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'STATUS_DESCRIPTION' => array(
				'Name' => GetMessage('CRM_FIELD_STATUS_DESCRIPTION'),
				'Type' => 'text',
				'Filterable' => false,
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
			'OPPORTUNITY' => array(
				'Name' => GetMessage('CRM_FIELD_OPPORTUNITY'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'CURRENCY_ID' => array(
				'Name' => GetMessage('CRM_FIELD_CURRENCY_ID'),
				'Type' => 'select',
				'Options' => CCrmCurrencyHelper::PrepareListItems(),
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
			'NAME' => array(
				'Name' => GetMessage('CRM_LEAD_FIELD_NAME'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'LAST_NAME' => array(
				'Name' => GetMessage('CRM_FIELD_LAST_NAME'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'SECOND_NAME' => array(
				'Name' => GetMessage('CRM_FIELD_SECOND_NAME'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'HONORIFIC' => array(
				'Name' => GetMessage('CRM_FIELD_HONORIFIC'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('HONORIFIC'),
				'Editable' => true,
				'Required' => false,
			),
			'BIRTHDATE' => array(
				'Name' => GetMessage('CRM_LEAD_EDIT_FIELD_BIRTHDATE'),
				'Type' => 'datetime',
				'Filterable' => true,
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
			'COMPANY_TITLE' => array(
				'Name' => GetMessage('CRM_FIELD_COMPANY_TITLE'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'POST' => array(
				'Name' => GetMessage('CRM_FIELD_POST'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'FULL_ADDRESS' => array(
				'Name' => GetMessage('CRM_FIELD_ADDRESS'),
				'Type' => 'text',
				'Filterable' => false,
				'Editable' => false,
				'Required' => false,
			),
			'ADDRESS' => array(
				'Name' => $addressLabels['ADDRESS'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_2' => array(
				'Name' => $addressLabels['ADDRESS_2'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_CITY' => array(
				'Name' => $addressLabels['CITY'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_POSTAL_CODE' => array(
				'Name' => $addressLabels['POSTAL_CODE'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_REGION' => array(
				'Name' => $addressLabels['REGION'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_COUNTRY' => array(
				'Name' => $addressLabels['COUNTRY'],
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'SOURCE_ID' => array(
				'Name' => GetMessage('CRM_FIELD_SOURCE_ID'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('SOURCE'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'SOURCE_DESCRIPTION' => array(
				'Name' => GetMessage('CRM_FIELD_SOURCE_DESCRIPTION'),
				'Type' => 'text',
				'Filterable' => false,
				'Editable' => true,
				'Required' => false,
			),
			"DATE_CREATE" => array(
				"Name" => GetMessage("CRM_LEAD_EDIT_FIELD_DATE_CREATE"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"DATE_MODIFY" => array(
				"Name" => GetMessage("CRM_LEAD_EDIT_FIELD_DATE_MODIFY"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			'IS_RETURN_CUSTOMER' => array(
				'Name' => GetMessage('CRM_DOCUMENT_LEAD_IS_RETURN_CUSTOMER'),
				'Type' => 'bool',
				'Editable' => false,
			),
		);

		$arResult += parent::getEntityFields($entityType);

		return $arResult;
	}

}
