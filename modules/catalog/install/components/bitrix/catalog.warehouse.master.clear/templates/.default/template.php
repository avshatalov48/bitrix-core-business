<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

$APPLICATION->SetTitle(Loc::getMessage('CAT_WAREHOUSE_MASTER_NEW_TITLE'));

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.hint',
	'ui.label',
	'ui.switcher',
	'main.loader',
	'ui.vue',
	'ui.buttons',
	'main.popup',
	'catalog.store-use',
	'catalog.warehouse-master',
	'ui.fonts.opensans',
]);

CJSCore::Init(array('marketplace'));
$APPLICATION->SetPageProperty(
	'BodyClass',
	$APPLICATION->GetPageProperty('BodyClass') . ' catalog-warehouse-master-clear'
);
?>

<div id="warehouseApp"></div>

<script>
	BX.ready(() => {
		const app = new BX.Catalog.WarehouseMaster.App({
			rootNodeId: 'warehouseApp',
			isUsed: <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED']) ?>,
			isPlanRestricted: <?= CUtil::PhpToJSObject((bool)$arResult['IS_PLAN_RESTRICTED']) ?>,
			isUsed1C: <?= CUtil::PhpToJSObject((bool)$arResult['IS_USED_ONEC']) ?>,
			isWithOrdersMode: <?= CUtil::PhpToJSObject((bool)$arResult['IS_WITH_ORDERS_MODE']) ?>,
			isRestrictedAccess: <?= CUtil::PhpToJSObject((bool)$arResult['IS_RESTRICTED_ACCESS']) ?>,
			inventoryManagementSource: <?= CUtil::PhpToJSObject($arResult['INVENTORY_MANAGEMENT_SOURCE']) ?>,
			previewLang: <?= CUtil::PhpToJSObject($arResult['PREVIEW_LANG']) ?>,
		});

		app.attachTemplate();
	});
</script>
