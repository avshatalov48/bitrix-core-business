<?php
/**
 * @deprecated
 *
 * use bitrix:crm.entity.selector.ajax
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 */

if(!\Bitrix\Main\Loader::includeModule('crm'))
{
	return;
}

$preloadItemsCnt = 10;

$allowedEntityTypes = array('lead', 'contact', 'company', 'deal', 'quote');
$entityPrefix = array('lead' => 'L', 'contact' => 'C', 'company' => 'CO', 'deal' => 'D', 'quote' => 'Q');

$arParams['MULTIPLE'] = $arParams['MULTIPLE'] == 'Y' ? 'Y' : 'N';
$arParams['ENTITY_TYPE'] = is_array($arParams['ENTITY_TYPE'])
	? array_intersect(
		array_values($arParams['ENTITY_TYPE']),
		$allowedEntityTypes
	)
	: array();

if(count($arParams['ENTITY_TYPE']) <= 0)
{
	$arParams['ENTITY_TYPE'] = array('lead', 'contact', 'company');
}

$arParams['NAME'] = trim($arParams['NAME']);
if($arParams['NAME'] == '')
{
	$arParams['NAME'] = 'restCrmSelector';
}

$selectedList = array();

if(is_array($arParams['VALUE']))
{
	foreach($arParams['VALUE'] as $key => $valueList)
	{
		if(in_array($key, $allowedEntityTypes) && is_array($valueList))
		{
			foreach($valueList as $item)
			{
				if(intval($item) > 0)
				{
					$value = $entityPrefix[$key].'_'.intval($item);
					$selectedList[$value] = $value;
				}
			}
		}
		elseif(
			!is_array($valueList)
			&& preg_match('/^('.implode('|', $entityPrefix).')_\d+$/i', $valueList)
		)
		{
			$selectedList[$valueList] = $valueList;
		}
	}
}

$selectedList = array_unique($selectedList);

if($arParams['MULTIPLE'] == 'N' && count($selectedList) > 1)
{
	$item = array_shift($selectedList);
	$selectedList = array($item => $item);
}

$arResult['ELEMENT'] = array();

if(in_array('lead', $arParams['ENTITY_TYPE']))
{
	$obRes = \CCrmLead::GetListEx(
		array('ID' => 'DESC'),
		array(),
		false,
		array('nTopCount' => $preloadItemsCnt),
		array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
	);
	while($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $entityPrefix['lead'].'_'.$arRes['ID'];
		if(isset($selectedList[$arRes['SID']]))
		{
			unset($selectedList[$arRes['SID']]);
			$selected = 'Y';
		}
		else
		{
			$selected = 'N';
		}

		$description = \CCrmLead::PrepareFormattedName(
			array(
				'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
				'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
				'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
				'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
			)
		);

		$arResult['ELEMENT'][] = array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $description,
			'id' => $arRes['SID'],
			'url' => \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_lead_show'),
				array(
					'lead_id' => $arRes['ID']
				)
			),
			'type' => 'lead',
			'selected' => $selected
		);
	}
}

if(in_array('contact', $arParams['ENTITY_TYPE']))
{
	$obRes = \CCrmContact::GetListEx(
		array('ID' => 'DESC'),
		array(),
		false,
		array('nTopCount' => $preloadItemsCnt),
		array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
	);
	while($arRes = $obRes->Fetch())
	{
		$arImg = array();
		if(!empty($arRes['PHOTO'])&& intval($arRes['PHOTO']) > 0)
		{
			$arImg = \CFile::ResizeImageGet($arRes['PHOTO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
		}

		$arRes['SID'] = $entityPrefix['contact'].'_'.$arRes['ID'];
		if(isset($selectedList[$arRes['SID']]))
		{
			unset($selectedList[$arRes['SID']]);
			$selected = 'Y';
		}
		else
		{
			$selected = 'N';
		}

		$title = \CCrmContact::PrepareFormattedName(
			array(
				'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
				'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
				'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
				'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
			)
		);

		$arResult['ELEMENT'][] = array(
			'title' => $title,
			'desc' => empty($arRes['COMPANY_TITLE']) ? '' : $arRes['COMPANY_TITLE'],
			'id' => $arRes['SID'],
			'url' => \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_contact_show'),
				array('contact_id' => $arRes['ID'])
			),
			'image' => $arImg['src'],
			'type' => 'contact',
			'selected' => $selected
		);
	}
}

if(in_array('company', $arParams['ENTITY_TYPE']))
{
	$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
	$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
	$obRes = \CCrmCompany::GetListEx(
		array('ID' => 'DESC'),
		array(),
		false,
		array('nTopCount' => $preloadItemsCnt),
		array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY', 'LOGO')
	);
	while($arRes = $obRes->Fetch())
	{
		$arImg = array();
		if(!empty($arRes['LOGO']) && intval($arRes['LOGO']) > 0)
		{
			$arImg = \CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
		}

		$arRes['SID'] = $entityPrefix['company'].'_'.$arRes['ID'];
		if(isset($selectedList[$arRes['SID']]))
		{
			unset($selectedList[$arRes['SID']]);
			$selected = 'Y';
		}
		else
		{
			$selected = 'N';
		}

		$arDesc = array();
		if(isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
		{
			$arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
		}
		if(isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
		{
			$arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];
		}

		$arResult['ELEMENT'][] = array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => implode(', ', $arDesc),
			'id' => $arRes['SID'],
			'url' => \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_company_show'),
				array(
					'company_id' => $arRes['ID']
				)
			),
			'image' => $arImg['src'],
			'type' => 'company',
			'selected' => $selected
		);
	}
}

if(in_array('deal', $arParams['ENTITY_TYPE']))
{
	$obRes = \CCrmDeal::GetListEx(
		array('ID' => 'DESC'),
		array(),
		false,
		array('nTopCount' => $preloadItemsCnt),
		array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
	);
	while($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $entityPrefix['deal'].'_'.$arRes['ID'];
		if(isset($selectedList[$arRes['SID']]))
		{
			unset($selectedList[$arRes['SID']]);
			$selected = 'Y';
		}
		else
		{
			$selected = 'N';
		}

		$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
		$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

		$arResult['ELEMENT'][] = array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $clientTitle,
			'id' => $arRes['SID'],
			'url' => \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_deal_show'),
				array(
					'deal_id' => $arRes['ID']
				)
			),
			'type' => 'deal',
			'selected' => $selected
		);
	}
}

if(in_array('quote', $arParams['ENTITY_TYPE']))
{
	$arQuoteStageList = CCrmStatus::GetStatusListEx('QUOTE_STAGE');

	$obRes = \CCrmQuote::GetList(
		array('ID' => 'DESC'),
		array(),
		false,
		array('nTopCount' => $preloadItemsCnt),
		array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
	);
	while($arRes = $obRes->Fetch())
	{
		$arRes['SID'] = $entityPrefix['quote'].'_'.$arRes['ID'];
		if(isset($selectedList[$arRes['SID']]))
		{
			unset($selectedList[$arRes['SID']]);
			$selected = 'Y';
		}
		else
		{
			$selected = 'N';
		}

		$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
		$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

		$arResult['ELEMENT'][] = array(
			'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
			'desc' => $clientTitle,
			'id' => $arRes['SID'],
			'url' => \CComponentEngine::MakePathFromTemplate(
				\Bitrix\Main\Config\Option::get('crm', 'path_to_quote_show'),
				array(
					'quote_id' => $arRes['ID']
				)
			),
			'type' => 'quote',
			'selected' => $selected
		);
	}
}

if(count($selectedList) > 0)
{
	$additionalSelect = array();
	foreach($selectedList as $item)
	{
		list($type, $id) = explode('_', $item);
		if(!isset($additionalSelect[$type]))
		{
			$additionalSelect[$type] = array($id);
		}
		else
		{
			$additionalSelect[$type][] = $id;
		}
	}

	if(array_key_exists($entityPrefix['lead'], $additionalSelect) && in_array('lead', $arParams['ENTITY_TYPE']))
	{
		$obRes = \CCrmLead::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $additionalSelect[$entityPrefix['lead']]),
			false,
			false,
			array('ID', 'TITLE', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
		);
		while($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $entityPrefix['lead'].'_'.$arRes['ID'];
			$description = \CCrmLead::PrepareFormattedName(
				array(
					'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
					'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
					'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
					'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
				)
			);

			$arResult['ELEMENT'][] = array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $description,
				'id' => $arRes['SID'],
				'url' => \CComponentEngine::MakePathFromTemplate(
					\Bitrix\Main\Config\Option::get('crm', 'path_to_lead_show'),
					array(
						'lead_id' => $arRes['ID']
					)
				),
				'type' => 'lead',
				'selected' => 'Y',
			);
		}
	}

	if(array_key_exists($entityPrefix['contact'], $additionalSelect) && in_array('contact', $arParams['ENTITY_TYPE']))
	{
		$obRes = \CCrmContact::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $additionalSelect[$entityPrefix['contact']]),
			false,
			false,
			array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'COMPANY_TITLE', 'PHOTO')
		);
		while($arRes = $obRes->Fetch())
		{
			$arImg = array();
			if(!empty($arRes['PHOTO']) && intval($arRes['PHOTO']) > 0)
			{
				$arImg = \CFile::ResizeImageGet($arRes['PHOTO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
			}

			$arRes['SID'] = $entityPrefix['lead'].'_'.$arRes['ID'];
			$title = \CCrmContact::PrepareFormattedName(
				array(
					'HONORIFIC' => isset($arRes['HONORIFIC']) ? $arRes['HONORIFIC'] : '',
					'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
					'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
					'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
				)
			);

			$arResult['ELEMENT'][] = array(
				'title' => $title,
				'desc' => empty($arRes['COMPANY_TITLE']) ? '' : $arRes['COMPANY_TITLE'],
				'id' => $arRes['SID'],
				'url' => \CComponentEngine::MakePathFromTemplate(
					\Bitrix\Main\Config\Option::get('crm', 'path_to_contact_show'),
					array(
						'contact_id' => $arRes['ID']
					)
				),
				'image' => $arImg['src'],
				'type' => 'contact',
				'selected' => 'Y'
			);
		}
	}

	if(array_key_exists($entityPrefix['company'], $additionalSelect) && in_array('company', $arParams['ENTITY_TYPE']))
	{
		$arCompanyTypeList = CCrmStatus::GetStatusListEx('COMPANY_TYPE');
		$arCompanyIndustryList = CCrmStatus::GetStatusListEx('INDUSTRY');
		$obRes = \CCrmCompany::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $additionalSelect[$entityPrefix['company']]),
			false,
			false,
				array('ID', 'TITLE', 'COMPANY_TYPE', 'INDUSTRY', 'LOGO')
		);
		while($arRes = $obRes->Fetch())
		{
			$arImg = array();
			if(!empty($arRes['LOGO']) && intval($arRes['LOGO']) > 0)
			{
				$arImg = \CFile::ResizeImageGet($arRes['LOGO'], array('width' => 25, 'height' => 25), BX_RESIZE_IMAGE_EXACT);
			}

			$arRes['SID'] = $entityPrefix['company'].'_'.$arRes['ID'];

			$arDesc = array();
			if(isset($arCompanyTypeList[$arRes['COMPANY_TYPE']]))
			{
				$arDesc[] = $arCompanyTypeList[$arRes['COMPANY_TYPE']];
			}
			if(isset($arCompanyIndustryList[$arRes['INDUSTRY']]))
			{
				$arDesc[] = $arCompanyIndustryList[$arRes['INDUSTRY']];
			}

			$arResult['ELEMENT'][] = array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => implode(', ', $arDesc),
				'id' => $arRes['SID'],
				'url' => \CComponentEngine::MakePathFromTemplate(
					\Bitrix\Main\Config\Option::get('crm', 'path_to_company_show'),
					array(
						'company_id' => $arRes['ID']
					)
				),
				'image' => $arImg['src'],
				'type' => 'company',
				'selected' => 'Y'
			);
		}
	}

	if(array_key_exists($entityPrefix['deal'], $additionalSelect) && in_array('deal', $arParams['ENTITY_TYPE']))
	{
		$obRes = \CCrmDeal::GetListEx(
			array('ID' => 'DESC'),
			array('=ID' => $additionalSelect[$entityPrefix['deal']]),
			false,
			false,
			array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
		);

		$obRes = \CCrmDeal::GetListEx(
				array('ID' => 'DESC'),
				array('=ID' => $additionalSelect[$entityPrefix['deal']]),
				false,
				false,
				array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
		);
		while($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $entityPrefix['deal'].'_'.$arRes['ID'];

			$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
			$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

			$arResult['ELEMENT'][] = array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $clientTitle,
				'id' => $arRes['SID'],
				'url' => \CComponentEngine::MakePathFromTemplate(
					\Bitrix\Main\Config\Option::get('crm', 'path_to_deal_show'),
					array(
						'deal_id' => $arRes['ID']
					)
				),
				'type' => 'deal',
				'selected' => 'Y'
			);
		}
	}

	if(array_key_exists($entityPrefix['quote'], $additionalSelect) && in_array('quote', $arParams['ENTITY_TYPE']))
	{
		$arQuoteStageList = CCrmStatus::GetStatusListEx('QUOTE_STAGE');

		$obRes = \CCrmQuote::GetList(
			array('ID' => 'DESC'),
			array('=ID' => $additionalSelect[$entityPrefix['quote']]),
			false,
			false,
			array('ID', 'TITLE', 'STAGE_ID', 'COMPANY_TITLE', 'CONTACT_FULL_NAME')
		);
		while($arRes = $obRes->Fetch())
		{
			$arRes['SID'] = $entityPrefix['quote'].'_'.$arRes['ID'];

			$clientTitle = (!empty($arRes['COMPANY_TITLE'])) ? $arRes['COMPANY_TITLE'] : '';
			$clientTitle .= (($clientTitle !== '' && !empty($arRes['CONTACT_FULL_NAME'])) ? ', ' : '').$arRes['CONTACT_FULL_NAME'];

			$arResult['ELEMENT'][] = array(
				'title' => (str_replace(array(';', ','), ' ', $arRes['TITLE'])),
				'desc' => $clientTitle,
				'id' => $arRes['SID'],
				'url' => \CComponentEngine::MakePathFromTemplate(
					\Bitrix\Main\Config\Option::get('crm', 'path_to_quote_show'),
					array(
						'quote_id' => $arRes['ID']
					)
				),
				'type' => 'quote',
				'selected' => 'Y'
			);
		}
	}
}

$APPLICATION->RestartBuffer();
$APPLICATION->ShowAjaxHead();

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/css/crm.css');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/crm.js');

$this->includeComponentTemplate();

CMain::FinalActions();
die();