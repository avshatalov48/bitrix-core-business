<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\Loader::includeModule('mail');

$mailboxes = \Bitrix\Mail\MailboxTable::getUserMailboxes();
if (!empty($mailboxes))
{
	$previousSeenMailboxId = \CUserOptions::getOption('mail', 'previous_seen_mailbox_id', null);

	$mailbox = $previousSeenMailboxId > 0 && !empty($mailboxes[$previousSeenMailboxId])
		? $mailboxes[$previousSeenMailboxId]
		: reset($mailboxes);
}

$arResult['VARIABLES'] = array();

if (!empty($mailbox))
{
	$arResult['VARIABLES']['id'] = $mailbox['ID'];
	$componentName = 'bitrix:mail.client.message.list';
}
else
{
	global $USER;
	$userId = $USER->getId();
	$siteId = SITE_ID;
	\CUserCounter::set($userId, 'mail_unseen', 0, $siteId);

	$arResult['VARIABLES']['IS_MAIN_MAIL_PAGE'] = true;
	$componentName = 'bitrix:mail.client.config';
}

$APPLICATION->IncludeComponent(
	'bitrix:ui.sidepanel.wrapper',
	'',
	[
		'POPUP_COMPONENT_USE_BITRIX24_THEME' => 'Y',
		'DEFAULT_THEME_ID' => 'light:mail',
		'POPUP_COMPONENT_NAME' => $componentName,
		'POPUP_COMPONENT_TEMPLATE_NAME' => '',
		'POPUP_COMPONENT_PARAMS' => $arResult,
		'USE_UI_TOOLBAR' => 'Y',
		'USE_PADDING' => false,
		'PLAIN_VIEW' => false,
		'PAGE_MODE' => false,
		'PAGE_MODE_OFF_BACK_URL' => "/stream/",
	]
);