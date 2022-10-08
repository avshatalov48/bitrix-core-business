<?php

namespace Bitrix\UI\NotificationManager;

use Bitrix\Main\Loader;
use Bitrix\Pull\Event;

class Notifier
{
	/**
	 * Sends a notification to the user
	 *
	 * @param int $userId
	 * @param Notification $notification
	 *
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function notify(int $userId, Notification $notification): bool
	{
		if (!Loader::includeModule('pull'))
		{
			return false;
		}

		return Event::add($userId, [
			'module_id' => 'ui',
			'command' => 'notify',
			'params' => [
				'notification' => $notification,
			],
		]);
	}
}