<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

UI\Extension::load('ui.buttons');
UI\Extension::load('mail.secretary');

$message = $arResult['MESSAGE'];

$createMenu = array(
	'TASKS_TASK' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_TASK_BTN'),
		'href' => \CHTTP::urlAddParams(
			\CComponentEngine::makePathFromTemplate(
				$arParams['PATH_TO_USER_TASKS_TASK'],
				array(
					'action' => 'edit',
					'task_id' => '0',
				)
			),
			array(
				'TITLE' => rawurlencode(Loc::getMessage(
					'MAIL_MESSAGE_ACTIONS_TASK_TITLE',
					array(
						'#SUBJECT#' => $message['SUBJECT'] ?: Loc::getMessage('MAIL_MESSAGE_ACTIONS_SUBJECT_PLACEHOLDER')
					)
				)),
				'UF_MAIL_MESSAGE' => (int) $message['ID'],
			)
		),
	),
	'CRM_ACTIVITY' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_CRM_BTN'),
	),
	'CRM_EXCLUDE' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_CRM_EXCLUDE_BTN'),
	),
	'BLOG_POST' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_FEED_POST_BTN'),
		'href' => \CHTTP::urlAddParams(
			\CComponentEngine::makePathFromTemplate(
				$arParams['PATH_TO_USER_BLOG_POST_EDIT'],
				array(
					'post_id' => '0',
				)
			),
			array(
				'TITLE' => rawurlencode(Loc::getMessage(
					'MAIL_MESSAGE_ACTIONS_POST_TITLE',
					array(
						'#SUBJECT#' => $message['SUBJECT'] ?: Loc::getMessage('MAIL_MESSAGE_ACTIONS_SUBJECT_PLACEHOLDER')
					)
				)),
				'UF_MAIL_MESSAGE' => (int) $message['ID'],
			)
		),
	),
	'IM_CHAT' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_IM_BTN'),
	),
	'CALENDAR_EVENT' => array(
		'title' => Loc::getMessage('MAIL_MESSAGE_ACTIONS_EVENT_BTN'),
	),
);

foreach ($createMenu as $id => $item)
{
	$createMenu[$id]['id'] = $id;
	$createMenu[$id]['binded'] = (bool) preg_grep(sprintf('/%s-\d+/', preg_quote($id)), $message['BIND']);
}

$createMenu['__default'] = &$createMenu[\CUserOptions::getOption('mail', 'default_create_action', 'TASKS_TASK')];

?>

<div class="ui-btn-split ui-btn-primary">
	<a class="ui-btn-main" id="mail-msg-<?=intval($message['ID']) ?>-actions-create-btn"><?=$createMenu['__default']['title'] ?></a>
	<a class="ui-btn-extra" id="mail-msg-<?=intval($message['ID']) ?>-actions-create-menu-btn"></a>
</div>

<script type="text/javascript">

	BX.ready(function ()
	{
		BX.message({
			MAIL_MESSAGE_ACTIONS_NOTIFY_ADDED_TO_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ACTIONS_NOTIFY_ADDED_TO_CRM')) ?>',
			MAIL_MESSAGE_ACTIONS_NOTIFY_EXCLUDED_FROM_CRM: '<?=\CUtil::jsEscape(Loc::getMessage('MAIL_MESSAGE_ACTIONS_NOTIFY_EXCLUDED_FROM_CRM')) ?>'
		});

		BXMailMessageActions.init({
			messageId: <?=intval($message['ID']) ?>,
			createMenu: <?=\Bitrix\Main\Web\Json::encode($createMenu) ?>,
			isCrmEnabled: <?=\CUtil::phpToJsObject(!empty($arParams['CRM_AVAILABLE'])); ?>
		});
	});

</script>
