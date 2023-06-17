<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Socialnetwork\Update\WorkgroupDeptSync;
use Bitrix\Main\Grid\Panel;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$component = $this->getComponent();

\Bitrix\Main\Loader::includeModule('ui');

CUtil::InitJSCore(['popup']);
Extension::load([
	'socialnetwork.toolbar',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.label',
	'ui.notification',
	'ui.fonts.opensans',
]);

$toolbarId = mb_strtolower($arResult['GRID_ID']) . '_toolbar';

Toolbar::addFilter([
	'GRID_ID' => $arResult['GRID_ID'],
	'FILTER_ID' => $arResult['FILTER_ID'],
	'FILTER' => $arResult['FILTER'],
	'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
	'RESET_TO_DEFAULT_MODE' => true,
	'ENABLE_LIVE_SEARCH' => true,
	'ENABLE_LABEL' => true,
	'LAZY_LOAD' => [
		'CONTROLLER' => [
			'getList' => 'socialnetwork.filter.usertogroup.getlist',
			'getField' => 'socialnetwork.filter.usertogroup.getfield',
			'componentName' => 'socialnetwork.group.user.list',
			'signedParameters' => ParameterSigner::signParameters('socialnetwork.group.user.list', [])
		]
	],
	'CONFIG' => [
		'AUTOFOCUS' => false,
	],
]);

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	$this->SetViewTarget('below_pagetitle');
}

$toolbarContainerNodeId = $toolbarId . '_container';

$classList = [ 'sonet-group-user-list-toolbar-container' ];
if ($arResult['GROUP_PERMS']['UserCanModifyGroup'])
{
	$classList[] = '--group-actions';
}

?><div class="<?= implode(' ', $classList)?>">
	<?php

	$APPLICATION->IncludeComponent(
		'bitrix:socialnetwork.interface.counters',
		'',
		[
			'ENTITY_TYPE' => CounterDictionary::ENTITY_WORKGROUP_DETAIL,
			'ENTITY_ID' => (int)$arParams['GROUP_ID'],
			'GRID_ID' => $arResult['GRID_ID'],
			'COUNTERS' => [
				CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT,
				CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN,
			],
			'CURRENT_COUNTER' => $arResult['CURRENT_COUNTER'],
			'ROLE' => $arResult['GROUP_PERMS']['UserRole'],
		],
		$component
	);
	?>
</div><?php

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	$this->EndViewTarget();
}

if (
	isset($_REQUEST['IFRAME'])
	&& $_REQUEST['IFRAME'] === 'Y'
)
{
	Toolbar::deleteFavoriteStar();
}

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	echo \Bitrix\Main\Update\Stepper::getHtml([ 'socialnetwork' => [ WorkgroupDeptSync::class ] ], Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_STEPPER_TITLE'));
}

if (!empty($arResult['TOOLBAR_BUTTONS']))
{
	foreach($arResult['TOOLBAR_BUTTONS'] as $button)
	{
		Toolbar::addButton([
			'link' => $button['LINK'],
			'color' => Buttons\Color::SUCCESS,
			'text' => $button['TITLE'],
			'click' => $button['CLICK'] ?? '',
		], ButtonLocation::AFTER_TITLE);
	}
}

$gridContainerId = 'bx-sgul-' . $arResult['GRID_ID'] . '-container';

$removeButton = [
	'ICON' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_remove.svg',
	'TYPE' => Panel\Types::BUTTON,
	'NAME' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_DELETE'),
	'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_DELETE'),
	'VALUE' => 'delete',
	'ONCHANGE' => [
		[
			'ACTION' => Panel\Actions::CALLBACK,
			'DATA' => [
				[
					'JS' => "BX.Socialnetwork.WorkgroupUserList.Manager.getById('" . $arResult['GRID_ID'] . "').actionManagerInstance.groupDelete();",
				],
			],
		],
	],
];

?><div class="bx-sgul-top-action-panel"></div><?php

?><span id="<?= htmlspecialcharsbx($gridContainerId) ?>" class="sonet-group-user-grid-container"><?php
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
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_HISTORY' => 'N',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'SHOW_ROW_CHECKBOXES' => $arResult['GROUP_PERMS']['UserCanModifyGroup'],
			'SHOW_SELECTED_COUNTER' => $arResult['GROUP_PERMS']['UserCanModifyGroup'],
			'SHOW_ROW_ACTIONS_MENU' => true,
			'SHOW_ACTION_PANEL' => false,
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
			'TOP_ACTION_PANEL_RENDER_TO' => '.sonet-group-user-list-toolbar-container',
			'TOP_ACTION_PANEL_PINNED_MODE' => false,
		],
		$component
	);
?></span><?php

?><script>
	BX.ready(function () {
		new BX.Socialnetwork.WorkgroupUserList.Manager({
			id: '<?= $arResult['GRID_ID'] ?>',
			componentName: '<?= $component->getName() ?>',
			signedParameters: '<?= $component->getSignedParameters() ?>',
			useSlider: <?= (
				\Bitrix\Main\ModuleManager::isModuleInstalled('intranet') && SITE_TEMPLATE_ID === 'bitrix24'
					? 'true'
					: 'false'
			) ?>,
			gridId: '<?= CUtil::JSEscape($arResult['GRID_ID']) ?>',
			filterId: '<?= CUtil::JSEscape($arResult['FILTER_ID']) ?>',
			defaultFilterPresetId: '<?= CUtil::JSEscape($arResult['CURRENT_PRESET_ID']) ?>',
			defaultCounter: '<?= CUtil::JSEscape($arResult['CURRENT_COUNTER']) ?>',
			gridContainerId: '<?= CUtil::JSEscape($gridContainerId) ?>',
			urls: {
				users: '<?= CUtil::JSEscape(\CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_GROUP_USERS'],
					[
						'group_id' => (int)$arParams['GROUP_ID'],
					]
				)) ?>',
				requests: '<?= CUtil::JSEscape(\CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_GROUP_REQUESTS'],
					[
						'group_id' => (int)$arParams['GROUP_ID'],
					]
				)) ?>',
				requestsOut: '<?= CUtil::JSEscape(\CComponentEngine::makePathFromTemplate(
					$arParams['PATH_TO_GROUP_REQUESTS_OUT'],
					[
						'group_id' => (int)$arParams['GROUP_ID'],
					]
				)) ?>',
			},
		});
	});

	BX.message({
		SOCIALNETWORK_GROUP_USER_LIST_ACTION_REINVITE_SUCCESS: '<?= CUtil::JSEscape(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_REINVITE_SUCCESS')) ?>',
		SOCIALNETWORK_GROUP_USER_LIST_ACTION_FAILURE: '<?= CUtil::JSEscape(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_ACTION_FAILURE')) ?>',
		SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_CONFIRM_TEXT: '<?= CUtil::JSEscape(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_CONFIRM_TEXT')) ?>',
		SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_BUTTON_DELETE: '<?= CUtil::JSEscape(Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_GROUP_ACTION_BUTTON_DELETE')) ?>',
	});
</script><?php
