<?php

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

		$this->includeComponentTemplate();

		return $this->arParams['MAILBOXES'];
	}

	public static function prepareMailboxes()
	{
		global $USER;

		static $mailboxes;

		if (!is_null($mailboxes))
			return $mailboxes;

		$mailboxes = array();

		if (!is_object($USER) || !$USER->isAuthorized())
			return $mailboxes;

		if (\CModule::includeModule('mail'))
		{
			$res = \Bitrix\Mail\MailboxTable::getList(array(
				'select' => array('NAME', 'LOGIN', 'USER_ID', 'OPTIONS'),
				'filter' => array(
					'=LID'    => SITE_ID,
					'=ACTIVE' => 'Y',
					array(
						'LOGIC' => 'OR',
						'=USER_ID' => $USER->getId(),
						array(
							'USER_ID'      => 0,
							'=SERVER_TYPE' => 'imap',
						),
					),
				),
				'order' => array(
					'USER_ID' => 'DESC',
					'ID' => 'ASC',
				),
			));

			while ($mailbox = $res->fetch())
			{
				$isUserMailbox = $mailbox['USER_ID'] > 0;
				$isCrmTracker  = !empty($mailbox['OPTIONS']['flags']) && in_array('crm_connect', (array) $mailbox['OPTIONS']['flags']);

				if (!$isUserMailbox && !$isCrmTracker)
					continue;

				$mailboxEmail = null;
				if (check_email($mailbox['NAME'], true))
					$mailboxEmail = strtolower($mailbox['NAME']);
				else if(check_email($mailbox['LOGIN'], true))
					$mailboxEmail = strtolower($mailbox['LOGIN']);

				if (!empty($mailboxEmail))
				{
					$mailboxName = !$isUserMailbox && trim($mailbox['OPTIONS']['name'])
						? trim($mailbox['OPTIONS']['name'])
						: static::getUserNameFormated();

					$key = hash('crc32b', strtolower($mailboxName).$mailboxEmail);
					$mailboxes[$key] = array(
						'name'  => $mailboxName,
						'email' => $mailboxEmail,
					);
				}
			}
		}

		// @TODO: query
		$crmEmail = static::extractEmail(\COption::getOptionString('crm', 'mail', ''));
		if (check_email($crmEmail, true))
		{
			$crmEmail = strtolower($crmEmail);
			$mailboxes[hash('crc32b', $crmEmail)] = array(
				'name'  => static::getUserNameFormated(),
				'email' => $crmEmail,
			);
		}

		$userEmail = $USER->getEmail();
		if (check_email($userEmail, true))
		{
			$userEmail = strtolower($userEmail);
			$mailboxes[hash('crc32b', $userEmail)] = array(
				'name'  => static::getUserNameFormated(),
				'email' => $userEmail,
			);
		}

		$confirmed = (array) \CUserOptions::getOption('mail', 'confirmed_from_emails', null)
			+ (array) \CUserOptions::getOption('mail', 'confirmed_from_emails', null, 0);
		foreach ($confirmed as $item)
		{
			if (!is_array($item))
				continue;

			if (check_email($item['email'], true))
			{
				$item['name']  = trim($item['name']);
				$item['email'] = strtolower($item['email']);
				$key = hash('crc32b', strtolower($item['name']).$item['email']);
				$mailboxes[$key] = array(
					'name'  => $item['name'] ?: static::getUserNameFormated(),
					'email' => $item['email'],
				);
			}
		}

		$mailboxes = array_values($mailboxes);

		foreach ($mailboxes as $k => $item)
		{
			$mailboxes[$k]['formated'] = sprintf(
				$item['name'] ? '%s <%s>' : '%s%s',
				$item['name'], $item['email']
			);
		}

		return $mailboxes;
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
