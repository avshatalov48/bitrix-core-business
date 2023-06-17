<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
\Bitrix\Main\Loader::includeModule('socialnetwork');

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'bizpoc-automation-body');

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'tooltip',
	'admin_interface',
	'date',
	'uploader',
	'file_dialog',
	'bp_user_selector',
	'bp_field_type',
	'dnd',
	'ls',
	'bizproc.local-settings',
	'bizproc.automation',
	'bizproc.automation.robot-selector',
	'bizproc.automation.guide',
	'bizproc.globals',
	'bizproc.debugger',
	'sidepanel',
	'ui.actionpanel',
	'ui.buttons',
	'ui.forms',
	'ui.hint',
	'ui.notification',
	'ui.alerts',
	'ui.dialogs.messagebox',
	'ui.entity-selector',
	'ui.hint',
]);
/**
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 */

$titleView = $arResult['TITLE_VIEW'];
$titleEdit = $arResult['TITLE_EDIT'];
$robotsLimit = $arParams['ROBOTS_LIMIT'] ?? 0;

if ($arResult['USE_DISK'])
{
	$this->addExternalJs($this->GetFolder().'/disk_uploader.js');
	$this->addExternalCss('/bitrix/js/disk/css/legacy_uf_common.css');
}
$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

if (isset($arParams['~MESSAGES']) && is_array($arParams['MESSAGES']))
{
	$messages = $arParams['~MESSAGES'] + $messages;
}

if (\Bitrix\Main\Loader::includeModule('rest'))
{
	CJSCore::Init(['marketplace', 'applayout']);
}

$getHint = function ($messageCode) use ($messages)
{
	$text = isset($messages[$messageCode]) ? $messages[$messageCode] : GetMessage($messageCode);
	return htmlspecialcharsbx(nl2br($text));
};

$getMessage = function ($messageCode) use ($messages)
{
	return isset($messages[$messageCode]) ? $messages[$messageCode] : GetMessage($messageCode);
};

if (!isset($arParams['HIDE_TOOLBAR']) || $arParams['HIDE_TOOLBAR'] !== 'Y'):
	$this->SetViewTarget('pagetitle') ?>
	<div class="ui-btn-container">
		<?php
		if (\Bitrix\Main\Loader::includeModule('intranet'))
		{
			$context = [];
			$type = isset($arResult['DOCUMENT_TYPE'][2]) ? $arResult['DOCUMENT_TYPE'][2] : '';
			if (mb_strpos($type, 'TASK_PLAN_') === 0)
			{
				$context = [
					'USER_ID' => mb_substr($type, mb_strlen('TASK_PLAN_'))
				];
				$type = 'task';
			}
			else if (mb_strpos($type, 'TASK_PROJECT_') === 0)
			{
				$context = [
					'GROUP_ID' => mb_substr($type, mb_strlen('TASK_PROJECT_'))
				];
				$type = 'task';
			}

			$APPLICATION->includeComponent(
				'bitrix:intranet.binding.menu',
				'',
				array(
					'SECTION_CODE' => 'bizproc_automation',
					'MENU_CODE' => $type,
					'CONTEXT' => $context
				)
			);
		}
		?>
		<?php if ($arResult['CAN_DEBUG']): ?>
			<button
				class="ui-btn ui-btn-primary"
				onclick="BX.Bizproc.Automation.Debugger.showStartPage();"
			><?= GetMessage('BIZPROC_AUTOMATION_CMP_DEBUGGER') ?></button>
		<?php endif ?>
	</div>
	<?php $this->EndViewTarget();


$menuTabs = [];
$menuTabs[] = [
	'ID' => 'robots',
	'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST'),
	'IS_ACTIVE' => true,
];

$menuTabs[] = [
	'ID' => 'global_variables',
	'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_AUTOMATION_CMP_GLOB_VAR_MENU'),
	'ON_CLICK' => 'BX.Bizproc.Automation.showGlobals.showVariables()',
];
$menuTabs[] = [
	'ID' => 'global_constants',
	'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_AUTOMATION_CMP_GLOB_CONST_MENU'),
	'ON_CLICK' => 'BX.Bizproc.Automation.showGlobals.showConstants()',
];
if ($arResult['CAN_DEBUG'])
{
	$menuTabs[] = [
		'ID' => 'debug_sessions',
		'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_AUTOMATION_CMP_DEBUGGER_SESSION_LIST_MENU'),
		'ON_CLICK' => 'BX.Bizproc.Automation.Debugger.showDebugSessions()',
	];
}

$APPLICATION->IncludeComponent(
	"bitrix:main.interface.buttons",
	"",
	[
		"ID" => 'bp_automation_menu',
		"ITEMS" => $menuTabs,
		"EDIT_MODE" => false,
	]
);
endif;
?>

<div class="automation-base<?=(count($arResult['TEMPLATES']) <= 1 ?'automation-base-script-mode':'')?>" data-role="automation-base-node">
		<?php if (!isset($arParams['HIDE_TOOLBAR']) || $arParams['HIDE_TOOLBAR'] !== 'Y'):?>
		<div class="automation-base-node-top" data-role="automation-actionpanel">
			<div class="automation-base-node-title">
				<?=GetMessage('BIZPROC_AUTOMATION_CMP_SUBTITLE')?>
			</div>
			<div class="automation-base-button" data-role="automation-base-toolbar">
				<button
					class="ui-btn ui-btn-success ui-btn-themes <?php if (!$arResult['CAN_EDIT']):?> ui-btn-disabled<?php endif?>"
					<?php if ($arResult['CAN_EDIT']):?>data-role="automation-btn-create"<?php endif?>
					>
					<?=$getMessage('BIZPROC_AUTOMATION_CMP_CREATE')?>
				</button>

				<?php if (!empty($arParams['~CATEGORY_SELECTOR'])):?>
					<div class="ui-btn ui-btn-dropdown ui-btn-themes ui-btn-light-border bizproc-automation-category-selector" data-role="category-selector">
						<?=htmlspecialcharsbx($arParams['~CATEGORY_SELECTOR']['TEXT'])?>
					</div>
				<?php endif ?>

				<div class="ui-ctl ui-ctl-inline ui-ctl-before-icon ui-ctl-after-icon automation-toolbar-search">
					<div class="ui-ctl-before ui-ctl-icon-search"></div>
					<a class="ui-ctl-after ui-ctl-icon-clear" data-role="automation-search-clear"></a>
					<input type="text" data-role="automation-search" class="ui-ctl-element automation-toolbar-search-input" placeholder="<?=GetMessage('BIZPROC_AUTOMATION_CMP_SEARCH_PLACEHOLDER')?>">
				</div>
			</div>
		</div>
		<?php endif;?>
	<?php if ($robotsLimit):?>
		<div class="ui-alert ui-alert-xs ui-alert-warning ui-alert-icon-warning">
			<span class="ui-alert-message"><?=GetMessage('BIZPROC_AUTOMATION_ROBOTS_LIMIT_MAIN_ALERT', ['#LIMIT#' => $robotsLimit])?></span>
		</div>
	<?php endif;?>
	<div class="automation-base-node">
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-list">
				<?php if (count($arResult['STATUSES']) > 1): foreach ($arResult['STATUSES'] as $statusId => $status):
					$color = htmlspecialcharsbx($status['COLOR'] ? str_replace('#','',$status['COLOR']) : 'acf2fa');
				?>
				<div class="bizproc-automation-status-list-item">
					<div class="bizproc-automation-status-title" data-role="automation-status-title" data-bgcolor="<?=$color?>">
						<?=htmlspecialcharsbx(isset($status['NAME']) ? $status['NAME'] : $status['TITLE'])?>
						<div class="bizproc-automation__status--bg" style="background-color: <?='#'.$color?>; border-color: <?='#'.$color?>;">
							<span class="bizproc-automation__status--bg-arrow"></span>
						</div>
					</div>
					<div style="padding-top: 12px">
						<div class="bizproc-automation-robot-btn-block" data-role="add-button-container" data-status-id="<?=htmlspecialcharsbx($statusId)?>"></div>
					</div>
				</div>
				<?php endforeach; else:?>
					<?php if ($arResult['STATUSES']): ?>
					<div class="bizproc-automation-status-list-item">
						<div style="padding-top: 12px">
							<div class="bizproc-automation-robot-btn-block" data-role="add-button-container" data-status-id="<?=htmlspecialcharsbx(array_key_first($arResult['STATUSES']))?>"></div>
						</div>
					</div>
					<?php endif; ?>
					<div class="bizproc-automation-status-list-item"></div>
				<?php endif;?>
				<a href="<?=htmlspecialcharsbx($arResult['STATUSES_EDIT_URL'])?>"
					class="bizproc-automation-status-list-config"
					<?php if ($arResult['FRAME_MODE']):?>target="_blank"<?php endif;?>
				></a>
			</div>
		</div>
		<?php if (!empty($arResult['AVAILABLE_TRIGGERS'])):?>
		<!-- triggers -->
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-name">
				<span class="bizproc-automation-status-name-bg"><?=GetMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_LIST')?>
					<span class="bizproc-automation-status-help" data-hint="<?=$getHint('BIZPROC_AUTOMATION_CMP_TRIGGER_HELP_3')?>" data-hint-html="y"></span>
				</span>
				<span class="bizproc-automation-status-line"></span>
			</div>
			<div class="bizproc-automation-status-list">
			<?php foreach (array_keys($arResult['STATUSES']) as $statusId):?>
				<div class="bizproc-automation-status-list-item" data-type="column-trigger">
					<div data-role="trigger-list" class="bizproc-automation-trigger-list" data-status-id="<?=htmlspecialcharsbx($statusId)?>"></div>
				</div>
			<?php endforeach;?>
			</div>
		</div>
		<?php endif;?>
		<!-- robots -->
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-name">
				<span class="bizproc-automation-status-name-bg"><?=GetMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST')?>
					<span class="bizproc-automation-status-help" data-hint="<?=$getHint('BIZPROC_AUTOMATION_CMP_ROBOT_HELP')?>" data-hint-html="y"></span>
				</span>
				<span class="bizproc-automation-status-line"></span>
			</div>
			<div class="bizproc-automation-status-list">
				<?php foreach (array_keys($arResult['STATUSES']) as $statusId):?>
					<div class="bizproc-automation-status-list-item" data-type="column-robot" data-role="automation-template" data-status-id="<?=htmlspecialcharsbx($statusId)?>">
						<div data-role="robot-list" class="bizproc-automation-robot-list" data-status-id="<?=htmlspecialcharsbx($statusId)?>"></div>
						<div data-role="buttons" class="bizproc-automation-robot-btn-block"></div>
					</div>
				<?php endforeach;?>
			</div>
		</div>
	</div>
	<?php if (!isset($arParams['HIDE_SAVE_CONTROLS']) || $arParams['HIDE_SAVE_CONTROLS'] !== 'Y'):?>
	<div class="bizproc-automation-buttons" data-role="automation-buttons" style="display: none">
		<?php $APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
			'BUTTONS' =>
			[
				'save',
				[
					'type' => 'custom',
					'layout' => '<input type="submit" class="ui-btn ui-btn-link"  data-role="automation-btn-cancel" name="cancel"  value="'.GetMessage('BIZPROC_AUTOMATION_CMP_CANCEL').'">'
				]
			]
		]);?>
	</div>
	<?php endif?>
</div>
<script>
	BX.ready(function()
	{
		BX.namespace('BX.Bizproc.Automation');
		if (typeof BX.Bizproc.Automation.Component === 'undefined')
		{
			return;
		}

		var baseNode = document.querySelector('[data-role="automation-base-node"]');
		if (baseNode)
		{
			BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);
			BX.message({
				BIZPROC_AUTOMATION_YES: '<?=GetMessageJS('MAIN_YES')?>',
				BIZPROC_AUTOMATION_NO: '<?=GetMessageJS('MAIN_NO')?>',
				BIZPROC_AUTOMATION_DELAY_MIN_LIMIT_LABEL: '<?=CUtil::JSEscape($arResult['DELAY_MIN_LIMIT_LABEL'])?>',
				BIZPROC_AUTOMATION_RIGHTS_ERROR: '<?=GetMessageJS("BIZPROC_AUTOMATION_RIGHTS_ERROR")?>',
				BIZPOC_AUTOMATION_NO_ROBOT_SELECTED: '<?=GetMessageJS("BIZPOC_AUTOMATION_NO_ROBOT_SELECTED")?>',
			});

			var viewMode = BX.Bizproc.Automation.Component.ViewMode.Edit;

			(new BX.Bizproc.Automation.Component(baseNode))
				.init(<?=\Bitrix\Main\Web\Json::encode([
					'AJAX_URL' => '/bitrix/components/bitrix/bizproc.automation/ajax.php',
					'WORKFLOW_EDIT_URL' => $arResult['WORKFLOW_EDIT_URL'],
					'CONSTANTS_EDIT_URL' => $arResult['CONSTANTS_EDIT_URL'],
					'PARAMETERS_EDIT_URL' => $arResult['PARAMETERS_EDIT_URL'],
					'CAN_EDIT' => $arResult['CAN_EDIT'],

					'DOCUMENT_TYPE' => $arResult['DOCUMENT_TYPE'],
					'DOCUMENT_CATEGORY_ID' => $arResult['DOCUMENT_CATEGORY_ID'],
					'DOCUMENT_ID' => $arResult['DOCUMENT_ID'],
					'DOCUMENT_SIGNED' => $arResult['DOCUMENT_SIGNED'],
					'DOCUMENT_TYPE_SIGNED' => $arResult['DOCUMENT_TYPE_SIGNED'],
					'DOCUMENT_STATUS' => $arResult['DOCUMENT_STATUS'],
					'DOCUMENT_STATUS_LIST' => array_values($arResult['STATUSES']),
					'DOCUMENT_FIELDS' => $arResult['DOCUMENT_FIELDS'],

					'ENTITY_NAME' => $arResult['ENTITY_NAME'],

					'TRIGGERS' => $arResult['TRIGGERS'],
					'TEMPLATES' => $arResult['TEMPLATES'],
					'IS_TEMPLATES_SCHEME_SUPPORTED' => $arResult['IS_TEMPLATES_SCHEME_SUPPORTED'],
					'AVAILABLE_ROBOTS' => $arResult['AVAILABLE_ROBOTS'],
					'AVAILABLE_TRIGGERS' => $arResult['AVAILABLE_TRIGGERS'],
					'TRIGGER_CAN_SET_EXECUTE_BY' => $arResult['TRIGGER_CAN_SET_EXECUTE_BY'],
					'GLOBAL_CONSTANTS' => $arResult['GLOBAL_CONSTANTS'],
					'GLOBAL_VARIABLES' => $arResult['GLOBAL_VARIABLES'],
					'LOG' => $arResult['LOG'],

					'B24_TARIF_ZONE' => $arResult['B24_TARIF_ZONE'] ?? '',
					'USER_OPTIONS' => $arResult['USER_OPTIONS'],
					'FRAME_MODE' => $arResult['FRAME_MODE'],
					'IS_EMBEDDED' => $arResult['IS_EMBEDDED'],
					'SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING' => $arResult['SHOW_TEMPLATE_PROPERTIES_MENU_ON_SELECTING'],

					'MARKETPLACE_ROBOT_CATEGORY' => 'crm_bots', //$arParams['MARKETPLACE_ROBOT_CATEGORY'],
					'MARKETPLACE_TRIGGER_PLACEMENT' => $arParams['MARKETPLACE_TRIGGER_PLACEMENT'] ?? '',
					'ROBOTS_LIMIT' => $robotsLimit,

					'DELAY_MIN_LIMIT_M' => $arResult['DELAY_MIN_LIMIT_M'],
					'IS_WORKTIME_AVAILABLE' => $arResult['IS_WORKTIME_AVAILABLE'],
				])?>, viewMode);
		}
	});
</script>
