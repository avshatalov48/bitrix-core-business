<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"popup",
	"ui.buttons.icons",
]);

if (\Bitrix\Main\Loader::includeModule('intranet'))
{
	$APPLICATION->includeComponent(
		'bitrix:intranet.binding.menu',
		'',
		array(
			'SECTION_CODE' => 'crm_analytics',
			'MENU_CODE' => 'config'
		)
	);
}
?>

<div class="analytic-board-config-control">
	<a id="analytic_board_configuration_button" class="ui-btn ui-btn-light-border ui-btn-icon-setting"></a>
</div>


<script>
	BX.message({
		'VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT': "<?=\Bitrix\Main\Localization\Loc::getMessage('VISUALCONSTRUCTOR_DASHBOARD_GO_TO_DEFAULT')?>"
	});
	new BX.Report.Analytics.Config.Controls({
		boardId: <?=CUtil::PhpToJSObject($arResult['BOARD_ID'])?>,
		boardOptions: <?= \Bitrix\Main\Web\Json::encode($arResult['BOARD_OPTIONS'])?>,
		configurationButton: BX('analytic_board_configuration_button'),
	});
</script>