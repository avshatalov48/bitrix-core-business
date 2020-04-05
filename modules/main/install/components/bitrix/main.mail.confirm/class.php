<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class MainMailConfirmComponent extends CBitrixComponent
{

	public function executeComponent()
	{
		global $USER;

		if (!is_object($USER) || !$USER->isAuthorized())
			return array();

		if (!empty($this->arParams['CONFIRM_CODE']))
		{
			$this->includeComponentTemplate('confirm_code');
			return;
		}

		$this->arParams['USER_FULL_NAME'] = static::getUserNameFormated();
		$this->arParams['MAILBOXES'] = static::prepareMailboxes();

		$this->arParams['IS_SMTP_AVAILABLE'] = Main\ModuleManager::isModuleInstalled('bitrix24');
		$this->arParams['IS_ADMIN'] = Main\Loader::includeModule('bitrix24')
			? \CBitrix24::isPortalAdmin($USER->getId())
			: $USER->isAdmin();

		$this->includeComponentTemplate();

		return $this->arParams['MAILBOXES'];
	}

	public static function prepareMailboxes()
	{
		return Main\Mail\Sender::prepareUserMailboxes();
	}

	public static function prepareMailboxesFormated()
	{
		static $mailboxesFormated;

		if (!is_null($mailboxesFormated))
			return $mailboxesFormated;

		$mailboxesFormated = array();

		foreach (static::prepareMailboxes() as $item)
			$mailboxesFormated[] = $item['formated'];

		return $mailboxesFormated;
	}

	protected static function getUserNameFormated()
	{
		global $USER;

		static $userNameFormated;

		if (!is_null($userNameFormated))
			return $userNameFormated;

		$userNameFormated = '';

		if (!is_object($USER) || !$USER->isAuthorized())
			return $userNameFormated;

		$userNameFormated = \CUser::formatName(
			\CSite::getNameFormat(),
			\Bitrix\Main\UserTable::getList(array(
				'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO'),
				'filter' => array('=ID' => $USER->getId()),
			))->fetch(),
			true, false
		);

		return $userNameFormated;
	}

	protected static function extractEmail($email)
	{
		$email = trim($email);
		if (preg_match('/.*?[<\[\(](.+?)[>\]\)].*/i', $email, $matches))
			$email = $matches[1];

		return $email;
	}

}
