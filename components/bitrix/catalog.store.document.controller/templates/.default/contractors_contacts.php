<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

if ($arResult['IS_CRM_CONTRACTORS_PROVIDER'])
{
	$APPLICATION->IncludeComponent(
		'bitrix:crm.store.contractor_contact.list',
		'',
		[
			'PATH_TO' => $arResult['PATH_TO'],
		]
	);
}
