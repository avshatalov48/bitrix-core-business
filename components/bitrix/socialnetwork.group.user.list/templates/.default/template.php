<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons;

\Bitrix\Main\Loader::includeModule('ui');

CUtil::InitJSCore(['popup']);
Extension::load([
	'ui.buttons',
	'ui.buttons.icons',
]);

$toolbarId = mb_strtolower($arResult['GRID_ID']) . '_toolbar';

Toolbar::addFilter([
	'GRID_ID' => $arResult['GRID_ID'],
	'FILTER_ID' => $arResult['FILTER_ID'],
	'FILTER' => $arResult['FILTER'],
	'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
	'ENABLE_LIVE_SEARCH' => true,
	'ENABLE_LABEL' => true,
	'LAZY_LOAD' => [
		'CONTROLLER' => [
			'getList' => 'socialnetwork.filter.usertogroup.getlist',
			'getField' => 'socialnetwork.filter.usertogroup.getfield',
			'componentName' => 'socialnetwork.group.user.list',
			'signedParameters' => \Bitrix\Main\Component\ParameterSigner::signParameters('socialnetwork.group.user.list', [
			])

		]
	],
	'CONFIG' => [
		'AUTOFOCUS' => false,
	],
]);

if (
	isset($_REQUEST['IFRAME'])
	&& $_REQUEST['IFRAME'] === 'Y'
)
{
	Toolbar::deleteFavoriteStar();
}

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	echo \Bitrix\Main\Update\Stepper::getHtml([ 'socialnetwork' => [ 'Bitrix\Socialnetwork\Update\WorkgroupDeptSync' ] ], Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_STEPPER_TITLE'));
}

$buttonId = "{$toolbarId}_button";

if (!empty($arResult['TOOLBAR_MENU']))
{
	$menuButton = new Buttons\Button([
		'color' => Buttons\Color::LIGHT_BORDER,
		'icon' => Buttons\Icon::SETTING,
	]);
	$menuButton->addAttribute('id', $buttonId);
	Toolbar::addButton($menuButton);
}

if (!empty($arResult['TOOLBAR_BUTTONS']))
{
	foreach($arResult['TOOLBAR_BUTTONS'] as $button)
	{
		switch($button['TYPE'])
		{
			case 'ADD':
				$icon = Buttons\Icon::ADD;
				break;
			default:
				$icon = '';
		}

		Toolbar::addButton([
			'link' => $button['LINK'],
			'color' => Buttons\Color::PRIMARY,
			'icon' => $icon,
			'text' => $button['TITLE'],
			'click' => $button['CLICK']
		]);
	}
}

$gridContainerId = 'bx-sgul-' . $arResult['GRID_ID'] . '-container';
$snippet = new \Bitrix\Main\Grid\Panel\Snippet();

$removeButton = $snippet->getRemoveButton();

?><span id="<?= htmlspecialcharsbx($gridContainerId) ?>"><?php
	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		[
			'GRID_ID' => $arResult['GRID_ID'],
			'HEADERS' => $arResult['HEADERS'],
			'ROWS' => $arResult['ROWS'],
			'NAV_OBJECT' => $arResult['NAV_OBJECT'],
			'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
			'ENABLE_COLLAPSIBLE_ROWS' => true,
			'ACTION_ALL_ROWS' => false,
			'AJAX_OPTION_HISTORY' => 'N',
			'AJAX_MODE' => 'Y',
			'SHOW_ROW_CHECKBOXES' => $arResult['GROUP_PERMS']['UserCanModifyGroup'],
			'SHOW_SELECTED_COUNTER' => $arResult['GROUP_PERMS']['UserCanModifyGroup'],
			'SHOW_ROW_ACTIONS_MENU' => true,
			'ACTION_PANEL' => [
				'GROUPS' => [
					[
						'ITEMS' => [
							$removeButton,
						],
					],
				]
			],
			'EDITABLE' => false,
			'MESSAGES' => $arResult['GROUP_ACTION_MESSAGES'],
		],
		$component
	);
?></span><?php

?><script>
	BX.ready(function () {
		new BX.Socialnetwork.WorkgroupUserList.Manager({
			id: '<?= \Bitrix\Main\Security\Random::getString(6) ?>',
			componentName: '<?= $component->getName() ?>',
			signedParameters: '<?= $component->getSignedParameters() ?>',
			gridId: '<?= CUtil::JSEscape($arResult['GRID_ID']) ?>',
			filterId: '<?= CUtil::JSEscape($arResult['FILTER_ID']) ?>',
			gridContainerId: '<?= CUtil::JSEscape($gridContainerId) ?>',
			toolbar: {
				id: '<?= CUtil::JSEscape($toolbarId) ?>',
				menuButtonId: '<?= CUtil::JSEscape($buttonId) ?>',
				menuItems: <?= CUtil::PhpToJSObject($arResult['TOOLBAR_MENU']) ?>,
			},
		});
	});
</script><?php
