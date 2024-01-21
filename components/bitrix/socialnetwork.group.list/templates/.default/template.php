<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Grid\Panel;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Integration\Intranet\Settings;
use Bitrix\Socialnetwork\Internals\Counter\CounterDictionary;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @var $component */
/** @var $templateFolder */

$component = $this->getComponent();

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\Extension;
use Bitrix\Socialnetwork\Component\WorkgroupList;
use Bitrix\Tasks\UI\ScopeDictionary;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons;

Loader::includeModule('ui');

CUtil::InitJSCore(['popup']);
Extension::load([
	'socialnetwork.toolbar',
	'socialnetwork.common',
	'socialnetwork.ui.grid',
	'socialnetwork.toolbar',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.notification',
	'tasks.tour',
]);

$messages = Loc::loadLanguageFile(__FILE__);

$classList = [];

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->setPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : ''));

$isTasksScope = (
	in_array($arParams['MODE'], [ WorkgroupList::MODE_TASKS_PROJECT, WorkgroupList::MODE_TASKS_SCRUM ], true)
	&& Loader::includeModule('tasks')
);

$settings = new Settings();
if (!$settings->isToolAvailable(Settings::SONET_TOOLS['workgroups']) && !$isTasksScope)
{
	$componentParameters = [
		'LIMIT_CODE' => Settings::LIMIT_CODES['workgroups'],
		'MODULE' => 'socialnetwork',
		'SOURCE' => 'groupList',
	];

	$APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrapper",
		"",
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:intranet.settings.tool.stub',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $componentParameters,
		],
	);

	return;
}

if ($isTasksScope)
{
	$scope = (
		$arParams['MODE'] === WorkgroupList::MODE_TASKS_SCRUM
			? ScopeDictionary::SCOPE_SCRUM_PROJECTS_GRID
			: ScopeDictionary::SCOPE_PROJECTS_GRID
	);

	if ($scope === ScopeDictionary::SCOPE_SCRUM_PROJECTS_GRID)
	{
		$isAvailable = $settings->isToolAvailable(Settings::TASKS_TOOLS['scrum']);
		$limitCode = Settings::LIMIT_CODES['scrum'];
		$limitScope = Settings::TASKS_TOOLS['scrum'];
	}
	else
	{
		$isAvailable = $settings->isToolAvailable(Settings::TASKS_TOOLS['projects']);
		$limitCode = Settings::LIMIT_CODES['projects'];
		$limitScope = Settings::TASKS_TOOLS['projects'];
	}

	if (!$isAvailable)
	{
		$APPLICATION->IncludeComponent('bitrix:tasks.error', 'limit',
			[
				'SCOPE' => $limitScope,
				'LIMIT_CODE' => $limitCode,
			]
		);
		return;
	}

	$APPLICATION->IncludeComponent(
		'bitrix:tasks.interface.topmenu',
		'',
		[
			'USER_ID' => $arParams['USER_ID'],
			'SECTION_URL_PREFIX' => '',

			'MARK_SECTION_PROJECTS_LIST' => $arParams['MARK_SECTION_PROJECTS_LIST'] ?? '',
			'MARK_SECTION_SCRUM_LIST' => $arParams['MARK_SECTION_SCRUM_LIST'] ?? '',
			'USE_AJAX_ROLE_FILTER' => 'N',

			'PATH_TO_GROUP_TASKS' => $arParams['PATH_TO_GROUP_TASKS'] ?? '',
			'PATH_TO_GROUP_TASKS_TASK' => $arParams['PATH_TO_GROUP_TASKS_TASK'] ?? '',
			'PATH_TO_GROUP_TASKS_VIEW' => $arParams['PATH_TO_GROUP_TASKS_VIEW'] ?? '',
			'PATH_TO_GROUP_TASKS_REPORT' => $arParams['PATH_TO_GROUP_TASKS_REPORT'] ?? '',

			'PATH_TO_USER_TASKS' => $arParams['PATH_TO_USER_TASKS'] ?? '',
			'PATH_TO_USER_TASKS_TASK' => $arParams['PATH_TO_USER_TASKS_TASK'] ?? '',
			'PATH_TO_USER_TASKS_VIEW' => $arParams['PATH_TO_USER_TASKS_VIEW'] ?? '',
			'PATH_TO_USER_TASKS_REPORT' => $arParams['PATH_TO_USER_TASKS_REPORT'] ?? '',
			'PATH_TO_USER_TASKS_TEMPLATES' => $arParams['PATH_TO_USER_TASKS_TEMPLATES'] ?? '',

			'SCOPE' => $scope,
		],
		$component,
		[ 'HIDE_ICONS' => true ]
	);
}

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
			'getList' => 'socialnetwork.filter.workgroup.getlist',
			'getField' => 'socialnetwork.filter.workgroup.getfield',
			'componentName' => 'socialnetwork.group.list',
			'signedParameters' => ParameterSigner::signParameters('socialnetwork.group.list', [
				'MODE' => $arParams['MODE'],
				'USER_ID' => $arParams['USER_ID'],
			])
		]
	],
	'CONFIG' => [
		'AUTOFOCUS' => false,
	],
]);

if ($isTasksScope)
{
	$APPLICATION->IncludeComponent(
		'bitrix:tasks.interface.toolbar',
		'',
		[
			'USER_ID' => (int)$arParams['USER_ID'],
			'GRID_ID' => $arResult['GRID_ID'],
			'FILTER_ID' => $arResult['FILTER_ID'],
			'COUNTERS' => $arResult['TASKS_COUNTERS'],
			'SCOPE' => $arResult['TASKS_COUNTERS_SCOPE'],
			'FILTER_FIELD' => 'COUNTERS',
		],
		$component,
		['HIDE_ICONS' => true]
	);
}
else
{
	if (SITE_TEMPLATE_ID === 'bitrix24')
	{
		$this->SetViewTarget('below_pagetitle');
	}

	$classList = [
		'sonet-group-list-toolbar-container',
		'--group-actions',
	];

	?><div class="<?= implode(' ', $classList)?>">
		<?php
			$counters = [
				CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED,
			];
			if (ModuleManager::isModuleInstalled('tasks'))
			{
				$counters[] = CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS;
			}

			$APPLICATION->IncludeComponent(
				'bitrix:socialnetwork.interface.counters',
				'',
				[
					'ENTITY_TYPE' => CounterDictionary::ENTITY_WORKGROUP_LIST,
					'ENTITY_ID' => 0,
					'GRID_ID' => $arResult['GRID_ID'],
					'COUNTERS' => $counters,
					'CURRENT_COUNTER' => $arResult['CURRENT_COUNTER'],
				],
				$component
			);
		?>
	</div><?php

	if (SITE_TEMPLATE_ID === 'bitrix24')
	{
		$this->EndViewTarget();
	}

}


if (SITE_TEMPLATE_ID === 'bitrix24')
{
//	echo \Bitrix\Main\Update\Stepper::getHtml([ 'socialnetwork' => [ WorkgroupDeptSync::class ] ], Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_TEMPLATE_STEPPER_TITLE'));
}

$buttonId = "{$toolbarId}_button";

if (!empty($arResult['TOOLBAR_BUTTONS']))
{
	foreach($arResult['TOOLBAR_BUTTONS'] as $buttonData)
	{
		$button = new Buttons\Button([
			'link' => $buttonData['LINK'],
			'color' => Buttons\Color::SUCCESS,
			'text' => $buttonData['TITLE'],
			'click' => ($buttonData['CLICK'] ?? ''),
			'icon' => ($buttonData['ICON'] ?? ''),
		]);

		$button->addAttribute('id', 'projectAddButton');

		Toolbar::addButton($button, ButtonLocation::AFTER_TITLE);
	}
}

$gridContainerId = 'bx-sgl-' . $arResult['GRID_ID'] . '-container';

$addToArchiveButton = [
	'ICON' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_move.svg',
	'TYPE' => Panel\Types::BUTTON,
	'NAME' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_ADD_TO_ARCHIVE'),
	'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_ADD_TO_ARCHIVE'),
	'VALUE' => WorkgroupList::GROUP_ACTION_ADD_TO_ARCHIVE,
	'ONCHANGE' => [
		[
			'ACTION' => Panel\Actions::CALLBACK,
			'DATA' => [
				[
					'JS' => "BX.Socialnetwork.WorkgroupList.Manager.getById('" . $arResult['GRID_ID'] . "').
						actionManagerInstance.groupAction('" . WorkgroupList::GROUP_ACTION_ADD_TO_ARCHIVE . "');",
				],
			],
		],
	],
];

$removeFromArchiveButton = [
	'ICON' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_open.svg',
	'TYPE' => Panel\Types::BUTTON,
	'NAME' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_REMOVE_FROM_ARCHIVE'),
	'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_REMOVE_FROM_ARCHIVE'),
	'VALUE' => WorkgroupList::GROUP_ACTION_REMOVE_FROM_ARCHIVE,
	'ONCHANGE' => [
		[
			'ACTION' => Panel\Actions::CALLBACK,
			'DATA' => [
				[
					'JS' => "BX.Socialnetwork.WorkgroupList.Manager.getById('" . $arResult['GRID_ID'] . "').
						actionManagerInstance.groupAction('" . WorkgroupList::GROUP_ACTION_REMOVE_FROM_ARCHIVE . "');",
				],
			],
		],
	],
];
/*
$removeButton = [
	'ICON' => '/bitrix/js/ui/actionpanel/images/ui_icon_actionpanel_remove.svg',
	'TYPE' => Panel\Types::BUTTON,
	'NAME' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_DELETE'),
	'TEXT' => Loc::getMessage('SOCIALNETWORK_GROUP_LIST_GROUP_ACTION_DELETE'),
	'VALUE' => WorkgroupList::GROUP_ACTION_DELETE,
	'ONCHANGE' => [
		[
			'ACTION' => Panel\Actions::CALLBACK,
			'DATA' => [
				[
					'JS' => "BX.Socialnetwork.WorkgroupList.Manager.getById('" . $arResult['GRID_ID'] . "').
						actionManagerInstance.groupAction('" . WorkgroupList::GROUP_ACTION_DELETE . "');",
				],
			],
		],
	],
];
*/
?><div class="bx-sgul-top-action-panel"></div><?php

?><span id="<?= htmlspecialcharsbx($gridContainerId) ?>" class="sonet-group-grid-container">
<?php
	$stub = (count($arResult['ROWS']) > 0 ? null : $arResult['STUB']);
	if ($arParams['MODE'] === WorkgroupList::MODE_TASKS_SCRUM && is_array($stub) && count($stub) > 2)
	{
		$jiraIcon = $templateFolder . '/images/tasks-projects-jira.svg';
		$asanaIcon = $templateFolder . '/images/tasks-projects-asana.svg';
		$trelloIcon = $templateFolder . '/images/tasks-projects-trello.svg';

		$stub = <<<HTML
			<div class="sg-tasks-scrum__transfer--contant">
				<div class="sg-tasks-scrum__transfer--title">{$stub['title']}</div>
				<div class="sg-tasks-scrum__transfer--description">{$stub['description']}</div>
				<div class="sg-tasks-scrum__transfer--content">
					<div class="sg-tasks-scrum__transfer--info">
						<div class="sg-tasks-scrum__transfer--info-text">
							{$stub['migrationTitle']}
						</div>
						<div class="sg-tasks-scrum__transfer--info-systems">
							<div class="sg-tasks-scrum__transfer--info-systems-item">
								<img src="{$jiraIcon}" alt="Jira">
							</div>
							<div class="sg-tasks-scrum__transfer--info-systems-item">
								<img src="{$asanaIcon}" alt="Asana">
							</div>
							<div class="sg-tasks-scrum__transfer--info-systems-item">
								<img src="{$trelloIcon}" alt="Trello">
							</div>
							<div class="sg-tasks-scrum__transfer--info-systems-item">{$stub['migrationOther']}</div>
						</div>
					</div>
					<div class="sg-tasks-scrum__transfer--btn-block">
						<a href="/marketplace/?tag[]=migrator&tag[]=tasks" class="ui-btn ui-btn-primary ui-btn-round">
							{$stub['migrationButton']}
						</a>
					</div>
				</div>
			</div>
		HTML;
	}

	$APPLICATION->IncludeComponent(
		'bitrix:main.ui.grid',
		'',
		[
			'GRID_ID' => $arResult['GRID_ID'],
			'HEADERS' => $arResult['HEADERS'],
			'ROWS' => $arResult['ROWS'],
			'STUB' => $stub,
			'NAV_OBJECT' => $arResult['NAV_OBJECT'],
			'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
			'ENABLE_COLLAPSIBLE_ROWS' => true,
			'ACTION_ALL_ROWS' => false,
			'AJAX_MODE' => 'Y',
			'AJAX_OPTION_HISTORY' => 'N',
			'AJAX_OPTION_JUMP' => 'N',
			'AJAX_OPTION_STYLE' => 'N',
			'SHOW_ROW_CHECKBOXES' => true,
			'SHOW_SELECTED_COUNTER' => true,
			'SHOW_ROW_ACTIONS_MENU' => true,
			'SHOW_ACTION_PANEL' => false,
			'SHOW_MORE_BUTTON' => false,
			'ACTION_PANEL' => [
				'GROUPS' => [
					[
						'ITEMS' => [
							$addToArchiveButton,
							$removeFromArchiveButton,
//							$removeButton,
						],
					],
				]
			],
			'EDITABLE' => false,
			'MESSAGES' => $arResult['ACTION_MESSAGES'],
			'TOP_ACTION_PANEL_RENDER_TO' => (
				$arResult['HAS_ACCESS_TO_TASKS_COUNTERS'] === true
					? '.task-interface-toolbar'
					: '.sonet-interface-toolbar'
			),
			'TOP_ACTION_PANEL_PINNED_MODE' => false,
			'CURRENT_PAGE' => $arResult['CURRENT_PAGE'],
		],
		$component
	);
?></span><?php

?><script>

	BX.ready(function () {
		new BX.Socialnetwork.WorkgroupList.Manager({
			id: '<?= $arResult['GRID_ID'] ?>',
			componentName: '<?= $component->getName() ?>',
			signedParameters: '<?= $component->getSignedParameters() ?>',
			useSlider: <?= (
				ModuleManager::isModuleInstalled('intranet') && SITE_TEMPLATE_ID === 'bitrix24'
					? 'true'
					: 'false'
			) ?>,
			gridId: '<?= CUtil::JSEscape($arResult['GRID_ID']) ?>',
			filterId: '<?= CUtil::JSEscape($arResult['FILTER_ID']) ?>',
			sort: <?= CUtil::PhpToJsObject($arResult['SORT']) ?>,
			items: <?= CUtil::PhpToJsObject(array_fill_keys($arResult['GROUP_ID_LIST'], null)) ?>,
			pageSize: <?= (int)$arResult['PAGE_SIZE'] ?>,
			defaultFilterPresetId: '<?= CUtil::JSEscape($arResult['CURRENT_PRESET_ID']) ?>',
			defaultCounter: '<?= CUtil::JSEscape($arResult['CURRENT_COUNTER']) ?>',
			gridContainerId: '<?= CUtil::JSEscape($gridContainerId) ?>',
			urls: {
				groupUrl: '<?= CUtil::JSEscape($arParams['PATH_TO_GROUP'] ?? Helper\Path::get('group_path_template')) ?>',
				groupLivefeedUrl: '<?= Helper\Path::get('group_livefeed_path_template') ?>',
			},
			useTasksCounters: <?= (
				in_array($arParams['MODE'], WorkgroupList::getTasksModeList(), true)
					? 'true'
					: 'false'
			) ?>,
			tours: <?= Json::encode($arResult['TOURS']) ?>,
			livefeedCounterColumnId: '<?= CUtil::JSEscape(WorkgroupList\Counter::getLivefeedCounterColumnId()) ?>',
			livefeedCounterSliderOptions: <?= CUtil::PhpToJsObject(WorkgroupList\Counter::getLivefeedCounterSliderOptions()) ?>,
		});
	});

	BX.message(<?= Json::encode($messages) ?>);

</script>