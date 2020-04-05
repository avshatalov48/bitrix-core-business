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
	$componentName = 'bitrix:mail.client.config';
}

$this->getComponent()->includePageComponent($componentName, '', $arResult);
