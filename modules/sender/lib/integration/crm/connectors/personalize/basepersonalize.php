<?php

namespace Bitrix\Sender\Integration\Crm\Connectors\Personalize;

use Bitrix;
use Bitrix\Main\UI\Filter\AdditionalDateType;

abstract class BasePersonalize
{
	private const COMMA = ',';

	public static function getMap()
	{
		return [
			'NAME' => [
				'CONTACT.NAME',
				'LEAD.TITLE',
				'COMPANY.TITLE'
			],
			'ID' => [
				'CONTACT.ID',
				'LEAD.ID',
				'COMPANY.ID'
			],
			'EMAIL' => [
				'CONTACT.EMAIL',
				'LEAD.EMAIL',
				'COMPANY.EMAIL'
			],
			'PHONE' => [
				'CONTACT.PHONE',
				'LEAD.PHONE',
				'COMPANY.PHONE'
			],
		];
	}
	public static function getEntityFields($entityType)
	{
		\Bitrix\Main\Localization\Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components/bitrix/crm.'.strtolower($entityType).'.edit/component.php'
		);

		$arResult = [];

		$arResult += static::getAssignedByFields();

		$ar = \CCrmFieldMulti::GetEntityTypeList();
		foreach ($ar as $typeId => $arFields)
		{
			foreach ($arFields as $valueType => $valueName)
			{
				$arResult[$typeId.'_'.$valueType] = [
					'Name'       => $valueName,
					'Type'       => 'string',
					"Filterable" => true,
					"Editable"   => false,
					"Required"   => false,
				];
			}
		}

		return $arResult;
	}

	protected static function getAssignedByFields()
	{
		\Bitrix\Main\Localization\Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/crm/classes/general/crm_document.php'
		);

		return [
			'ASSIGNED_BY_EMAIL'           => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_EMAIL'),
				'Type' => 'string',
			],
			'ASSIGNED_BY_WORK_PHONE'      => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_WORK_PHONE'),
				'Type' => 'string',
			],
			'ASSIGNED_BY_PERSONAL_MOBILE' => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_PERSONAL_MOBILE'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_PHONE_INNER'  => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_PHONE_INNER'),
				'Type' => 'string',
			],

			'ASSIGNED_BY.LOGIN'         => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_LOGIN'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.ACTIVE'        => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_ACTIVE'),
				'Type' => 'bool',
			],
			'ASSIGNED_BY.LAST_NAME'     => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_LAST_NAME'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.NAME'          => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_NAME'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.SECOND_NAME'   => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_SECOND_NAME'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.WORK_POSITION' => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_WORK_POSITION'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.PERSONAL_WWW'  => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_PERSONAL_WWW'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.PERSONAL_CITY' => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_PERSONAL_CITY'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_SKYPE'      => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_SKYPE'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_TWITTER'    => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_TWITTER'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_FACEBOOK'   => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_FACEBOOK'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_LINKEDIN'   => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_LINKEDIN'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_XING'       => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_XING'),
				'Type' => 'string',
			],
			'ASSIGNED_BY.UF_WEB_SITES'  => [
				'Name' => GetMessage('CRM_DOCUMENT_FIELD_ASSIGNED_BY_UF_WEB_SITES'),
				'Type' => 'string',
			],
		];
	}

	/**
	 * Get filter user fields.
	 *
	 * @param integer $entityTypeId Entity type ID.
	 *
	 * @return array
	 */
	public static function getFilterUserFields($entityTypeId)
	{
		$list = [];
		$ufManager = is_object($GLOBALS['USER_FIELD_MANAGER'])? $GLOBALS['USER_FIELD_MANAGER'] : null;
		if (!$ufManager)
		{
			return $list;
		}

		$ufEntityId = \CCrmOwnerType::resolveUserFieldEntityID($entityTypeId);
		$crmUserType = new \CCrmUserType($ufManager, $ufEntityId);
		$logicFilter = [];
		$crmUserType->prepareListFilterFields($list, $logicFilter);
		$originalList = $crmUserType->getFields();
		$restrictedTypes = ['address', 'file', 'crm', 'resourcebooking'];

		$list = array_filter(
			$list,
			function($field) use ($originalList, $restrictedTypes)
			{
				if (empty($originalList[$field['id']]))
				{
					return false;
				}

				$type = $originalList[$field['id']]['USER_TYPE']['USER_TYPE_ID'];

				return !in_array($type, $restrictedTypes);
			}
		);

		foreach ($list as $index => $field)
		{
			if ($field['type'] === 'date')
			{
				$list[$index]['include'] = [
					AdditionalDateType::CUSTOM_DATE,
					AdditionalDateType::PREV_DAY,
					AdditionalDateType::NEXT_DAY,
					AdditionalDateType::MORE_THAN_DAYS_AGO,
					AdditionalDateType::AFTER_DAYS,
				];
				if (!isset($list[$index]['allow_years_switcher']))
				{
					$list[$index]['allow_years_switcher'] = true;
				}
			}
			if ($originalList[$field['id']]['MULTIPLE'] == 'Y')
			{
				$list[$index]['multiple_uf'] = true;
			}
		}

		return $list;
	}

	/**
	 * @param string $entityType
	 * @param array $entityIds
	 * @param array|string[] $usedFields
	 * @param string $sortBy
	 * @param string $sortOrder
	 *
	 * @return array
	 * @throws Bitrix\Main\ArgumentException
	 * @throws Bitrix\Main\ObjectPropertyException
	 * @throws Bitrix\Main\SystemException
	 */
	public static function getData(
		string $entityType,
		array $entityIds,
		array $usedFields = ['*'],
		string $sortBy = 'id',
		string $sortOrder = 'asc'
	)
	{
		\Bitrix\Main\Localization\Loc::loadMessages(
			$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/crm/classes/general/crm_fields.php'
		);

		$arResult = null;
		$entityType = ucfirst(strtolower($entityType));
		$className = 'CCrm'.$entityType;
		$dbDocumentList = $className::GetListEx(
			[],
			[
				"__CONDITIONS"      => [
					[
						"SQL" => "L.ID IN (".implode(",", $entityIds).")"
					]
				],
				"CHECK_PERMISSIONS" => "N"
			],
			false,
			false,
			array_merge(
				$usedFields,
				['UF_*', 'ASSIGNED_BY_ID']
			)
		);
		$data = [];
		while (($objDocument = $dbDocumentList->Fetch()) !== false)
		{
			$assignedByID = isset($objDocument['ASSIGNED_BY_ID'])? intval($objDocument['ASSIGNED_BY_ID']) : 0;

			if ($assignedByID > 0)
			{
				self::addAssignedByFieldsValue($assignedByID, $objDocument);
			}

			self::addAddressFieldsValue($entityType,  $usedFields, $objDocument);
			self::addMultiFieldsValue($usedFields, $entityType,  $objDocument);
			self::addUserFieldsValue($className, $objDocument);

			//communications
			$typeId = \CCrmOwnerType::ResolveID($entityType);
			$objDocument += static::getCommunicationFieldsValues($typeId, $objDocument['ID']);

			$data[$objDocument['ID']] = $objDocument;
		}

		return $data;
	}

	/**
	 * @param $entityType
	 * @param $objDocument
	 */
	private static function addAddressFieldsValue($entityType, $usedFields, &$objDocument)
	{
		$rqAddr = new Bitrix\Crm\RequisiteAddress();

		$res = $rqAddr->getList(
			array(
				'filter' => array(
					'ENTITY_TYPE_ID' => \CCrmOwnerType::Requisite,
					'ANCHOR_TYPE_ID' => \CCrmOwnerType::ResolveID($entityType),
					'ANCHOR_ID' => $objDocument['ID']
				),
				'select' => array(
					'ADDRESS_TYPE_ID' => 'TYPE_ID',
					'ADDRESS' => 'ADDRESS_1',
					'ADDRESS_2',
					'ADDRESS_CITY' => 'CITY',
					'ADDRESS_POSTAL_CODE' => 'POSTAL_CODE',
					'ADDRESS_REGION' => 'REGION',
					'ADDRESS_PROVINCE' => 'PROVINCE',
					'ADDRESS_COUNTRY' => 'COUNTRY',
					'ADDRESS_COUNTRY_CODE' => 'COUNTRY_CODE'
				)
			)
		)->fetchAll();

		if(!$res)
		{
			return;
		}

		foreach ($res as $addresses)
		{
			if(
				(int)$addresses['ADDRESS_TYPE_ID'] === (int)\Bitrix\Crm\EntityAddressType::Primary
			)
			{
				foreach ($addresses as $key => $address)
				{
					$objDocument[$key] = $address;
				}
			}
			if(isset($usedFields[strtoupper($entityType).'.ADDRESS_LEGAL']) &&
				(int)$addresses['ADDRESS_TYPE_ID'] === \Bitrix\Crm\EntityAddressType::Registered)
			{
				$objDocument['ADDRESS_LEGAL'] = self::buildAddress($entityType, $addresses);
			}
		}
	}

	private static function buildAddress($entityType,$address)
	{
		$addressFormatter = '\\Bitrix\\Crm\\Format\\'.
			ucfirst(strtolower($entityType)).
			'AddressFormatter';

		return $addressFormatter::format(
			[
				'ADDRESS' => $address['ADDRESS'],
				'ADDRESS_2' => $address['ADDRESS_2'],
				'ADDRESS_CITY' => $address['ADDRESS_CITY'],
				'ADDRESS_REGION' => $address['ADDRESS_REGION'],
				'ADDRESS_PROVINCE' => $address['ADDRESS_PROVINCE'],
				'ADDRESS_POSTAL_CODE' => $address['ADDRESS_POSTAL_CODE'],
				'ADDRESS_COUNTRY' => $address['ADDRESS_COUNTRY']
			],
			array('SEPARATOR' => Bitrix\Crm\Format\AddressSeparator::Comma)
		);
	}

	/**
	 * @param $usedFields
	 * @param $entityType
	 * @param $objDocument
	 */
	private static function addMultiFieldsValue($usedFields, $entityType, &$objDocument)
	{
		$userMultiFields = \CCrmFieldMulti::GetAllEntityFields($entityType, $objDocument['ID']);

		foreach ($usedFields as $usedField)
		{
			self::checkUsedField($usedField, $entityType, $objDocument);
			$splitedField = explode('_', $usedField);

			if(
				in_array($splitedField[0], ['PHONE', 'IM', 'EMAIL', 'WEB'])
				&& isset($userMultiFields[$splitedField[0]])
			)
			{
				foreach ($userMultiFields[$splitedField[0]] as $field)
				{
					if(!isset($splitedField[1]))
					{
						$objDocument[$usedField] = $field['VALUE'];
						continue;
					}

					if($field['VALUE_TYPE'] === $splitedField[1])
					{
						$objDocument[$usedField] = $field['VALUE'];
					}
				}
			}
		}
	}

	/**
	 * @param $className
	 * @param $objDocument
	 */
	private static function addUserFieldsValue($className, &$objDocument)
	{
		$userFieldsList = $className::GetUserFields();

		if (is_array($userFieldsList))
		{
			foreach ($userFieldsList as $userFieldName => $userFieldParams)
			{
				$fieldTypeID = isset($userFieldParams['USER_TYPE'])? $userFieldParams['USER_TYPE']['USER_TYPE_ID']
					: '';
				$isFieldMultiple = isset($userFieldParams['MULTIPLE']) && $userFieldParams['MULTIPLE'] === 'Y';
				$fieldSettings = isset($userFieldParams['SETTINGS'])? $userFieldParams['SETTINGS'] : [];

				if (isset($objDocument[$userFieldName]))
				{
					$fieldValue = $objDocument[$userFieldName];
				}
				elseif (isset($fieldSettings['DEFAULT_VALUE']))
				{
					$fieldValue = $fieldSettings['DEFAULT_VALUE'];
				}

				if ($fieldTypeID == 'employee')
				{
					if (!$isFieldMultiple)
					{
						$objDocument[$userFieldName] = $fieldValue;
					}
					elseif (is_array($fieldValue))
					{
						$objDocument[$userFieldName] = [];
						foreach ($fieldValue as $value)
						{
							$objDocument[$userFieldName][] = $value;
						}
					}
				}
				elseif ($fieldTypeID === 'boolean')
				{
					$objDocument[$userFieldName] = self::getBool($fieldValue)? 'Y' : 'N';
				}
			}
		}
	}

	private static function getBool($value)
	{
		if (empty($value) || $value === 'false' || is_int($value) && ($value == 0) || (mb_strtoupper($value) == 'N'))
		{
			return false;
		}

		return (bool)$value;
	}
	/**
	 * @param $assignedByID
	 * @param $objDocument
	 */
	private static function addAssignedByFieldsValue($assignedByID, &$objDocument)
	{
		$sortBy = 'id';
		$sortOrder = 'asc';

		$dbUsers = \CUser::GetList(
			$sortBy,
			$sortOrder,
			['ID' => $assignedByID],
			[
				'SELECT' => [
					'EMAIL',
					'PHONE',
					'IM',
					'UF_SKYPE',
					'UF_TWITTER',
					'UF_FACEBOOK',
					'UF_LINKEDIN',
					'UF_XING',
					'UF_WEB_SITES',
					'UF_PHONE_INNER',
				]
			]
		);

		$arUser = is_object($dbUsers)? $dbUsers->Fetch() : null;
		$objDocument['ASSIGNED_BY_EMAIL'] = is_array($arUser)? $arUser['EMAIL'] : '';
		$objDocument['ASSIGNED_BY_WORK_PHONE'] = is_array($arUser)? $arUser['WORK_PHONE'] : '';
		$objDocument['ASSIGNED_BY_PERSONAL_MOBILE'] = is_array($arUser)? $arUser['PERSONAL_MOBILE'] : '';

		$objDocument['ASSIGNED_BY.LOGIN'] = is_array($arUser)? $arUser['LOGIN'] : '';
		$objDocument['ASSIGNED_BY.ACTIVE'] = is_array($arUser)? $arUser['ACTIVE'] : '';
		$objDocument['ASSIGNED_BY.NAME'] = is_array($arUser)? $arUser['NAME'] : '';
		$objDocument['ASSIGNED_BY.LAST_NAME'] = is_array($arUser)? $arUser['LAST_NAME'] : '';
		$objDocument['ASSIGNED_BY.SECOND_NAME'] = is_array($arUser)? $arUser['SECOND_NAME'] : '';
		$objDocument['ASSIGNED_BY.WORK_POSITION'] = is_array($arUser)? $arUser['WORK_POSITION'] : '';
		$objDocument['ASSIGNED_BY.PERSONAL_WWW'] = is_array($arUser)? $arUser['PERSONAL_WWW'] : '';
		$objDocument['ASSIGNED_BY.PERSONAL_CITY'] = is_array($arUser)? $arUser['PERSONAL_CITY'] : '';
		$objDocument['ASSIGNED_BY.UF_SKYPE'] = is_array($arUser)? $arUser['UF_SKYPE'] : '';
		$objDocument['ASSIGNED_BY.UF_TWITTER'] = is_array($arUser)? $arUser['UF_TWITTER'] : '';
		$objDocument['ASSIGNED_BY.UF_FACEBOOK'] = is_array($arUser)? $arUser['UF_FACEBOOK'] : '';
		$objDocument['ASSIGNED_BY.UF_LINKEDIN'] = is_array($arUser)? $arUser['UF_LINKEDIN'] : '';
		$objDocument['ASSIGNED_BY.UF_XING'] = is_array($arUser)? $arUser['UF_XING'] : '';
		$objDocument['ASSIGNED_BY.UF_WEB_SITES'] = is_array($arUser)? $arUser['UF_WEB_SITES'] : '';
		$objDocument['ASSIGNED_BY.UF_PHONE_INNER'] = is_array($arUser)? $arUser['UF_PHONE_INNER'] : '';
	}

	/**
	 * @param $usedField
	 * @param $entityType
	 * @param $objDocument
	 */
	private static function checkUsedField($usedField, $entityType, &$objDocument)
	{
		switch ($usedField)
		{
			case 'FULL_ADDRESS':
				$objDocument['FULL_ADDRESS'] = self::buildAddress($entityType, $objDocument);
				break;
			case 'BANKING_DETAILS':
				$requisites = Bitrix\Crm\EntityRequisite::getSingleInstance()
					->getList(
						[
							'filter' => [
								'=ENTITY_TYPE_ID' => \CCrmOwnerType::ResolveID($entityType),
								'=ENTITY_ID'      => $objDocument['ID']
							],
							'select' => ['ID'],
							'limit' => '1'
						]
					)->fetch();
				$titleMap =  Bitrix\Crm\EntityBankDetail::getSingleInstance()->getRqFieldTitleMap();

				$details = Bitrix\Crm\EntityBankDetail::getByOwners(
					\CCrmOwnerType::Requisite, [$requisites['ID']]
				);
				$objDocument['BANKING_DETAILS'] = '';
				$tmpDetails = [];
				foreach ($details[$requisites['ID']] as $detail)
				{
					foreach ($titleMap as $key => $title)
					{
						if(isset($title[$detail['COUNTRY_ID']]))
						{
							$tmpDetails[] =
								$title[$detail['COUNTRY_ID']] . ': ' . $detail[$key];
						}
					}
				}
				$objDocument['BANKING_DETAILS'] = implode(self::COMMA, $tmpDetails);
				break;
			case 'MODIFY_BY_ID':
			case 'CREATED_BY_ID':
				$dbUsers = \CUser::GetList(
					$sortBy,
					$sortOrder,
					['ID' => $objDocument[$usedField]],
					[
						'SELECT' => [
							'NAME',
							'LAST_NAME'
						]
					]
				);

				$arUser = is_object($dbUsers)? $dbUsers->Fetch() : null;
				$objDocument[$usedField] =
					is_array($arUser)? implode(" ", [
						$arUser['NAME'],
						$arUser['LAST_NAME']
					]
					) : '';
				break;
			case 'CONTACT_ID':
				$contactID = \Bitrix\Crm\Binding\ContactCompanyTable::getCompanyContactIDs($objDocument['ID']);
				$contact = \CCrmContact::GetByID(
					$contactID[0]
				);
				if(!empty($contact))
				{
					$objDocument['CONTACT_ID'] = implode(" ", [
							$contact['NAME'],
							$contact['LAST_NAME']
						]
					);
				}
				break;
			case 'COMPANY_ID':
				$objDocument['COMPANY_ID'] = \CCrmCompany::GetByID(
					$objDocument['COMPANY_ID']
				)['TITLE'];
				break;
			case 'COMPANY_IDS':
				$contactCompany = "\Bitrix\Crm\Binding\ContactCompanyTable";
				$companies = $contactCompany::getContactCompanyIDs(
					$objDocument['ID']
				);

				$companiesTitle = [];
				foreach ($companies as $company)
				{
					$companiesTitle[] = \CCrmCompany::GetByID(
						$company
					)['TITLE'];
				}
				$objDocument['COMPANY_IDS'] = implode(', ', $companiesTitle);
				break;
			case 'IS_RETURN_CUSTOMER':
			case 'ASSIGNED_BY.ACTIVE':
			case 'OPENED':
			case 'EXPORT':
				$objDocument[$usedField] = $objDocument[$usedField]  === 'Y'
					? GetMessage('CRM_FIELDS_TYPE_B_VALUE_YES') : GetMessage('CRM_FIELDS_TYPE_B_VALUE_NO');
				break;
			case 'HONORIFIC':
				$honorifics = \CCrmStatus::GetStatus('HONORIFIC');
				$objDocument['HONORIFIC'] = $honorifics[$objDocument['HONORIFIC']]['NAME'];
				break;
			case 'ORIGINATOR_ID':
				/**
				 * @var \CDBResult $originator
				 */
				$originator = \CCrmExternalSale::GetList([],[
					['=ID' => $objDocument['ORIGINATOR_ID']]
				])->Fetch();

				if(isset($originator[0]))
				{
					$objDocument['ORIGINATOR_ID'] = $originator[0]['NAME'];
				}
				break;
			case 'INDUSTRY':
				$sources = \CCrmStatus::GetStatus('INDUSTRY');
				$objDocument['INDUSTRY'] = $sources[$objDocument['INDUSTRY']]['NAME'];
				break;
			case 'SOURCE_ID':
				$sources = \CCrmStatus::GetStatus('SOURCE');
				$objDocument['SOURCE_ID'] = $sources[$objDocument['SOURCE_ID']]['NAME'];
				break;
			case 'COMPANY_TYPE':
				$types = \CCrmStatus::GetStatus('COMPANY_TYPE');
				$objDocument['COMPANY_TYPE'] = $types[$objDocument['COMPANY_TYPE']]['NAME'];
				break;
			case 'TYPE_ID':
				$types = \CCrmStatus::GetStatus('CONTACT_TYPE');
				$objDocument['TYPE_ID'] = $types[$objDocument['TYPE_ID']]['NAME'];
				break;
			case 'STATUS_ID':
				$statuses = \CCrmStatus::GetStatus('STATUS');
				$objDocument['STATUS_ID'] = $statuses[$objDocument['STATUS_ID']]['NAME'];
				break;
			case 'EMPLOYEES':
				$employees = \CCrmStatus::GetStatus('EMPLOYEES');
				$objDocument['EMPLOYEES'] = $employees[$objDocument['EMPLOYEES']]['NAME'];
				break;
		}
	}

	/**
	 * @param $typeId
	 * @param $ids
	 *
	 * @return string[]
	 * @throws Bitrix\Main\ArgumentException
	 * @throws Bitrix\Main\ObjectPropertyException
	 * @throws Bitrix\Main\SystemException
	 */
	protected static function getCommunicationFieldsValues($typeId, $ids)
	{
		$callId = Bitrix\Crm\Activity\Provider\Call::getId();
		$emailId = Bitrix\Crm\Activity\Provider\Email::getId();
		$olId = Bitrix\Crm\Activity\Provider\OpenLine::getId();
		$webFormId = Bitrix\Crm\Activity\Provider\WebForm::getId();

		$callDate = $emailDate = $olDate = $webFormDate = null;

		$ormRes = \Bitrix\Crm\ActivityTable::getList(
			[
				'select' => ['END_TIME', 'PROVIDER_ID'],
				'filter' => [
					'=COMPLETED'              => 'Y',
					'@PROVIDER_ID'            => [$callId, $emailId, $olId, $webFormId],
					'=BINDINGS.OWNER_TYPE_ID' => $typeId,
					'@BINDINGS.OWNER_ID'      => $ids,
				],
				'order'  => ['END_TIME' => 'DESC']
			]
		);

		while ($row = $ormRes->fetch())
		{
			if ($callDate === null)
			{
				if ($row['PROVIDER_ID'] === $callId)
				{
					$callDate = $row['END_TIME'];
				}
			}
			if ($emailDate === null)
			{
				if ($row['PROVIDER_ID'] === $emailId)
				{
					$emailDate = $row['END_TIME'];
				}
			}
			if ($olDate === null)
			{
				if ($row['PROVIDER_ID'] === $olId)
				{
					$olDate = $row['END_TIME'];
				}
			}
			if ($webFormDate === null)
			{
				if ($row['PROVIDER_ID'] === $webFormId)
				{
					$webFormDate = $row['END_TIME'];
				}
			}

			if ($callDate !== null && $emailDate !== null && $olDate !== null && $webFormDate !== null)
			{
				break;
			}
		}

		return [
			'COMMUNICATIONS.LAST_CALL_DATE'  => (string)$callDate,
			'COMMUNICATIONS.LAST_EMAIL_DATE' => (string)$emailDate,
			'COMMUNICATIONS.LAST_OL_DATE'    => (string)$olDate,
			'COMMUNICATIONS.LAST_FORM_DATE'  => (string)$webFormDate,
		];
	}
}
