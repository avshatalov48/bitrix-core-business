<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Loader;

if($arParams['IS_SLIDER'])
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowHead();

	$bodyClass = $APPLICATION->getPageProperty("BodyClass", false);
	$bodyClasses = "app-layout-subscribe-slider-modifier";
	if($arParams['USE_PADDING'] != 'N')
	{
		$bodyClasses .= " app-layout-subscribe-renew-modifier-75";
	}
	$APPLICATION->setPageProperty("BodyClass", trim(sprintf("%s %s", $bodyClass, $bodyClasses)));
}

Loader::includeModule('ui');
Extension::load('ui.common');

Loc::loadMessages(__FILE__);
$arResult['ERROR_TITLE'] = ($arResult['ERROR_TITLE']) ?: Loc::getMessage("REST_APP_LAYOUT_ERROR_TITLE_DEFAULT");

$needPadding = false;
if($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle($arResult['ERROR_TITLE']);
	$needPadding = true;
}

?>
<div class="app-layout-subscribe-renew app-layout-error-block<?=$needPadding ? ' app-layout-subscribe-renew-padding' : ''; ?>">
	<div class="app-layout-icon">
		<div class="app-layout-icon-cloud app-layout-icon-cloud-blue app-layout-icon-cloud-left-top"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-left-bottom"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-blue app-layout-icon-cloud-blue-right app-layout-icon-cloud-right-bottom"></div>
		<div class="app-layout-icon-cloud app-layout-icon-cloud-right app-layout-icon-cloud-right-top"></div>
		<div class="app-layout-icon-main app-layout-icon-main-error">
			<div class="app-layout-icon-refresh"></div>
			<div class="app-layout-icon-circle"></div>
			<div class="app-layout-icon-alert"></div>
		</div>
	</div>
	<p><?=$arResult['ERROR_MESSAGE']; ?></p>
</div>
<?php

if($arParams['IS_SLIDER'])
{
	CMain::FinalActions();
	die();
}