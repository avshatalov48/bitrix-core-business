<?php

/**
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load("ui.hint");

global $APPLICATION;

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

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
$APPLICATION->SetTitle(
	Loc::getMessage(
		'DEALS_WITH_RESERVED_PRODUCT_SLIDER_TITLE', ['#PRODUCT_NAME#' => htmlspecialcharsbx($arResult['PRODUCT_NAME'])]
	)
);

$this->SetViewTarget('inside_pagetitle');
?>
<span data-hint="<?= Loc::getMessage('DEALS_WITH_RESERVED_PRODUCT_SLIDER_HINT') ?>"></span>
<?php
$this->EndViewTarget();
?>

<script>
	BX.UI.Hint.init(document.querySelector('.pagetitle-inner-container'));
</script>