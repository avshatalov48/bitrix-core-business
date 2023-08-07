<?php

namespace Bitrix\Mail\Integration\Im;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Mail\Internals\MailboxAccessTable;
use \Bitrix\Mail\MailboxTable;

Loc::loadMessages(__FILE__);

class Notification
{
	const notifierSchemeTypeMail = 'new_message';
	const notifierSchemeTypeMailTariffRestrictions = 'tariff_restrictions';

	public static function getSchema()
	{
		Main\Loader::includeModule('im');

		return [
			'mail' => [
				'new_message' => [
					'NAME' => Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE'),
					'SITE' => 'Y',
					'SYSTEM' => 'Y',
					'MAIL' => 'N',
					'PUSH' => 'N',
					'DISABLED' => [
						IM_NOTIFY_FEATURE_PUSH,
						IM_NOTIFY_FEATURE_MAIL,
					],
				],
				'imposed_tariff_restrictions_on_the_mailbox' => [
					'NAME' => Loc::getMessage('MAIL_NOTIFY_IMPOSE_TARIFF_RESTRICTIONS_ON_THE_MAILBOX'),
					'SITE' => 'Y',
					'SYSTEM' => 'Y',
					'MAIL' => 'Y',
					'PUSH' => 'Y',
					'DISABLED' => [
						IM_NOTIFY_FEATURE_PUSH,
						IM_NOTIFY_FEATURE_MAIL,
						IM_NOTIFY_FEATURE_SITE,
					],
				],
			],
		];
	}

	private static function getNotifyMessageForNewMessageSetInMail($mailboxId, $messageCount, $absoluteUrl = false): string
	{
		$url = htmlspecialcharsbx(sprintf("/mail/list/%u", $mailboxId));

		if ($absoluteUrl)
		{
			$uri = new Uri($url);
			$url = $uri->toAbsolute()->getLocator();
		}

		return Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE_MULTI_1',
			[
				'#COUNT#' => $messageCount,
				'#VIEW_URL#' => $url,
			]
		);
	}

	private static function getNotifyMessageForNewMessageInMail($message, $absoluteUrl = false): string
	{
		$url = htmlspecialcharsbx($message['__href']);

		if ($absoluteUrl)
		{
			$uri = new Uri($url);
			$url = $uri->toAbsolute()->getLocator();
		}

		if ($message['SUBJECT'])
		{
			return Loc::getMessage('MAIL_NOTIFY_NEW_SINGLE_MESSAGE_IN_MAIL_CLIENT_1',
				[
					'#SUBJECT#' => $message['SUBJECT'],
					'#VIEW_URL#' => $url,
				]
			);
		}
		else
		{
			return Loc::getMessage('MAIL_NOTIFY_NEW_SINGLE_MESSAGE_IN_MAIL_CLIENT_EMPTY_SUBJECT',
				[
					'#VIEW_URL#' => $url,
				]
			);
		}
	}

	private static function getNotifyMessageForTariffRestrictionsMailbox($mailboxId, $email, $forEmailNotification = false): string
	{
		$url = htmlspecialcharsbx(sprintf("/mail/list/%u", $mailboxId));

		if ($forEmailNotification)
		{
			$uri = new Uri($url);
			$url = $uri->toAbsolute()->getLocator();

			$text = Loc::getMessage('MAIL_NOTIFY_FULL_MAILBOX_TARIFF_RESTRICTIONS_HAVE_BEEN_IMPOSED',
				[
					'#EMAIL#' => $email,
					'#VIEW_URL#' => $url,
				]
			);
		}
		else
		{
			$text = Loc::getMessage('MAIL_NOTIFY_MAILBOX_TARIFF_RESTRICTIONS_HAVE_BEEN_IMPOSED',
				[
					'#EMAIL#' => $email,
					'#VIEW_URL#' => $url,
				]
			);
		}

		return $text;
	}

	private static function notifyForNewMessagesInMail($userId, $fields): void
	{
		$message = $fields['message'];

		\CIMNotify::add([
			'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
			'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
			'NOTIFY_MODULE' => 'mail',
			'NOTIFY_EVENT' => self::notifierSchemeTypeMail,
			'NOTIFY_TITLE' => Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE_TITLE'),
			'NOTIFY_MESSAGE_OUT' => empty($message)
				? self::getNotifyMessageForNewMessageSetInMail($fields['mailboxId'], $fields['count'], true)
				: self::getNotifyMessageForNewMessageInMail($message, true),
			'NOTIFY_MESSAGE' => empty($message)
				? self::getNotifyMessageForNewMessageSetInMail($fields['mailboxId'], $fields['count'])
				: self::getNotifyMessageForNewMessageInMail($message),
			'TO_USER_ID' => $userId,
		]);
	}

	private static function notifyForTariffRestrictions($mailboxId): void
	{
		$mailbox = MailboxTable::getList([
			'select' => [
				'USER_ID',
				'EMAIL'
			],
			'filter' => [
				'=ID' => $mailboxId,
			],
			'limit' => 1,
		])->fetch();

		if (isset($mailbox['USER_ID']) && isset($mailbox['EMAIL']))
		{
			\CIMNotify::add([
				'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				'NOTIFY_MODULE' => 'mail',
				'NOTIFY_EVENT' => self::notifierSchemeTypeMailTariffRestrictions,
				'NOTIFY_TITLE' => Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE_TITLE'),
				'NOTIFY_MESSAGE_OUT' => self::getNotifyMessageForTariffRestrictionsMailbox($mailboxId, $mailbox['EMAIL'], true),
				'NOTIFY_MESSAGE' => self::getNotifyMessageForTariffRestrictionsMailbox($mailboxId, $mailbox['EMAIL']),
				'TO_USER_ID' => $mailbox['USER_ID'],
			]);
		}
	}

	public static function add($userId, $type, $fields, $mailboxId = null)
	{
		if (Main\Loader::includeModule('im'))
		{
			if ($type == 'new_message')
			{
				$mailboxId = $fields['mailboxId'];

				$userIds = [];

				$mailboxOwnerId = (int)$fields['mailboxOwnerId'] ?? 0;

				if ($mailboxOwnerId)
				{
					$userIds = MailboxAccessTable::getUserIdsWithAccessToTheMailbox($mailboxId);
				}
				else
				{
					$userIds[] = $userId;
				}

				foreach ($userIds as $id)
				{
					self::notifyForNewMessagesInMail($id, $fields);
				}
			}
			else if ($type == 'imposed_tariff_restrictions_on_the_mailbox')
			{
				self::notifyForTariffRestrictions($mailboxId);
			}
		}
	}

}
