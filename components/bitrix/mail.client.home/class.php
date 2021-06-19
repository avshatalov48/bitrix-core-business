<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CMailClientHomeComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$APPLICATION->setTitle(Loc::getMessage('MAIL_CLIENT_HOME_TITLE'));

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');
			return;
		}

		\Bitrix\Main\Loader::includeModule('mail');

		$mailboxes = \Bitrix\Mail\MailboxTable::getUserMailboxes();
		if (!empty($mailboxes))
		{
			$previousSeenMailboxId = \CUserOptions::getOption('mail', 'previous_seen_mailbox_id', null);

			$mailbox = $previousSeenMailboxId > 0 && !empty($mailboxes[$previousSeenMailboxId])
				? $mailboxes[$previousSeenMailboxId]
				: reset($mailboxes);
		}

		if (!empty($mailbox))
		{
			$redirect = \CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_MAIL_MSG_LIST'],
				array('id' => $mailbox['ID'],
					  'start_sync_with_showing_stepper' =>false,
					)
			);
		}
		else
		{
			$redirect = \CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_MAIL_CONFIG'],
				array('act' => '')
			);
		}

		localRedirect(
			\CHTTP::urlAddParams(
				$redirect,
				array_filter(array(
					'IFRAME' => isset($_REQUEST['IFRAME']) ? $_REQUEST['IFRAME'] : null,
					'IFRAME_TYPE' => isset($_REQUEST['IFRAME_TYPE']) ? $_REQUEST['IFRAME_TYPE'] : null,
				))
			),
			true
		);

		$this->includeComponentTemplate();
	}

}
