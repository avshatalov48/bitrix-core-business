<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"popup",
	"ui.buttons.icons",
	'report.integration.toolbar',
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

echo \Bitrix\UI\Buttons\SettingsButton::create([
	'air' => true,
	'tag' => \Bitrix\UI\Buttons\Tag::LINK,
])
	->addAttribute('id', 'analytic_board_configuration_button')
	->render()
;
?>

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
