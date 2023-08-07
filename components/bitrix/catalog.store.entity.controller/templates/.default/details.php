<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\StoreTable;
use Bitrix\Intranet\Util;
use Bitrix\Main;
use Bitrix\Main\Loader;

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControllerComponent $component */
/** @var \CBitrixComponentTemplate $this */

$request = Main\Context::getCurrent()->getRequest();

global $APPLICATION;

Main\UI\Extension::load('ui.notification');

$pathToDetail = $arResult['PATH_TO']['DETAILS'];
$pathToDetailJS = str_replace('#ID#', '(\d+)', $pathToDetail);

$userFieldCreatePageUrl = null;
if (Loader::includeModule('intranet'))
{
	$userFieldCreatePageUrl = Util::getUserFieldDetailConfigUrl('catalog', StoreTable::getUfId());
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_NAME' => 'bitrix:catalog.store.entity.details',
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => [
			'ID' => (int)($arResult['VARIABLES']['ID'] ?? 0),
			'PATH_TO_DETAIL' => $pathToDetail,
			'USER_FIELD_CREATE_PAGE_URL' => $userFieldCreatePageUrl,
		],
		'RELOAD_GRID_AFTER_SAVE' => true,
		'CLOSE_AFTER_SAVE' => true,
		'USE_UI_TOOLBAR' => 'Y',
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => '/shop/documents-stores/',
	]
);

?>
<script>
	if (window.top === window)
	{
		BX.SidePanel.Instance.bindAnchors({
			rules: [
				{
					condition: [
						'<?=CUtil::JSEscape($pathToDetailJS)?>'
					],
					options: {
						cacheable: false,
						allowChangeHistory: true,
						width: 500
					}
				},
			]
		});
	}
</script>
