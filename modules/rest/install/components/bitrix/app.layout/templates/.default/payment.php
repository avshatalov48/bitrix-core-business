<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\AppTable;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

if ($arParams['IS_SLIDER'])
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowHead();

	$bodyClass = $APPLICATION->getPageProperty("BodyClass", false);
	$bodyClasses = "app-layout-subscribe-slider-modifier";
	if ($arParams['USE_PADDING'] != 'N')
	{
		$bodyClasses .= " app-layout-subscribe-renew-modifier-75";
	}
	$APPLICATION->setPageProperty("BodyClass", trim(sprintf("%s %s", $bodyClass, $bodyClasses)));
}

$needPadding = $arParams['SET_TITLE'] == 'Y' ? true : false;

Loader::includeModule('ui');
Extension::load(['ui.common','ui.buttons']);

if ($arResult['PAYMENT_TYPE'] === AppTable::STATUS_SUBSCRIPTION || $arResult['APP_STATUS']['STATUS'] === AppTable::STATUS_SUBSCRIPTION)
{
	$title = Loc::getMessage('REST_APP_LAYOUT_PAYMENT_ACCESS_TITLE_SUBSCRIBE');
	$buyButton = Loc::getMessage('REST_APP_LAYOUT_PAYMENT_ACCESS_BTN_BUY_SUBSCRIBE');
	$buyUrl = '/settings/license_buy.php?product=subscr';
}
else
{
	$title = Loc::getMessage(
		'REST_APP_LAYOUT_PAYMENT_ACCESS_TITLE_APP',
			[
				'#APP_NAME#' => htmlspecialcharsbx($arResult['APP_NAME'])
			]
	);
	$buyButton = Loc::getMessage('REST_APP_LAYOUT_PAYMENT_ACCESS_BTN_BUY_APP');
	$buyUrl = $arResult['DETAIL_URL'];
}
?>
<div class="app-layout-subscribe-renew<?=$needPadding ? ' app-layout-subscribe-renew-padding' : ''; ?>">
	<h1 class="ui-title-1 app-layout-subscribe-title"><?=$title; ?></h1>
	<div class="app-layout-icon">
		<div class="app-layout-icon-cloud app-layout-icon-cloud-blue app-layout-icon-cloud-left-top"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-left-bottom"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-blue app-layout-icon-cloud-blue-right app-layout-icon-cloud-right-bottom"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-right app-layout-icon-cloud-right-top"></div>
		<div class="app-layout-icon-main">
			<div class="app-layout-icon-refresh"></div>
			<div class="app-layout-icon-alert"></div>
			<div class="app-layout-clock-face">
				<div class="app-layout-clock-face-line-dark"></div>
				<div class="app-layout-clock-face-line-light"></div>
				<div class="app-layout-clock-face-circle"></div>
			</div>
			<div class="app-layout-icon-clock"></div>
		</div>
	</div>
	<?php
	if ($arResult['PAYMENT_TYPE'] === AppTable::STATUS_SUBSCRIPTION || $arResult['APP_STATUS']['STATUS'] === AppTable::STATUS_SUBSCRIPTION):?>
		<a class="ui-btn ui-btn-success ui-btn-lg ui-btn-round app-layout-subscribe-renew-button" target="_blank" href="<?=$buyUrl; ?>"><?=$buyButton; ?></a>
	<?php else:?>
		<a class="ui-btn ui-btn-success ui-btn-lg ui-btn-round app-layout-subscribe-renew-button" href="<?=$buyUrl; ?>"><?=$buyButton; ?></a>
	<?php endif;?>
</div>
<?php

if ($arParams['IS_SLIDER'])
{
	CMain::FinalActions();
	die();
}