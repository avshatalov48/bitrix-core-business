<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @global \CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Loc::loadMessages(__FILE__);

Loader::includeModule('ui');

Extension::load('ui.hint');

global $APPLICATION;

$APPLICATION->SetTitle(
	Loc::getMessage(
		'DEALS_WITH_RESERVED_PRODUCT_SLIDER_TITLE', ['#PRODUCT_NAME#' => htmlspecialcharsbx($arResult['PRODUCT_NAME'])]
	)
);

$APPLICATION->IncludeComponent(
	'bitrix:crm.deal.list',
	'',
	[
		'INTERNAL_FILTER' => $arResult['DEALS_FILTER'],
		'ENABLE_TOOLBAR' => true,
		'GRID_ID_SUFFIX' => 'PRODUCT_CARD',
		'HIDE_FILTER' => true,
	]
);

Toolbar::deleteFavoriteStar();
Toolbar::addAfterTitleHtml(
	'<span data-hint="' . Loc::getMessage('DEALS_WITH_RESERVED_PRODUCT_SLIDER_HINT_MSGVER_1') . '"></span>'
);
?>
<script>
	BX.UI.Hint.init(document.querySelector('.pagetitle-inner-container'));
</script>
