<?php

namespace Bitrix\Mail\Integration\Im;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Notification
{

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
				]
			]
		];
	}

	public static function add($rcpt, $type, $data)
	{
		if (Main\Loader::includeModule('im'))
		{
			if ('new_message' == $type)
			{
				$message = $data['message'];

				\CIMNotify::add([
					'MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
					'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
					'NOTIFY_MODULE' => 'mail',
					'NOTIFY_EVENT' => 'new_message',
					'NOTIFY_TITLE' => Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE_TITLE'),
					'NOTIFY_MESSAGE' => empty($message)
						? sprintf(
							'%s <a href="/mail/list/%u">%s</a>',
							Loc::getMessage('MAIL_NOTIFY_NEW_MESSAGE_TEXT_MULTI', ['#COUNT#' => $data['count']]),
							$data['mailboxId'],
							Loc::getMessage('MAIL_NOTIFY_GO_TO_LIST')
						)
						: Loc::getMessage(
							'MAIL_NOTIFY_NEW_MESSAGE_TEXT_SINGLE',
							array(
								'#BRIEF#' => sprintf(
									'<a href="%s">"%s"</a>',
									$message['__href'],
									$message['SUBJECT'] ?: mb_substr($message['BODY'], 0, 50) . '...'
								),
							)
						),
					'TO_USER_ID' => $rcpt,
				]);
			}
		}
	}

}
