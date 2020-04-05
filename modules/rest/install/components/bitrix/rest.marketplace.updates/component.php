<?php
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
 * @global CUser $USER
 */

if(!CModule::IncludeModule("rest"))
{
	return;
}

$arResult["ITEMS"] = array();

$arCodes = array();
$arStatuses = array();
$dbApps = \Bitrix\Rest\AppTable::getList(array(
	'filter' => array(
		"=ACTIVE" => \Bitrix\Rest\AppTable::ACTIVE,
		"!=STATUS" => \Bitrix\Rest\AppTable::STATUS_LOCAL,
	),
	'select' => array(
		'*', 'MENU_NAME' => 'LANG.MENU_NAME',
	)
));
while ($arApp = $dbApps->Fetch())
{
	$arCodes[$arApp["CODE"]] = $arApp["VERSION"];
	$arStatuses[$arApp["CODE"]] = $arApp["STATUS"];
}

if (!empty($arCodes))
{
	$curNumUpdates = \Bitrix\Rest\Marketplace\Client::getAvailableUpdateNum();

	$arUpdates = \Bitrix\Rest\Marketplace\Client::getUpdates($arCodes);
	if(is_array($arUpdates) && !empty($arUpdates))
	{
		$arUpdates = $arUpdates["ITEMS"];

		$newNumUpdates = \Bitrix\Rest\Marketplace\Client::getAvailableUpdateNum();
		if ($curNumUpdates != $newNumUpdates)
		{
			$arResult["NEW_NUM_UPDATES"] = $newNumUpdates;
		}

		foreach ($arUpdates as $key => $arApp)
		{
			$arUpdates[$key]["STATUS"] = $arStatuses[$arApp["CODE"]];
			$arUpdates[$key]['CAN_INSTALL'] = \CRestUtil::canInstallApplication($arUpdates[$key]);
		}
	}

	$arResult["ITEMS"] = $arUpdates;
	$arResult["ITEMS_CODES"] = $arCodes;
}

$arResult["ADMIN"] = \CRestUtil::isAdmin();

$APPLICATION->SetTitle(GetMessage("MARKETPLACE_UPDATES"));

\CJSCore::Init(array('marketplace'));

$this->IncludeComponentTemplate();
?>