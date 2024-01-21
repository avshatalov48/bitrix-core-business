<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\UI\Filter\Theme;
use Bitrix\Mail\Helper\LicenseManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	"mail.client",
	"mail.messagegrid",
	"mail.avatar",
	"mail.directorymenu",
	"ui.progressbar",
	'ui.info-helper',
	'mail.secretary',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.alerts',
	'ui.dialogs.messagebox',
	'ui.hint',
	'ui.icons.service',
	'pull.client',
]);

$APPLICATION->SetAdditionalCSS("/bitrix/css/main/font-awesome.css");

Main\Page\Asset::getInstance()->addJS('/bitrix/components/bitrix/mail.client.message.list/templates/.default/user-interface-manager.js');

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view no-background')));
$filterOptions = [
	'FILTER_ID' => $arResult['FILTER_ID'],
	'GRID_ID' => $arResult['GRID_ID'],
	'ENABLE_LABEL' => true,
	'FILTER' => $arResult['FILTER'],
	'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
	'RESET_TO_DEFAULT_MODE' => true,
	'VALUE_REQUIRED' => true,
	'THEME' => Theme::MUTED,
	'CONFIG' => [
		'AUTOFOCUS' => false
	],
];

$unseenCountInCurrentMailbox = 0;
$unseenCountInOtherMailboxes = 0;

$mailboxMenu = array();
foreach ($arResult['MAILBOXES'] as $mailboxId => $item)
{
	if ($mailboxId !== $arResult['MAILBOX']['ID'])
	{
		$unseenCountInOtherMailboxes += $item['__unseen'];
	}
	else
	{
		$unseenCountInCurrentMailbox += $item['__unseen'];
	}

	$mailboxLockIconHtml = '';

    if (!LicenseManager::checkTheMailboxForSyncAvailability($mailboxId))
    {
		$mailboxLockIconHtml = '<span class="mail-connect-lock-icon"></span>';
    }

    $mailboxMenu[] = array(
		'html' => sprintf(
			'<span class="mail-menu-popup-item-text-wrapper"><span class="main-buttons-item-text">%s</span>%s</span> %s',
			htmlspecialcharsbx($item['NAME']),
			$mailboxLockIconHtml,
			sprintf('<span class="main-buttons-item-counter %s">%u</span>',
				$item['__unseen'] > 0 ? 'js-unseen-mailbox' : 'main-ui-hide',
				$item['__unseen']
			)
		),
		'dataset' => ['mailboxId' => $mailboxId, 'unseen' => $item['__unseen'], 'sliderIgnoreAutobinding' => 'true'],
		'className' => $item['ID'] == $arResult['MAILBOX']['ID'] ? 'menu-popup-item-take' : 'dummy',
		'href' => \CHTTP::urlAddParams(
			\CComponentEngine::makePathFromTemplate(
				$arParams['PATH_TO_MAIL_MSG_LIST'],
				array('id' => $item['ID'],'start_sync_with_showing_stepper'=>false,
				)
			),
			array_filter(array(
				'IFRAME' => isset($_REQUEST['IFRAME']) ? $_REQUEST['IFRAME'] : null,
				'IFRAME_TYPE' => isset($_REQUEST['IFRAME_TYPE']) ? $_REQUEST['IFRAME_TYPE'] : null,
			))
		),
	);
}

$addMailboxMenuItem = array(
	'text' => Loc::getMessage('MAIL_CLIENT_MAILBOX_ADD'),
	'html' => '<span class="main-buttons-item-text">' . Loc::getMessage('MAIL_CLIENT_MAILBOX_ADD') . '</span>',
	'className' => 'dummy',
	'href' => \CComponentEngine::makePathFromTemplate(
		$arParams['PATH_TO_MAIL_CONFIG'],
		array('act' => '')
	),
);

$userMailboxesLimit = $arResult['MAX_ALLOWED_CONNECTED_MAILBOXES'];
if ($userMailboxesLimit >= 0 && $arResult['USER_OWNED_MAILBOXES_COUNT'] >= $userMailboxesLimit)
{
    $addMailboxMenuItem = array(
        'html' => '<div onclick="BX.UI.InfoHelper.show(\'limit_contact_center_mail_box_number\')">'.
            '<span class="mail-connect-lock-text">' . Loc::getMessage('MAIL_CLIENT_MAILBOX_ADD') . '</span>' .
            '<span class="mail-connect-lock-icon"></span>' .
        '</div>',
        'className' => 'dummy',
    );
}

$mailboxMenu[] = array(
	'delimiter' => true,
);
$mailboxMenu[] = $addMailboxMenuItem;

$configPath = \CHTTP::urlAddParams(
	\CComponentEngine::makePathFromTemplate(
		$arParams['PATH_TO_MAIL_CONFIG'],
		array('act' => 'edit')
	),
	array('id' => $arResult['MAILBOX']['ID'])
);
$createPath = \CHTTP::urlAddParams(
	$arParams['PATH_TO_MAIL_MSG_NEW'],
	array('id' => $arResult['MAILBOX']['ID'])
);

$disabledMailSettings = $USER->getId() != $arResult['MAILBOX']['USER_ID'] && !$USER->isAdmin() && !$USER->canDoOperation('bitrix24_config');

$settingsMenu = [
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_BLACKLIST_LINK'),
		'className' => '',
		'href' => htmlspecialcharsbx($arParams['PATH_TO_MAIL_BLACKLIST']),
	],
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_ADDRESSBOOK_LINK'),
		'href' => htmlspecialcharsbx($arParams['PATH_TO_MAIL_ADDRESSBOOK']),
	],
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_SIGNATURE_LINK'),
		'href' => htmlspecialcharsbx($arParams['PATH_TO_MAIL_SIGNATURES']),
	],
	[
		'delimiter' => true,
	],
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_INTEGRATION_WITH_CRM'),
		'className' => '',
		'href' => htmlspecialcharsbx($configPath).'#configcrm',
		'disabled' => ($disabledMailSettings || !$arResult['userHasCrmActivityPermission']),
	],
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_SETTINGS_LINK'),
		'className' => '',
		'href' => htmlspecialcharsbx($configPath),
		'disabled' => $disabledMailSettings,
	],
	[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_LINK'),
		'className' => '',
		'href' => htmlspecialcharsbx($arResult['MAILBOX']['LINK']),
		'target' => "_blank",
		'disabled' => empty($arResult['MAILBOX']['LINK']),
	],
	/*[
		'text' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_MANAGEMENT'),
		'className' => '',
		'onclick' => 'BX.Mail.Home.Grid.openGridSettingsWindow()',
	],*/
];

$this->setViewTarget('mail-msg-counter-panel');

?>
<div class="mail-msg-counter-wrapper">
	<div class = 'mail-counter-toolbar' data-role="mail-counter-toolbar"></div>
	<div data-role = "error-box" class="mail-home-error-box mail-hidden-element">
		<div data-role = "error-box-title" class="error-box-title"></div>
		<div data-role = "error-box-text" class="error-box-text"></div>
		<div data-role = "error-box-hint" class="error-box-hint"></div>
	</div>
</div>
<?

$this->endViewTarget();

	$this->setViewTarget('progress');?>
	<div data-role="mail-progress-bar" class="mail-progress">
		<div class="mail-progress-bar"></div>
	</div>
	<?$this->endViewTarget();

	$this->setViewTarget('inside_pagetitle')?>
		<div></div>
	<?php
		$siteLogo = \Bitrix\Intranet\Util::getClientLogo();
		$siteTitle = trim(COption::GetOptionString("bitrix24", "site_title", ""));

		$logo24 = \Bitrix\Intranet\Util::getLogo24();

		if ($siteTitle == '')
		{
		$siteTitle =
			ModuleManager::isModuleInstalled("bitrix24")
				? Loc::getMessage('BITRIX')
				: COption::GetOptionString("main", "site_name", "");
		}
	?>
	<?$this->endViewTarget();

	$this->setViewTarget('left-panel');?>
		<div class="mail-left-menu-wrapper">
			<div class="mail-left-menu-head">
				<h2 class="mail-left-menu-title">
					<span class="logo-mail"><?=Loc::getMessage('MAIL_CLIENT_HOME_TITLE')?></span>
				</h2>
			</div>
			<div class="mailbox-sync-panel">
				<a class="mailbox-panel ui-btn ui-btn-themes ui-btn-light-border ui-btn-themes mail-btn-dropdown ui-btn-round"
			   data-role="mailbox-current-title"
			   data-mailbox-id="<?=intval($arResult['MAILBOX']['ID']) ?>" title="<?=htmlspecialcharsbx($arResult['MAILBOX']['NAME']) ?>">
<!--					<span class="ui-icon ui-icon-common-user mail-btn-dropdown-icon mail-ui-avatar" user-name="--><?//=htmlspecialcharsbx($arResult['MAILBOX']['NAME']) ?><!--" email="--><?//=htmlspecialcharsbx($arResult['MAILBOX']['NAME']) ?><!--"></span>-->
					<div class="mail-btn-dropdown-title">
						<span class="mail-btn-dropdown-title-mail-name" title="<?=htmlspecialcharsbx($arResult['MAILBOX_NAME'].$arResult['MAILBOX_DOMAIN'])?>">
							<?=htmlspecialcharsbx($arResult['MAILBOX_NAME'])?><?=htmlspecialcharsbx($arResult['MAILBOX_DOMAIN'])?>
						</span>
					</div>
					<span class="ui-btn ui-btn-sm ui-btn-light ui-btn-dropdown"></span>
					<span class="unread-message-marker-for-all-mailboxes <?=($unseenCountInOtherMailboxes > 0 ? '' : 'mail-hidden-element') ?>" data-role="unreadMessageMailboxesMarker"></span>
				</a>
				<div class='mailbox-sync-btn' data-role="mail-msg-sync-button-wrapper">
					</div>
				</div>
			</div>
	<?$this->endViewTarget();

	$this->setViewTarget('above_pagetitle'); ?>
	<div class="mail-home-head">
		<a class="ui-btn ui-btn-success mail-message-new-btn" href="<?=htmlspecialcharsbx($createPath) ?>"
		   style="overflow: hidden; text-overflow: ellipsis; ">
			<?=Loc::getMessage('MAIL_MESSAGE_NEW_BTN') ?>
		</a>

		<div class="pagetitle-container mail-pagetitle-flexible-space">
			<? $APPLICATION->includeComponent(
				'bitrix:main.ui.filter', '',
				$filterOptions
			); ?>
		</div>

		<button class="ui-btn ui-btn-themes ui-btn-light-border ui-btn-icon-setting" type="button"
				style="margin-left: 12px; min-width: 39px; min-width: var(--ui-btn-height);" data-role="mail-list-settings-menu-popup-toggle"
		></button>
	</div>
	<? $this->endViewTarget();

	$this->setViewTarget('below_pagetitle'); ?>

	<?=$APPLICATION->getViewContent('progress') ?>

	<?=$APPLICATION->getViewContent('mail-msg-counter-panel') ?>

	<?=$APPLICATION->getViewContent('mail-msg-temp-alert') ?>

	<? $this->endViewTarget();

$this->setViewTarget('mail-msg-counter-script');

?>

<script type="text/javascript">
(function ()
{
	var uiManager = BX.Mail.Client.Message.List['<?=\CUtil::jsEscape($component->getComponentId()) ?>'].userInterfaceManager;
	BX.onCustomEvent('Grid::updated',[uiManager.getGridInstance()]);
	uiManager.initMailboxes(<?=Main\Web\Json::encode($mailboxMenu) ?>);
	uiManager.updateTotalUnreadCounters(<?=intval($unseenCountInOtherMailboxes) ?>);

	BX.Mail.Home.mailboxCounters.setCounters([
		{
			'path': 'unseenCountInOtherMailboxes',
			'count': <?= $unseenCountInOtherMailboxes ?>
		},
		{
			'path': 'unseenCountInCurrentMailbox',
			'count': <?= $unseenCountInCurrentMailbox ?>
		}
	]);

	if (uiManager.getLastDir() != uiManager.getCurrentFolder())
	{
		uiManager.setLastDir();
		BXMailMailbox.sync(BX.Mail.Home.ProgressBar, '<?=\CUtil::jsEscape($arResult['GRID_ID']) ?>', true,true);
	}

	const curPage = <?= (int)$arResult['NAV_OBJECT']->getCurrentPage(); ?>

	uiManager.updateMessageMailHrefList(
		<?= Main\Web\Json::encode($arResult['MESSAGE_HREF_LIST']) ?>,
		curPage,
		<?= !empty($arResult['ENABLE_NEXT_PAGE']) ? 'true' : 'false' ?>
	);
})();
</script>

<?

$this->endViewTarget();

addEventHandler('main', 'onAfterAjaxResponse', function ()
{
	global $APPLICATION;
	return $APPLICATION->getViewContent('mail-msg-counter-script');
});


if (Main\Loader::includeModule('pull'))
{
	global $USER;
	\CPullWatch::add($USER->getId(), 'mail_mailbox_' . $arResult['MAILBOX']['ID']);
}

$showStepper = 0 == $arResult['MAILBOX']['SYNC_LOCK'];
if ($arResult['MAILBOX']['SYNC_LOCK'] > 0)
{
	$showStepper = time() - $arResult['MAILBOX']['SYNC_LOCK'] > 20;
}

\CJsCore::init(array('update_stepper'));

?>

<? if (empty($arResult['CONFIG_SYNC_DIRS'])): ?>
	<div style="background: #eef2f4; padding-bottom: 1px; margin-bottom: -1px; ">
		<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
			<span class="ui-alert-message"><?=Loc::getMessage('MAIL_CLIENT_CONFIG_DIRS_SYNC_EMPTY_WARNING') ?></span>
		</div>
	</div>
<? endif ?>

	<?=Main\Update\Stepper::getHtml(
		array(
			'mail' => array(
				'Bitrix\Mail\Helper\MessageIndexStepper',
				'Bitrix\Mail\Helper\ContactsStepper',
				'Bitrix\Mail\Helper\MessageClosureStepper',
			),
		),
		Loc::getMessage('MAIL_CLIENT_MAILBOX_INDEX_BAR')
	) ?>

<?

$snippet = new Main\Grid\Panel\Snippet();

$actionPanelActionButtons = [
	[
		'TYPE' => \Bitrix\Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['read']['id'],
		'ICON' => $arResult['gridActionsData']['read']['icon'],
		'TITLE'=> $arResult['gridActionsData']['read']['title'],
		'TEXT'=> $arResult['gridActionsData']['read']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onReadClick()",
					],
				],
			],
		],
	],
	[
		'TYPE' => \Bitrix\Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['notRead']['id'],
		'ICON' => $arResult['gridActionsData']['notRead']['icon'],
		'TITLE'=> $arResult['gridActionsData']['notRead']['title'],
		'TEXT'=> $arResult['gridActionsData']['notRead']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onReadClick()",
					],
				],
			],
		],
	],
	[
		'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
		'ID' => $arResult['gridActionsData']['move']['id'],
		'ICON' => $arResult['gridActionsData']['move']['icon'],
		'TITLE' => $arResult['gridActionsData']['move']['title'],
		'TEXT' => $arResult['gridActionsData']['move']['text'],
		'ITEMS' => $arResult['foldersItems'],
	],
	[
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['spam']['id'],
		'ICON' => $arResult['gridActionsData']['spam']['icon'],
		'TITLE'=> $arResult['gridActionsData']['spam']['title'],
		'TEXT'=> $arResult['gridActionsData']['spam']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onSpamClick()",
					],
				],
			],
		],
	],
	[
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ICON' => $arResult['gridActionsData']['notSpam']['icon'],
		'ID' => $arResult['gridActionsData']['notSpam']['id'],
		'TITLE' => $arResult['gridActionsData']['notSpam']['title'],
		'TEXT' => $arResult['gridActionsData']['notSpam']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onSpamClick()",
					],
				],
			],
		],
	],
	[
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['delete']['id'],
		'ICON' => $arResult['gridActionsData']['delete']['icon'],
		'TITLE'=> $arResult['gridActionsData']['delete']['title'],
		'TEXT'=> $arResult['gridActionsData']['delete']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'CONFIRM' => true,
				'CONFIRM_APPLY_BUTTON' => 'CONFIRM_APPLY_BUTTON',
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDeleteClick()",
					],
				],
			],
		],
	],
	[
		'HIDDEN_IN_PANEL' => true,
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => 'separator',
		'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-separator',
		'ONCHANGE' => [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[

					],
				],
			],
		],
	],
];

	$actionPanelActionButtons = array_merge($actionPanelActionButtons, [
		[
			'HIDDEN_IN_PANEL' => true,
			'TYPE' => Main\Grid\Panel\Types::BUTTON,
			'ID' => $arResult['gridActionsData']['addToCrm']['id'],
			'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-crm-action',
			'TEXT' => '<span data-role="crm-action">' . $arResult['gridActionsData']['addToCrm']['text'] . '</span>',
			'TITLE' => $arResult['gridActionsData']['addToCrm']['title'],
			'DISABLED' => true,
			'ONCHANGE' => [
				[
					'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => [
						[
							'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDisabledGroupActionClick()",
						],
					],
				],
			],
		],
		[
			'HIDDEN_IN_PANEL' => true,
			'TYPE' => Main\Grid\Panel\Types::BUTTON,
			'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-not-crm-action',
			'ID' => $arResult['gridActionsData']['excludeFromCrm']['id'],
			'TEXT' => '<span data-role="not-crm-action">' . $arResult['gridActionsData']['excludeFromCrm']['text'] . '</span>',
			'TITLE' => $arResult['gridActionsData']['excludeFromCrm']['text'],
			'DISABLED' => true,
			'ONCHANGE' => [
				[
					'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
					'DATA' => [
						[
							'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDisabledGroupActionClick()",
						],
					],
				],
			],
		],
	]);

$actionPanelActionButtons = array_merge($actionPanelActionButtons, [
	[
		'HIDDEN_IN_PANEL' => true,
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['task']['id'],
		'TEXT' => $arResult['gridActionsData']['task']['text'],
		'TITLE' => $arResult['gridActionsData']['task']['title'],
		'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-task',
		'DISABLED' => true,
		'ONCHANGE' => [
			[
				'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDisabledGroupActionClick()",
					],
				],
			],
		],
	],
	[
		'HIDDEN_IN_PANEL' => true,
		'TYPE' => Main\Grid\Panel\Types::DROPDOWN,
		'ID' => $arResult['gridActionsData']['discuss']['id'],
		'TEXT' => $arResult['gridActionsData']['discuss']['text'],
		'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-discuss',
		'TITLE' => $arResult['gridActionsData']['discuss']['title'],
		'DISABLED' => true,
	],

	[
		'HIDDEN_IN_PANEL' => true,
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['event']['id'],
		'TEXT' => $arResult['gridActionsData']['event']['text'],
		'TITLE' => $arResult['gridActionsData']['event']['title'],
		'ADDITIONAL_CLASS_FOR_PANEL' => 'mail-meeting',
		'DISABLED' => true,
		'ONCHANGE' => [
			[
				'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDisabledGroupActionClick()",
					],
				],
			],
		],
	],
	[
		'HIDDEN_IN_PANEL' => true,
		'DISABLED' => ($arResult['currentDir'] !== '[Gmail]/All Mail') ? false : true,
		'TYPE' => Main\Grid\Panel\Types::BUTTON,
		'ID' => $arResult['gridActionsData']['deleteImmediately']['id'],
		'TEXT' => $arResult['gridActionsData']['deleteImmediately']['text'],
		'TITLE' => $arResult['gridActionsData']['deleteImmediately']['text'],
		'ONCHANGE' => [
			[
				'ACTION' => Main\Grid\Panel\Actions::CALLBACK,
				'DATA' => [
					[
						'JS' => "BX.Mail.Client.Message.List['" . CUtil::JSEscape($component->getComponentId()) . "'].onDeleteImmediately()",
					],
				],
			],
		],
	],
]);

?>
<div class="mail-msg-list-grid-stub-wrapper"><div class="mail-msg-list-grid-stub" data-role="mail-msg-list-grid-stub"></div>
<div class="mail-msg-list-actionpanel-container" data-role="mail-msg-list-actionpanel-container"></div>
<div class="mail-msg-list-grid" data-role="mail-msg-list-grid">

<script type="text/javascript">
	BX.ready(function()
	{
		var Mail = BX.Mail.Home;

		Mail.Counters.setHiddenCountersForTotalCounter(<?= Main\Web\Json::encode($arResult['invisibleDirsToCounters']) ?>);

		var client = new BX.Mail.Client.Mailer({
			mailboxId: <?= intval($arResult['MAILBOX']['ID']) ?>,
			filterId: '<?= $arResult['FILTER_ID'] ?>',
			syncAvailable: '<?= \Bitrix\Mail\Helper\LicenseManager::isSyncAvailable() ?>',
		});

		Mail.FilterToolbar = client.getFilterToolbar();

		Mail.Counters.addCounters(<?= Main\Web\Json::encode($arResult['DIRS_WITH_UNSEEN_MAIL_COUNTERS']) ?>);

		Mail.mailboxCounters.addCounters([
			{
				'path': 'unseenCountInOtherMailboxes',
				'count': <?= $unseenCountInOtherMailboxes ?>
			},
			{
				'path': 'unseenCountInCurrentMailbox',
				'count': <?= $unseenCountInCurrentMailbox ?>
			}
		]);

		BX.addCustomEvent(
			'BX.UI.ActionPanel:created',
			function (panel)
			{
				Mail.Grid.setPanel(panel);
				if (panel.params.gridId == '<?=\CUtil::jsEscape($arResult['GRID_ID']); ?>')
				{
					var disableItem = panel.disableItem.bind(panel);
					panel.disableItem = function (item)
					{
						if (item) disableItem(item);
					};

					var fixPanel = panel.fixPanel.bind(panel);
					panel.fixPanel = function()
					{
						document.body.appendChild(this.getPanelContainer());
						fixPanel();
					};

					var unfixPanel = panel.unfixPanel.bind(panel);
					panel.unfixPanel = function()
					{
						var container = BX.Main.gridManager.getInstanceById(panel.params.gridId).getContainer();
						container.parentNode.insertBefore(this.getPanelContainer(), container);
						unfixPanel();
					};

					setTimeout(panel.unfixPanel.bind(panel));

					//cancellation of reset of checkboxes when clicking outside the panel and grid area
					panel.handleOuterClick = function()
					{
					};
				}
			}
		);
	});
</script>

<?

$APPLICATION->includeComponent(
	'bitrix:main.ui.grid', '',
	array(
		'GRID_ID' => $arResult['GRID_ID'],
		'MESSAGES' => $arResult['MESSAGES'],
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_HISTORY' => 'N',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'TOP_ACTION_PANEL_CLASS' => 'mail-msg-list-action-panel',
		'TOP_ACTION_PANEL_RENDER_TO' => '.mail-msg-list-actionpanel-container',
		'SHOW_ACTION_PANEL' => false,
		'TOP_ACTION_PANEL_PINNED_MODE' => true,
		'HEADERS' => array(
			array('id' => 'FROM', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_FROM'), 'class' => 'mail-msg-list-from-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'SUBJECT', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_SUBJECT'), 'class' => 'mail-msg-list-subject-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'DATE', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_DATE'), 'class' => 'mail-msg-list-date-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			//array('id' => 'ICAL', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_ICAL'), 'class' => 'mail-msg-list-ical-cell-head', 'default' => false, 'editable' => false, 'showname' => false),

			array('id' => 'CRM_BIND', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_CRM_BIND'), 'class' => 'mail-msg-list-crm-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'TASK_BIND', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_TASK_BIND'), 'class' => 'mail-msg-list-task-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'CHAT_BIND', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_CHAT_BIND'), 'class' => 'mail-msg-list-chat-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'POST_BIND', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_POST_BIND'), 'class' => 'mail-msg-list-post-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
			array('id' => 'MEETING_BIND', 'name' => Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_MEETING_BIND'), 'class' => 'mail-msg-list-meeting-cell-head', 'default' => true, 'editable' => false, 'showname' => false),
		),

		'ROWS' => $arResult['ROWS'],

		'SHOW_GRID_SETTINGS_MENU' => false,
		'ALLOW_COLUMNS_SORT' => false,
		'ALLOW_ROWS_SORT' => false,
		'SHOW_NAVIGATION_PANEL' => false,

		'SHOW_MORE_BUTTON' => true,
		'ENABLE_NEXT_PAGE' => !empty($arResult['ENABLE_NEXT_PAGE']),
		'NAV_PARAM_NAME' => $arResult['NAV_OBJECT']->getId(),
		'CURRENT_PAGE' => $arResult['NAV_OBJECT']->getCurrentPage(),
		'ACTION_PANEL' => array(
			'GROUPS' => [
				['ITEMS' => $actionPanelActionButtons],
			],
		),
		'ACTION_PANEL_OPTIONS' => [
			'MAX_HEIGHT' => 56
		],

		'SHOW_CHECK_ALL_CHECKBOXES' => true,
	)
);

?>

</div>

<script type="text/javascript">
	// workaround to prevent page title update after reloading grid in some side panel
	if(window !== window.top)
	{
		if(BX.type.isFunction(top.BX.ajax.UpdatePageTitle)) top.BX.ajax.UpdatePageTitle = (function() {});
		if(BX.type.isFunction(top.BX.ajax.UpdatePageData)) top.BX.ajax.UpdatePageData = (function() {});
	}

	BX.message({
		MAILBOX_IS_SYNC_AVAILABILITY: '<?= CUtil::JSEscape($arResult['MAILBOX_IS_SYNC_AVAILABILITY']) ?>',
		DEFAULT_DIR: '<?= CUtil::JSEscape($arResult['defaultDir']) ?>',
		MESSAGES_ALREADY_EXIST_IN_FOLDER : '<?= Loc::getMessage('MESSAGES_ALREADY_EXIST_IN_FOLDER') ?>',
		MAILBOX_LINK: '<?= CUtil::JSEscape($arResult['MAILBOX']['LINK'])?>',
		MAIL_MESSAGE_GRID_ID: '<?= CUtil::JSEscape($arResult['GRID_ID'])?>',
		INTERFACE_MAIL_CHECK_ALL: '<?=Loc::getMessage('INTERFACE_MAIL_CHECK_ALL')?>',
		MAIL_MESSAGE_LIST_COLUMN_BIND_TASKS_TASK: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_BIND_TASKS_TASK')) ?>',
		MAIL_MESSAGE_LIST_COLUMN_BIND_CRM_ACTIVITY: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_BIND_CRM_ACTIVITY')) ?>',
		MAIL_MESSAGE_LIST_COLUMN_BIND_IM_CHAT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_BIND_IM_CHAT')) ?>',
		MAIL_MESSAGE_LIST_COLUMN_BIND_CALENDAR_EVENT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_BIND_CALENDAR_EVENT')) ?>',
		MAIL_MESSAGE_LIST_COLUMN_BIND_BLOG_POST: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_COLUMN_BIND_BLOG_POST')) ?>',
		MAIL_CLIENT_AJAX_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR')) ?>',
		MAIL_MESSAGE_LIST_BTN_SEEN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SEEN')) ?>',
		MAIL_MESSAGE_LIST_BTN_UNSEEN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_BTN_UNSEEN')) ?>',
		MAIL_MESSAGE_LIST_BTN_DELETE: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_BTN_DELETE')) ?>',
		MAIL_MESSAGE_LIST_BTN_NOT_SPAM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_BTN_NOT_SPAM')) ?>',
		MAIL_MESSAGE_LIST_BTN_SPAM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_BTN_SPAM')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_DELETE: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_TITLE: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_TITLE')) ?>',
		MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM')) ?>',
		MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR')) ?>',
		MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM')) ?>',
		MAIL_MESSAGE_LIST_NOTIFY_SUCCESS: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_SUCCESS')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_CANCEL_BTN')) ?>',
		MAIL_MESSAGE_SYNC_BTN_HINT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_SYNC_BTN_HINT')) ?>',
		MAIL_CLIENT_MAILBOX_SYNC_BAR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_MAILBOX_SYNC_BAR')) ?>',
		MAIL_CLIENT_MAILBOX_SYNC_BAR_INTERRUPTED: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_MAILBOX_SYNC_BAR_INTERRUPTED')) ?>',
		MAIL_CLIENT_BUTTON_LOADING: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_CLIENT_BUTTON_LOADING')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_MOVE_ALL: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_MOVE_ALL')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_SPAM_ALL: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_SPAM_ALL')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_TRASH_ALL: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_TRASH_ALL')) ?>',
		MAIL_MESSAGE_LIST_CONFIRM_DELETE_ALL: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE_ALL')) ?>',
		MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT')) ?>',
		MAIL_MESSAGE_ICAL_NOTIFY_REJECT: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_REJECT')) ?>',
		MAIL_MESSAGE_ICAL_NOTIFY_ERROR: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ERROR')) ?>'
	});

	var numberOfRowsPerPage = 25;

	BX.ready(function()
	{
		var Mail = BX.Mail.Home;
		Mail.Grid.setGridId('<?= CUtil::JSEscape($arResult['GRID_ID'])?>');
		var mailboxId = Number(<?= intval($arResult['MAILBOX']['ID']) ?>);

		BX.addCustomEvent("onPullEvent-mail", BX.delegate(function(command, params){
			if (Mail.Grid.getCountDisplayed() < numberOfRowsPerPage && command === 'new_message_is_synchronized' && mailboxId === Number(params.mailboxId) && mailMessageList.getCurrentFolder() === params.dir)
			{
				BX.ajax.runComponentAction('bitrix:mail.client.message.list', 'syncMailCounters',
					{
						mode: 'class',
						data:
						{
							mailboxId: <?= intval($arResult['MAILBOX']['ID']) ?>,
						}
				});
				Mail.Grid.reloadTable();
			}
			else if (command ==='counters_updated' && mailboxId === Number(params.mailboxId))
			{
				mailMessageList.updateCountersFromBackend();
			}
			else if (command ==='counters_is_synchronized')
			{
				const data = params.dirs || {};
				BX.Mail.Home.Counters.setCounters(data);
			}
		}, this));

		<?php if($arParams['VARIABLES']['start_sync_with_showing_stepper']==='true')
		{
		?>
			if(!Mail.Grid.getCountDisplayed())
			{
				Mail.Grid.setGridWrapper(document.querySelector('[data-role="mail-msg-list-grid"]'));
				Mail.Grid.setGridStub(document.querySelector('[data-role="mail-msg-list-grid-stub"]'));
				Mail.Grid.enableLoadingMessagesStub();
			}
		<?php
		}
		?>

		BX.Mail.Home.LeftMenuNode = new Mail.LeftMenu({
			mailboxId: <?= intval($arResult['MAILBOX']['ID']) ?>,
			dirsWithUnseenMailCounters: <?= Main\Web\Json::encode($arResult['DIRECTORY_HIERARCHY_WITH_UNSEEN_MAIL_COUNTERS']) ?>,
			filterId: '<?= $arResult['FILTER_ID'] ?>',
			systemDirs :
				{
					spam: '<?= CUtil::JSEscape($arResult['spamDir']) ?>',
					trash: '<?= CUtil::JSEscape($arResult['trashDir']) ?>',
					drafts: '<?= CUtil::JSEscape($arResult['draftsDir']) ?>',
					outcome: '<?= CUtil::JSEscape($arResult['outcomeDir']) ?>',
					inbox: '<?= CUtil::JSEscape($arResult['defaultDir']) ?>',
				}
		});

		var mailMessageList = new BX.Mail.Client.Message.List({
			id: '<?= CUtil::JSEscape($component->getComponentId())?>',
			gridId: '<?= CUtil::JSEscape($arResult['GRID_ID'])?>',
			mailboxId: <?= intval($arResult['MAILBOX']['ID']) ?>,
			PATH_TO_USER_TASKS_TASK: '<?=\CUtil::jsEscape($arParams['PATH_TO_USER_TASKS_TASK']) ?>',
			PATH_TO_USER_BLOG_POST: '<?=\CUtil::jsEscape($arParams['PATH_TO_USER_BLOG_POST']) ?>',
			mailboxMenu: <?= Main\Web\Json::encode($mailboxMenu) ?>,
			settingsMenu: <?= Main\Web\Json::encode($settingsMenu) ?>,
			canDelete: <?= CUtil::PhpToJSObject((bool)$arResult['trashDir']); ?>,
			canMarkSpam: <?= CUtil::PhpToJSObject((bool)$arResult['spamDir']); ?>,
			outcomeDir: '<?= CUtil::JSEscape($arResult['outcomeDir']) ?>',
			inboxDir: '<?= CUtil::JSEscape($arResult['defaultDir']) ?>',
			spamDir: '<?= CUtil::JSEscape($arResult['spamDir']) ?>',
			trashDir: '<?= CUtil::JSEscape($arResult['trashDir']) ?>',
			enableNextPage: '<?= !empty($arResult['ENABLE_NEXT_PAGE']) ?>' ?? false,
			MESSAGE_MAIL_HREF_LIST: <?= Main\Web\Json::encode($arResult['MESSAGE_HREF_LIST']) ?>,
			ENTITY_TYPE_NO_BIND: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_NO_BIND) ?>',
			ENTITY_TYPE_CRM_ACTIVITY: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY) ?>',
			ENTITY_TYPE_TASKS_TASK: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_TASKS_TASK) ?>',
			ENTITY_TYPE_BLOG_POST: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_BLOG_POST) ?>',
			ENTITY_TYPE_IM_CHAT: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_IM_CHAT) ?>',
			ENTITY_TYPE_CALENDAR_EVENT: '<?= CUtil::JSEscape(\Bitrix\Mail\Internals\MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT) ?>',
			ERROR_CODE_CAN_NOT_MARK_SPAM: 'MAIL_CLIENT_SPAM_FOLDER_NOT_SELECTED_ERROR',
			ERROR_CODE_CAN_NOT_DELETE: 'MAIL_CLIENT_TRASH_FOLDER_NOT_SELECTED_ERROR'
		});

		var mailboxData = <?=Main\Web\Json::encode(array(
			'ID'       => $arResult['MAILBOX']['ID'],
			'EMAIL'    => $arResult['MAILBOX']['EMAIL'],
			'NAME'     => $arResult['MAILBOX']['NAME'],
			'USERNAME' => $arResult['MAILBOX']['USERNAME'],
			'SERVER'   => $arResult['MAILBOX']['SERVER'],
			'PORT'     => $arResult['MAILBOX']['PORT'],
			'USE_TLS'  => $arResult['MAILBOX']['USE_TLS'],
			'LOGIN'    => $arResult['MAILBOX']['LOGIN'],
			'LINK'     => $arResult['MAILBOX']['LINK'],
			'OPTIONS'  => array(
				'flags' => !empty($arResult['MAILBOX']['OPTIONS']['flags']) ? $arResult['MAILBOX']['OPTIONS']['flags'] : [],
				'inboxDir' => $arResult['defaultDir'],
			),
		)) ?>;

		BXMailMailbox.init(mailboxData);

		<? if (\Bitrix\Mail\Helper\LicenseManager::isSyncAvailable() && !empty($arResult['CONFIG_SYNC_DIRS'])): ?>
			if('<?=($arParams['VARIABLES']['start_sync_with_showing_stepper']!=='true') ?>' || Mail.Grid.getCountDisplayed())
			{
				BXMailMailbox.sync(BX.Mail.Home.ProgressBar, '<?=\CUtil::jsEscape($arResult['GRID_ID']) ?>',false,true);
			}
			else
			{
				BXMailMailbox.sync(BX.Mail.Home.ProgressBar, '<?=\CUtil::jsEscape($arResult['GRID_ID']) ?>',false,true);
			}
		<? endif ?>

		BX.PULL && BX.PULL.extendWatch('mail_mailbox_<?=intval($arResult['MAILBOX']['ID']) ?>');
		BX.addCustomEvent(
			'onPullEvent-mail',
			function (command, params)
			{
				if ('mailbox_sync_status' === command)
				{
					if (<?=intval($arResult['MAILBOX']['ID']) ?> == params.id && mailMessageList.getCurrentFolder() === params.dir)
					{
						BXMailMailbox.syncProgress(
							BX.Mail.Home.ProgressBar,
							'<?=\CUtil::jsEscape($arResult['GRID_ID']) ?>',
							params
						);
					}
				}
			}
		);

		BX.addCustomEvent(
			'SidePanel.Slider:onMessage',
			function (event)
			{
				var grid = BX.Main.gridManager.getInstanceById('<?=\CUtil::jsEscape($arResult['GRID_ID']) ?>');

				var urlParams = {};
				if (window !== window.top)
				{
					urlParams.IFRAME = 'Y';
				}

				if (event.getEventId() == 'mail-mailbox-config-success')
				{
					event.data.handled = true;
					if (event.data.id != <?=intval($arResult['MAILBOX']['ID']) ?> || event.data.changed)
					{
						grid && grid.tableFade();
						window.location.href = BX.util.add_url_param(
							'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_MSG_LIST']) ?>'.replace('#id#', event.data.id).replace('#start_sync_with_showing_stepper#', true),
							urlParams
						);
					}
				}
				else if (event.getEventId() == 'mail-mailbox-config-delete')
				{
					grid && grid.tableFade();
					window.location.href = BX.util.add_url_param(
						'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_HOME']) ?>',
						urlParams
					);
				}
				else if (event.getEventId() === 'mail-message-reload-grid')
				{
					grid && grid.reload();
				}
				else if (event.getEventId() == 'mail-message-create-task')
				{
					BX.Mail.Client.Message.List['<?=\CUtil::jsEscape($component->getComponentId()) ?>'].onCreateTaskEvent(event);
				}
				else if (event.getEventId() == 'mail-mailbox-config-close')
				{
					if (event.data.changed)
					{
						grid && grid.tableFade();
						window.location.href = BX.util.add_url_param(
							'<?=\CUtil::jsEscape($arParams['PATH_TO_MAIL_HOME']) ?>',
							urlParams
						);
					}
				}
			}
		);

		top.BX.addCustomEvent("SidePanel.Slider:onOpen", (event) => {
			const slider = event.getSlider();
			const dictionary = slider.getData();
			dictionary.set(
				'hrefList',
				mailMessageList.userInterfaceManager.MESSAGE_MAIL_HREF_LIST
			);
			dictionary.set('enableNextPage', mailMessageList.userInterfaceManager.enableNextPage ?? false);

			const views = slider.getWindow()?.BXMailView?.__views
			let view = null;
			if(slider.getWindow()?.BXMailView?.__views)
			{
				const keys = Object.keys(views);
				view = views[keys[0]];
			}

			if(view && view?.pageSwapper)
			{
				view.pageSwapper.updatePagesHref(slider.getData().get('hrefList'));
			}
		});

		if (window === window.top)
		{
			BX.data(
				BX.findChildByClassName(
					BX('bx_left_menu_menu_external_mail') || BX('menu_external_mail'),
					'menu-item-link',
					true
				),
				'slider-ignore-autobinding',
				'true'
			);
		}

		<? if (empty($arResult['CONFIG_SYNC_DIRS'])): ?>
		var url = '<?=\CUtil::jsEscape(\CHTTP::urlAddParams(
			$arParams['PATH_TO_MAIL_CONFIG_DIRS'],
			['mailboxId' => $arResult['MAILBOX']['ID']]
		)) ?>';

		top.BX.SidePanel.Instance.open(
			url
		);
		<? endif ?>
	});

</script>
