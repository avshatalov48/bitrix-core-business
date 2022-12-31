<?php

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$limitInfo = null;
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$limitInfo = \Bitrix\Catalog\Config\State::getCrmExceedingProductLimit();
}

$isInstallMobileApp = (bool)\CUserOptions::GetOption('mobile', 'iOsLastActivityDate')
	|| (bool)\CUserOptions::GetOption('mobile', 'AndroidLastActivityDate')
;
$isEnabledQrAuth = $isInstallMobileApp || (bool)\CUserOptions::GetOption('product-selector', 'barcodeQrAuth');

$isShowedBarcodeSpotlightInfo = \CUserOptions::GetOption('spotlight', 'view_date_selector_barcode_scanner_info');

$isAllowedShowBarcodeSpotlightInfo = false;
if (
	!$isShowedBarcodeSpotlightInfo
	&& \Bitrix\Main\Loader::includeModule('catalog')
	&& \Bitrix\Main\Loader::includeModule('iblock')
)
{
	$catalogId = CCrmCatalog::GetDefaultID();
	$product = \CIBlockElement::GetList(
		false,
		['IBLOCK_ID' => $catalogId],
		false,
		['nTopCount' => 1],
		['ID']
	)->Fetch();
	$hasProducts = !empty($product);

	$arrivalDocuments = StoreDocumentTable::getRow([
		'select' => ['ID'],
		'filter' => ['=DOC_TYPE' => StoreDocumentTable::TYPE_ARRIVAL],
	]);
	$hasArrivalDocuments = !empty($arrivalDocuments);

	$isAllowedShowBarcodeSpotlightInfo = $hasProducts && $hasArrivalDocuments;
}

return [
	'css' => 'dist/product-selector.bundle.css',
	'js' => 'dist/product-selector.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.forms',
		'fileinput',
		'catalog.sku-tree',
		'main.loader',
		'ui.info-helper',
		'ui.entity-selector',
		'catalog.product-model',
		'catalog.product-selector',
		'catalog.barcode-scanner',
		'ui.notification',
		'main.core',
		'main.core.events',
		'ui.qrauthorization',
		'spotlight',
		'ui.tour',
	],
	'skip_core' => false,
	'settings' => [
		'limitInfo' => $limitInfo,
		'isInstallMobileApp' => $isInstallMobileApp,
		'isEnabledQrAuth' => $isEnabledQrAuth,
		'isShowedBarcodeSpotlightInfo' => $isShowedBarcodeSpotlightInfo,
		'isAllowedShowBarcodeSpotlightInfo' => $isAllowedShowBarcodeSpotlightInfo,
		'errorAdminHint' =>
			Loader::includeModule('bitrix24')
				? Loc::getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_ADMIN_B4_HINT')
				: Loc::getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_ADMIN_HINT')
		,
	],
];
