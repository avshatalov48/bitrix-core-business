<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (is_array($arResult['VALUE']) && !empty($arResult['VALUE']))
{
	if(!CModule::IncludeModule("crm"))
		return;
		
	$arParams['ENTITY_TYPE'] = Array();
	if ($arParams['arUserField']['SETTINGS']['LEAD'] == 'Y')
		$arParams['ENTITY_TYPE'][] = 'LEAD';
	if ($arParams['arUserField']['SETTINGS']['CONTACT'] == 'Y')
		$arParams['ENTITY_TYPE'][] = 'CONTACT';
	if ($arParams['arUserField']['SETTINGS']['COMPANY'] == 'Y')
		$arParams['ENTITY_TYPE'][] = 'COMPANY';	
	if ($arParams['arUserField']['SETTINGS']['DEAL'] == 'Y')
		$arParams['ENTITY_TYPE'][] = 'DEAL';
	if ($arParams['arUserField']['SETTINGS']['ORDER'] == 'Y')
		$arParams['ENTITY_TYPE'][] = 'ORDER';

	$arParams['PREFIX'] = false;
	if (count($arParams['ENTITY_TYPE']) > 1)
		$arParams['PREFIX'] = true;
	if(!empty($arParams['usePrefix']))
		$arResult['PREFIX'] = 'Y';

	$arValue = Array();	
	foreach ($arResult['VALUE'] as $value)
	{
		if (is_numeric($value))
			$arValue[$arParams['ENTITY_TYPE'][0]][] = $value;
		else
		{
			$ar = explode('_', $value);
			$arValue[CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
		}
	}

	$arResult['VALUE'] = Array();
	if ($arParams['arUserField']['SETTINGS']['LEAD'] == 'Y' && isset($arValue['LEAD']) && !empty($arValue['LEAD']))
	{
		$dbRes = CCrmLead::GetListEx(
			array('TITLE' => 'ASC'),
			array('=ID' => $arValue['LEAD']),
			false,
			false,
			array('ID', 'TITLE')
		);
		while ($arRes = $dbRes->Fetch())
		{
			$arResult['VALUE']['LEAD'][$arRes['ID']] = Array(
				'ENTITY_TITLE' => $arRes['TITLE'],
				'ENTITY_LINK' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Lead, $arRes['ID']),
			);
		}
	}
	if ($arParams['arUserField']['SETTINGS']['CONTACT'] == 'Y' && isset($arValue['CONTACT']) && !empty($arValue['CONTACT']))
	{
		$hasNameFormatter = method_exists("CCrmContact", "PrepareFormattedName");
		$dbRes = CCrmContact::GetListEx(
			array('LAST_NAME'=>'ASC', 'NAME' => 'ASC'),
			array('=ID' => $arValue['CONTACT']),
			false,
			false,
			$hasNameFormatter
				? array('ID', 'HONORIFIC', 'NAME', 'SECOND_NAME', 'LAST_NAME')
				: array('ID', 'FULL_NAME')
		);

		while ($arRes = $dbRes->Fetch())
		{
			if($hasNameFormatter)
			{
				$title = CCrmContact::PrepareFormattedName(
					array(
						'HONORIFIC' => $arRes['HONORIFIC'] ?? '',
						'NAME' => $arRes['NAME'] ?? '',
						'SECOND_NAME' => $arRes['SECOND_NAME'] ?? '',
						'LAST_NAME' => $arRes['LAST_NAME'] ?? ''
					)
				);
			}
			else
			{
				$title = $arRes['FULL_NAME'] ?? '';
			}

			$arResult['VALUE']['CONTACT'][$arRes['ID']] = Array(
				'ENTITY_TITLE' => $title,
				'ENTITY_LINK' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Contact, $arRes['ID']),
			);
		}
	}
	if ($arParams['arUserField']['SETTINGS']['COMPANY'] == 'Y' && isset($arValue['COMPANY']) && !empty($arValue['COMPANY']))
	{
		$dbRes = CCrmCompany::GetListEx(array('TITLE'=>'ASC'), array('ID' => $arValue['COMPANY']));
		while ($arRes = $dbRes->Fetch())
		{
			$arResult['VALUE']['COMPANY'][$arRes['ID']] = Array(
				'ENTITY_TITLE' => $arRes['TITLE'],
				'ENTITY_LINK' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Company, $arRes['ID']),
			);
		}
	}
	if ($arParams['arUserField']['SETTINGS']['DEAL'] == 'Y' && isset($arValue['DEAL']) && !empty($arValue['DEAL']))
	{
		$dbRes = CCrmDeal::GetListEx(array('TITLE'=>'ASC'), array('ID' => $arValue['DEAL']));
		while ($arRes = $dbRes->Fetch())
		{
			$arResult['VALUE']['DEAL'][$arRes['ID']] = Array(
				'ENTITY_TITLE' => $arRes['TITLE'],
				'ENTITY_LINK' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Deal, $arRes['ID']),
			);
		}
	}
	if ($arParams['arUserField']['SETTINGS']['ORDER'] == 'Y' && isset($arValue['ORDER']) && !empty($arValue['ORDER']))
	{
		$resultDB = \Bitrix\Crm\Order\Order::getList(array(
			'filter' => array('=ID' => $arValue['ORDER']),
			'select' =>  array('ID', 'ACCOUNT_NUMBER'),
			'order' => array('ID' => 'DESC')
		));

		while ($order = $resultDB->fetch())
		{
			$arResult['VALUE']['ORDER'][$order['ID']] = array(
				'ENTITY_TITLE' => $order['ACCOUNT_NUMBER'],
				'ENTITY_LINK' => CCrmOwnerType::GetEntityShowPath(CCrmOwnerType::Order, $order['ID']),
			);
		}
	}

}

?>