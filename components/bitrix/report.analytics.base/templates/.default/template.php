<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 * @global \CMain $APPLICATION
 */

\Bitrix\Main\Loader::includeModule('report');
\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'report.js.dashboard',
	'report_visual_constructor',
	'loader',
	'report.integration.toolbar',
]);

$APPLICATION->SetTitle($arResult['ANALYTIC_BOARD_TITLE']);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '') . 'no-background');

if (\Bitrix\Main\Loader::includeModule('ui'))
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
	// stub button to ensure that toolbar-right-buttons content area is rendered
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(new \Bitrix\UI\Buttons\Button());
}

?>
<div id="report-analytics-page" class="report-analytics-page-wrapper">
	<?php
	$APPLICATION->IncludeComponent('bitrix:ui.sidepanel.wrappermenu', '', [
		'ID' => 'report-analytic-left-menu',
		'ITEMS' => $arResult['MENU_ITEMS'],
		'TITLE' => $arResult['ANALYTIC_BOARD_LEFT_TITLE']
	]);
	?>
	<div class="report-analytics-content"></div>
</div>

<script>
	new BX.Report.Analytics.Page({
		scope: document.getElementById('report-analytics-page'),
		menuScope: document.getElementById('report-analytic-left-menu'),
		defaultBoardKey: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_BOARD_KEY'])?>,
		defaultBoardTitle: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_BOARD_TITLE'])?>
	})
</script>
