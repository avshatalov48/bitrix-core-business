<?php

namespace Bitrix\Mail\Integration\Im;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Mail\Internals\MailboxAccessTable;

Loc::loadMessages(__FILE__);

class Notification
{
	const notifierSchemeTypeMail = 'new_message';

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

	public static function add($userId, $type, $fields)
	{
		if (Main\Loader::includeModule('im'))
		{
			if ('new_message' == $type)
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
		}
	}

}
