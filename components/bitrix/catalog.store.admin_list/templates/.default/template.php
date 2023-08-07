<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;

$APPLICATION->SetTitle(Loc::getMessage('CATALOG_STORE_LIST_TITLE'));

$this->setViewTarget('above_pagetitle');
$APPLICATION->IncludeComponent(
	'bitrix:catalog.store.document.control_panel',
	'',
	[
		'PATH_TO' => $arResult['PATH_TO'],
	]
);
$this->endViewTarget();

if (!empty($arResult['ERROR_MESSAGES']) && is_array($arResult['ERROR_MESSAGES']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.info.error',
		'',
		[
			'TITLE' => $arResult['ERROR_MESSAGES'][0],
			'DESCRIPTION' => Loc::getMessage('CATALOG_STORE_ADMIN_LIST_ACCESS_DENIED_DESCRIPTION'),
		]
	);

	return;
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);
?>

<script>
	BX.ready(function() {

		BX.Catalog.Store.Grid.init(<?= CUtil::PhpToJSObject([
			'gridId' => $arResult['GRID']['GRID_ID'],
			'tariff' => $arResult['TARIFF_HELP_LINK']['FEATURE_CODE'] ?? '',
		]) ?>);

	});
</script>
