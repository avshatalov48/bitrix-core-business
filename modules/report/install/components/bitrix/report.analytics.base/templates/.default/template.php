<?php

\Bitrix\Main\Loader::includeModule('report');
\Bitrix\Main\UI\Extension::load('report.js.dashboard');
\Bitrix\Main\UI\Extension::load('report_visual_constructor');
\Bitrix\Main\UI\Extension::load('loader');
$APPLICATION->SetTitle($arResult['ANALYTIC_BOARD_TITLE']);


?>


<div id="report-analytics-page" class="report-analytics-page-wrapper">



	<?
	$APPLICATION->IncludeComponent("bitrix:ui.sidepanel.wrappermenu", "", array(
		"ID" => 'report-analytic-left-menu',
		"ITEMS" => $arResult['MENU_ITEMS'],
		"TITLE" => $arResult['ANALYTIC_BOARD_LEFT_TITLE']
	));
	?>

	<div class="report-analytics-content">
		<?php
		if (!$arResult['IS_DISABLED_BOARD'])
		{
			$APPLICATION->IncludeComponent(
				'bitrix:report.visualconstructor.board.base',
				'',
				array(
					'BOARD_ID' => $arResult['ANALYTIC_BOARD_KEY'],
					'IS_DEFAULT_MODE_DEMO' => false,
					'IS_BOARD_DEFAULT' => true,
					'FILTER' => $arResult['ANALYTIC_BOARD_FILTER'],
					'BOARD_BUTTONS' => $arResult['BOARD_BUTTONS'],
					'IS_ENABLED_STEPPER' => $arResult['IS_ENABLED_STEPPER'],
					'STEPPER_IDS' => $arResult['STEPPER_IDS']
				),
				null,
				array()
			);
		}
		else
		{
			$APPLICATION->IncludeComponent(
				'bitrix:report.analytics.empty',
				''
			);
		}

		?></div>
</div>

<script>
	new BX.Report.Analytics.Page({
		scope: document.getElementById('report-analytics-page'),
		menuScope: document.getElementById('report-analytic-left-menu'),
		defaultBoardKey: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_BOARD_KEY'])?>,
		defaultBoardTitle: <?=CUtil::PhpToJSObject($arResult['ANALYTIC_BOARD_TITLE'])?>
	})
</script>