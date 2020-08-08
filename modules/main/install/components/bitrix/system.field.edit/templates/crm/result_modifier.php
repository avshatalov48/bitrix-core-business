<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule("crm"))
	return;

global $USER;
$userPermissions = CCrmPerms::GetCurrentUserPermissions();
$arSupportedTypes = array(); // all entity types are defined in settings
$arParams['ENTITY_TYPE'] = array(); // only entity types are allowed for current user
$arSettings = $arParams['arUserField']['SETTINGS'];
if (isset($arSettings['LEAD']) && $arSettings['LEAD'] === 'Y')
{
	$arSupportedTypes[] = CCrmOwnerType::LeadName;
	if(CCrmLead::CheckReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::LeadName;
	}
}
if (isset($arSettings['CONTACT']) && $arSettings['CONTACT'] === 'Y')
{
	$arSupportedTypes[] = 'CONTACT';
	if(CCrmContact::CheckReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::ContactName;
	}
}
if (isset($arSettings['COMPANY']) && $arSettings['COMPANY'] === 'Y')
{
	$arSupportedTypes[] = 'COMPANY';
	if(CCrmCompany::CheckReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::CompanyName;
	}
}
if (isset($arSettings['DEAL']) && $arSettings['DEAL'] === 'Y')
{
	$arSupportedTypes[] = 'DEAL';
	if(CCrmDeal::CheckReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::DealName;
	}
}
if (isset($arSettings['QUOTE']) && $arSettings['QUOTE'] === 'Y')
{
	$arSupportedTypes[] = CCrmOwnerType::QuoteName;
	if(CCrmQuote::CheckReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::DealName;
	}
}
if (isset($arSettings['ORDER']) && $arSettings['ORDER'] === 'Y')
{
	$arSupportedTypes[] = CCrmOwnerType::OrderName;
	if(\Bitrix\Crm\Order\Permissions\Order::checkReadPermission(0, $userPermissions))
	{
		$arParams['ENTITY_TYPE'][] = CCrmOwnerType::OrderName;
	}
}
if (isset($arSettings['PRODUCT']) && $arSettings['PRODUCT'] === 'Y')
{
	$arSupportedTypes[] = 'PRODUCT';
	if(CCrmProduct::CheckReadPermission())
	{
		$arParams['ENTITY_TYPE'][] = 'PRODUCT';
	}
}

$arResult['PERMISSION_DENIED'] = (empty($arParams['ENTITY_TYPE']) ? true : false);

$arResult['PREFIX'] = count($arSupportedTypes) > 1 ? 'Y' : 'N';
if(!empty($arParams['usePrefix']))
	$arResult['PREFIX'] = 'Y';

$arResult['MULTIPLE'] = $arParams['arUserField']['MULTIPLE'];
if (!is_array($arResult['VALUE']))
	$arResult['VALUE'] = explode(';', $arResult['VALUE']);
else
{
	$ar = array();
	foreach ($arResult['VALUE'] as $value)
		foreach(explode(';', $value) as $val)
			if (!empty($val))
				$ar[$val] = $val;
	$arResult['VALUE'] = $ar;
}

$arResult['SELECTED'] = array();
$arResult['SELECTED_LIST'] = [];

$selectorEntityTypes = array();

$arResult['USE_SYMBOLIC_ID'] = (count($arParams['ENTITY_TYPE']) > 1);

$arResult['LIST_PREFIXES'] = [
	'DEAL' => 'D',
	'CONTACT' => 'C',
	'COMPANY' => 'CO',
	'LEAD' => 'L',
	'ORDER' => 'O'
];
$arResult['SELECTOR_ENTITY_TYPES'] = [
	'DEAL' => 'deals',
	'CONTACT' => 'contacts',
	'COMPANY' => 'companies',
	'LEAD' => 'leads',
	'ORDER' => 'orders'
];

foreach ($arResult['VALUE'] as $key => $value)
{
	if (empty($value))
	{
		continue;
	}

	if ($arResult['USE_SYMBOLIC_ID'])
	{
		$code = '';
		foreach($arResult['LIST_PREFIXES'] as $type => $prefix)
		{
			if (preg_match('/^'.$prefix.'_(\d+)$/i', $value, $matches))
			{
				$code = $arResult['SELECTOR_ENTITY_TYPES'][$type];
				break;
			}
		}
	}
	elseif (preg_match('/(\d+)$/i', $value, $matches))
	{
		foreach($arParams['ENTITY_TYPE'] as $entityType)
		{
			if (!empty($entityType))
			{
				$value = $arResult['LIST_PREFIXES'][$entityType].'_'.$matches[1];
				$code = $arResult['SELECTOR_ENTITY_TYPES'][$entityType];
				break;
			}
		}
	}

	if (!empty($code))
	{
		$arResult['SELECTED_LIST'][$value] = $code;
	}

	if($arResult['PREFIX'] === 'Y')
	{
		$arResult['SELECTED'][$value] = $value;

	}
	else
	{
		// Try to get raw entity ID
		$ary = explode('_', $value);
		if(count($ary) > 1)
		{
			$value = $ary[1];
		}

		$arResult['SELECTED'][$value] = $value;
	}
}

$arResult['ELEMENT'] = array();
$arResult['ENTITY_TYPE'] = array();
// last 50 entity
if (in_array('LEAD', $arParams['ENTITY_TYPE'], true))
{
	$hasNameFormatter = method_exists("CCrmLead", "PrepareFormattedName");
	$arResult['ENTITY_TYPE'][] = 'lead';

	if (method_exists('CCrmLead', 'GetTopIDs'))
	{
		$IDs = CCrmLead::GetTopIDs(50, 'DESC', $userPermissions);
		if (empty($IDs))
		{
			$obRes = new CDBResult();
			$obRes->InitFromArray(array());
		}
		else
		{
			$obRes = CCrmLead::GetListEx(
				array('ID' => 'DESC'),
				array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'FULL_NAME')
			);
		}
	}
	else
	{
		$obRes = CCrmLead::GetListEx(
			array('ID' => 'DESC'),
			array(),
			false,
			array('nTopCount' => 50),
			$hasNameFormatter
				? array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
				: array('ID', 'TITLE', 'FULL_NAME')
		);
	}
	while ($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'L_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		if($hasNameFormatter)
		{
			$description = CCrmLead::PrepareFormattedName(
				array(
					'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
					'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
					'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
					'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
				)
			);
		}
		else
		{
			$description = isset($arRes['FULL_NAME']) ? $arRes['FULL_NAME'] : '';
		}

		$arResult['ELEMENT'][] = Array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $description,
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_lead_show'),
				array(
					'lead_id' => $arRes['ID']
				)
			),
			'type'  => 'lead',
			'selected' => $sSelected
		);
	}
}
if (in_array('CONTACT', $arParams['ENTITY_TYPE'], true))
{
	$hasNameFormatter = method_exists("CCrmContact", "PrepareFormattedName");
	$arResult['ENTITY_TYPE'][] = 'contact';

	if (method_exists('CCrmContact', 'GetTopIDs'))
	{
		$IDs = CCrmContact::GetTopIDs(50, 'DESC', $userPermissions);
		if (empty($IDs))
		{
			$obRes = new CDBResult();
			$obRes->InitFromArray(array());
		}
		else
		{
			$obRes = CCrmContact::GetListEx(
				array('ID' => 'DESC'),
				array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'FULL_NAME', 'COMPANY_TITLE', 'PHOTO')
			);
		}
	}
	else
	{
		$obRes = CCrmContact::GetListEx(
			array('ID' => 'DESC'),
			array(),
			false,
			array('nTopCount' => 50),
			$hasNameFormatter
				? array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
				: array('ID', 'FULL_NAME', 'COMPANY_TITLE', 'PHOTO')
		);
	}
	while ($arRes = $obRes->Fetch())
	{
		$imageUrl = '';
		if (isset($arRes['PHOTO']) && $arRes['PHOTO'] > 0)
		{
			$arImg = CFile::ResizeImageGet($arRes['PHOTO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
			if(is_array($arImg) && isset($arImg['src']))
			{
				$imageUrl = $arImg['src'];
			}
		}

		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'C_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		if($hasNameFormatter)
		{
			$title = CCrmContact::PrepareFormattedName(
				array(
					'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
					'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
					'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
					'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
				)
			);
		}
		else
		{
			$title = isset($arRes['FULL_NAME']) ? $arRes['FULL_NAME'] : '';
		}

		$arResult['ELEMENT'][] = Array(
			'title' => $title,
			'desc'  => empty($arRes['COMPANY_TITLE']) ? '' : $arRes['COMPANY_TITLE'],
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_contact_show'),
				array(
					'contact_id' => $arRes['ID']
				)
			),
			'image' => $imageUrl,
			'type'  => 'contact',
			'selected' => $sSelected
		);
	}
}
if (in_array('COMPANY', $arParams['ENTITY_TYPE'], true))
{
	$arResult['ENTITY_TYPE'][] = 'company';

	if (method_exists('CCrmCompany', 'GetTopIDs'))
	{
		$IDs = CCrmCompany::GetTopIDs(50, 'DESC', $userPermissions);
		if (empty($IDs))
		{
			$obRes = new CDBResult();
			$obRes->InitFromArray(array());
		}
		else
		{
			$obRes = CCrmCompany::GetListEx(
				array('ID' => 'DESC'),
				array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY', 'LOGO')
			);
		}
	}
	else
	{
		$obRes = CCrmCompany::GetListEx(
			array('ID' => 'DESC'),
			array(),
			false,
			array('nTopCount' => 50),
			array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO')
		);
	}

	$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
	$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');

	while ($arRes = $obRes->Fetch())
	{
		$imageUrl = '';
		if (isset($arRes['LOGO']) && $arRes['LOGO'] > 0)
		{
			$arImg = CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
			if(is_array($arImg) && isset($arImg['src']))
			{
				$imageUrl = $arImg['src'];
			}
		}

		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'CO_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		$arDesc = Array();
		if (isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
			$arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
		if (isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
			$arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];


		$arResult['ELEMENT'][] = Array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => implode(', ', $arDesc),
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'),
				array(
					'company_id' => $arRes['ID']
				)
			),
			'image' => $imageUrl,
			'type'  => 'company',
			'selected' => $sSelected
		);
	}
}
if (in_array('DEAL', $arParams['ENTITY_TYPE'], true))
{
	$arResult['ENTITY_TYPE'][] = 'deal';

	if (method_exists('CCrmDeal', 'GetTopIDs'))
	{
		$IDs = CCrmDeal::GetTopIDs(50, 'DESC', $userPermissions);
		if (empty($IDs))
		{
			$obRes = new CDBResult();
			$obRes->InitFromArray(array());
		}
		else
		{
			$obRes = CCrmDeal::GetListEx(
				array('ID' => 'DESC'),
				array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
			);
		}
	}
	else
	{
		$obRes = CCrmDeal::GetListEx(
			array('ID' => 'DESC'),
			array(),
			false,
			array('nTopCount' => 50),
			array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
		);
	}

	while ($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'D_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
		$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

		$arResult['ELEMENT'][] = Array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $clientTitle,
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_deal_show'),
				array(
					'deal_id' => $arRes['ID']
				)
			),
			'type'  => 'deal',
			'selected' => $sSelected
		);
	}
}
if (in_array('QUOTE', $arParams['ENTITY_TYPE'], true))
{
	$arResult['ENTITY_TYPE'][] = 'quote';

	if (method_exists('CCrmQuote', 'GetTopIDs'))
	{
		$IDs = CCrmQuote::GetTopIDs(50, 'DESC', $userPermissions);
		if (empty($IDs))
		{
			$obRes = new CDBResult();
			$obRes->InitFromArray(array());
		}
		else
		{
			$obRes = CCrmQuote::GetList(
				array('ID' => 'DESC'),
				array('@ID' => $IDs, 'CHECK_PERMISSIONS' => 'N'),
				false,
				false,
				array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
			);
		}
	}
	else
	{
		$obRes = CCrmQuote::GetList(
			array('ID' => 'DESC'),
			array(),
			false,
			array('nTopCount' => 50),
			array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
		);
	}

	while ($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'Q_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
		$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

		$arResult['ELEMENT'][] = Array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $clientTitle,
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_quote_show'),
				array(
					'quote_id' => $arRes['ID']
				)
			),
			'type'  => 'quote',
			'selected' => $sSelected
		);
	}
}
if (in_array('PRODUCT', $arParams['ENTITY_TYPE'], true))
{
	$arResult['ENTITY_TYPE'][] = 'product';

	$arSelect = array('ID', 'NAME', 'PRICE', 'CURRENCY_ID');
	$arPricesSelect = $arVatsSelect = array();
	$arSelect = CCrmProduct::DistributeProductSelect($arSelect, $arPricesSelect, $arVatsSelect);
	$obRes = CCrmProduct::GetList(array('ID' => 'DESC'), array(), $arSelect, 50);

	$arProducts = $arProductId = array();
	while ($arRes = $obRes->Fetch())
	{
		foreach ($arPricesSelect as $fieldName)
			$arRes[$fieldName] = null;
		foreach ($arVatsSelect as $fieldName)
			$arRes[$fieldName] = null;
		$arProductId[] = $arRes['ID'];
		$arProducts[$arRes['ID']] = $arRes;
	}
	CCrmProduct::ObtainPricesVats($arProducts, $arProductId, $arPricesSelect, $arVatsSelect);
	unset($arProductId, $arPricesSelect, $arVatsSelect);

	foreach ($arProducts as $arRes)
	{
		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'PROD_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		$arResult['ELEMENT'][] = array(
			'title' => $arRes['NAME'],
			'desc' => CCrmProduct::FormatPrice($arRes),
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_product_show'),
				array(
					'product_id' => $arRes['ID']
				)
			),
			'type'  => 'product',
			'selected' => $sSelected
		);
	}
	unset($arProducts);
}
if (in_array('ORDER', $arParams['ENTITY_TYPE'], true))
{
	$arResult['ENTITY_TYPE'][] = 'order';

	$resultDB = \Bitrix\Crm\Order\Order::getList(array(
		'select' =>  array('ID', 'ACCOUNT_NUMBER'),
		'limit' => 50,
		'order' => array('ID' => 'DESC')
	));
	while ($arRes = $resultDB->fetch())
	{
		$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'O_'.$arRes['ID']: $arRes['ID'];
		if (isset($arResult['SELECTED'][$arRes['SID']]))
		{
			unset($arResult['SELECTED'][$arRes['SID']]);
			$sSelected = 'Y';
		}
		else
		{
			if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
			{
				unset($arResult['SELECTED'][$arRes['ID']]);
				$sSelected = 'Y';
			}
			else
			{
				$sSelected = 'N';
			}
		}

		$arResult['ELEMENT'][] = Array(
			'title' => $arRes['ACCOUNT_NUMBER'],
			'desc' => $arRes['ACCOUNT_NUMBER'],
			'id' => $arRes['SID'],
			'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_order_details'),
				array(
					'order_id' => $arRes['ID']
				)
			),
			'type'  => 'order',
			'selected' => $sSelected
		);
	}
}

if (!empty($arResult['SELECTED']))
{
	foreach ($arResult['SELECTED'] as $value)
	{
		if (is_numeric($value))
			$arSelected[$arParams['ENTITY_TYPE'][0]][] = $value;
		else
		{
			$ar = explode('_', $value);
			$arSelected[CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
		}
	}

	if ($arSettings['LEAD'] == 'Y'
		&& isset($arSelected['LEAD']) && !empty($arSelected['LEAD']))
	{
		$hasNameFormatter = method_exists("CCrmLead", "PrepareFormattedName");
		$obRes = CCrmLead::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $arSelected['LEAD']),
			false,
			false,
			$hasNameFormatter
				? array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
				: array('ID', 'TITLE', 'FULL_NAME')
		);
		$ar = Array();
		while ($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'L_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			if($hasNameFormatter)
			{
				$description = CCrmLead::PrepareFormattedName(
					array(
						'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
						'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
						'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
						'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
					)
				);
			}
			else
			{
				$description = isset($arRes['FULL_NAME']) ? $arRes['FULL_NAME'] : '';
			}

			$ar[] = Array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $description,
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_lead_show'),
					array(
						'lead_id' => $arRes['ID']
					)
				),
				'type'  => 'lead',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if ($arSettings['CONTACT'] == 'Y'
		&& isset($arSelected['CONTACT']) && !empty($arSelected['CONTACT']))
	{
		$hasNameFormatter = method_exists("CCrmContact", "PrepareFormattedName");
		$obRes = CCrmContact::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $arSelected['CONTACT']),
			false,
			false,
			$hasNameFormatter
				? array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
				: array('ID', 'FULL_NAME', 'COMPANY_TITLE', 'PHOTO')
		);
		$ar = Array();
		while ($arRes = $obRes->Fetch())
		{
			$imageUrl = '';
			if (isset($arRes['PHOTO']) && $arRes['PHOTO'] > 0)
			{
				$arImg = CFile::ResizeImageGet($arRes['PHOTO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
				if(is_array($arImg) && isset($arImg['src']))
				{
					$imageUrl = $arImg['src'];
				}
			}

			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'C_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			if($hasNameFormatter)
			{
				$title = CCrmContact::PrepareFormattedName(
					array(
						'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
						'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
						'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
						'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
					)
				);
			}
			else
			{
				$title = isset($arRes['FULL_NAME']) ? $arRes['FULL_NAME'] : '';
			}

			$ar[] = Array(
				'title' => $title,
				'desc'  => empty($arRes['COMPANY_TITLE']) ? '': $arRes['COMPANY_TITLE'],
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_contact_show'),
					array('contact_id' => $arRes['ID'])
				),
				'image' => $imageUrl,
				'type'  => 'contact',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if ($arSettings['COMPANY'] == 'Y'
		&& isset($arSelected['COMPANY']) && !empty($arSelected['COMPANY']))
	{
		$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
		$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
		$arSelect = array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY',  'LOGO');
		$obRes = CCrmCompany::GetList(array('ID' => 'DESC'), Array('ID' => $arSelected['COMPANY']), $arSelect);
		$ar = Array();
		while ($arRes = $obRes->Fetch())
		{
			$imageUrl = '';
			if (isset($arRes['LOGO']) && $arRes['LOGO'] > 0)
			{
				$arImg = CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
				if(is_array($arImg) && isset($arImg['src']))
				{
					$imageUrl = $arImg['src'];
				}
			}

			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'CO_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}


			$arDesc = Array();
			if (isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
				$arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
			if (isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
				$arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];

			$ar[] = Array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => implode(', ', $arDesc),
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_company_show'),
					array(
						'company_id' => $arRes['ID']
					)
				),
				'image' => $imageUrl,
				'type'  => 'company',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if ($arSettings['DEAL'] == 'Y'
	&& isset($arSelected['DEAL']) && !empty($arSelected['DEAL']))
	{
		$arSelect = array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME');
		$ar = Array();
		$obRes = CCrmDeal::GetList(array('ID' => 'DESC'), Array('ID' => $arSelected['DEAL']), $arSelect);
		while ($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'D_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
			$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

			$ar[] = Array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $clientTitle,
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_deal_show'),
					array(
						'deal_id' => $arRes['ID']
					)
				),
				'type'  => 'deal',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if ($arSettings['ORDER'] == 'Y'
		&& isset($arSelected['ORDER']) && !empty($arSelected['ORDER']))
	{
		$ar = Array();
		$resultDB = \Bitrix\Crm\Order\Order::getList(array(
			'filter' => array('=ID' => $arSelected['ORDER']),
			'select' =>  array('ID', 'ACCOUNT_NUMBER'),
			'order' => array('ID' => 'DESC')
		));
		while ($arRes = $resultDB->fetch())
		{
			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'O_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			$ar[] = array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['ACCOUNT_NUMBER'])),
				'desc' => $arRes['ACCOUNT_NUMBER'],
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_order_details'),
					array(
						'order_id' => $arRes['ID']
					)
				),
				'type'  => 'order',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if ($arSettings['QUOTE'] == 'Y'
		&& isset($arSelected['QUOTE']) && !empty($arSelected['QUOTE']))
	{
		$arSelect = array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME');
		$ar = Array();
		$obRes = CCrmQuote::GetList(array('ID' => 'DESC'), Array('ID' => $arSelected['QUOTE']), false, false, $arSelect);
		while ($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'Q_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
			$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

			$ar[] = Array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $clientTitle,
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_quote_show'),
					array(
						'quote_id' => $arRes['ID']
					)
				),
				'type'  => 'quote',
				'selected' => $sSelected
			);
		}
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
	if (isset($arSettings['PRODUCT'])
		&& $arSettings['PRODUCT'] == 'Y'
		&& isset($arSelected['PRODUCT'])
		&& !empty($arSelected['PRODUCT']))
	{
		$ar = array();
		$arSelect = array('ID', 'NAME', 'PRICE', 'CURRENCY_ID');
		$arPricesSelect = $arVatsSelect = array();
		$arSelect = CCrmProduct::DistributeProductSelect($arSelect, $arPricesSelect, $arVatsSelect);
		$obRes = CCrmProduct::GetList(
			array('ID' => 'DESC'),
			array('ID' => $arSelected['PRODUCT']),
			$arSelect
		);

		$arProducts = $arProductId = array();
		while ($arRes = $obRes->Fetch())
		{
			foreach ($arPricesSelect as $fieldName)
				$arRes[$fieldName] = null;
			foreach ($arVatsSelect as $fieldName)
				$arRes[$fieldName] = null;
			$arProductId[] = $arRes['ID'];
			$arProducts[$arRes['ID']] = $arRes;
		}
		CCrmProduct::ObtainPricesVats($arProducts, $arProductId, $arPricesSelect, $arVatsSelect);
		unset($arProductId, $arPricesSelect, $arVatsSelect);

		foreach ($arProducts as $arRes)
		{
			$arRes['SID'] = $arResult['PREFIX'] == 'Y'? 'D_'.$arRes['ID']: $arRes['ID'];
			if (isset($arResult['SELECTED'][$arRes['SID']]))
			{
				unset($arResult['SELECTED'][$arRes['SID']]);
				$sSelected = 'Y';
			}
			else
			{
				if(!empty($arParams['usePrefix']) && isset($arResult['SELECTED'][$arRes['ID']]))
				{
					unset($arResult['SELECTED'][$arRes['ID']]);
					$sSelected = 'Y';
				}
				else
				{
					$sSelected = 'N';
				}
			}

			$ar[] = array(
				'title' => $arRes['NAME'],
				'desc' => CCrmProduct::FormatPrice($arRes),
				'id' => $arRes['SID'],
				'url' => CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_product_show'),
					array(
						'product_id' => $arRes['ID']
					)
				),
				'type'  => 'product',
				'selected' => $sSelected
			);
		}
		unset($arProducts);
		$arResult['ELEMENT'] = array_merge($ar, $arResult['ELEMENT']);
	}
}

$arParams['createNewEntity'] = ($arParams['createNewEntity'] && \Bitrix\Crm\Settings\LayoutSettings::getCurrent()->isSliderEnabled());

if(!empty($arParams['createNewEntity']))
{
	if(!empty($arResult['ENTITY_TYPE']))
	{
		if(count($arResult['ENTITY_TYPE']) > 1)
		{
			$arResult['PLURAL_CREATION'] = true;
		}
		else
		{
			$arResult['PLURAL_CREATION'] = false;
			$arResult['CURRENT_ENTITY_TYPE'] = current($arResult['ENTITY_TYPE']);
		}
	}
	
	$arResult['LIST_ENTITY_CREATE_URL'] = array();
	foreach($arResult['ENTITY_TYPE'] as $entityType)
	{

		$arResult['LIST_ENTITY_CREATE_URL'][$entityType] = \CCrmUrlUtil::addUrlParams(
			\CCrmOwnerType::getDetailsUrl(
				CCrmOwnerType::resolveID($entityType),
				0,
				false,
				array('ENABLE_SLIDER' => true)
			),
			array('init_mode' => 'edit')
		);
	}
}
?>