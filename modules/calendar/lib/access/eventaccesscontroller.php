<?php

namespace Bitrix\Calendar\Access;

use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\UserModel;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\BaseAccessController;
use Bitrix\Main\Access\User\AccessibleUser;
use Bitrix\Calendar\Core\Mappers\Event;

class EventAccessController extends BaseAccessController
{
	public static array $cache = [];

	private const ITEM_TYPE = 'EVENT';
	private const USER_TYPE = 'USER';

	protected function loadItem(int $itemId = null): ?AccessibleItem
	{
		$key = self::ITEM_TYPE . '_' . $itemId;
		if (!array_key_exists($key, static::$cache))
		{
			/**@var  \Bitrix\Calendar\Core\Event\Event $event */
			$event = (new Event())->getById($itemId);

			if ($event instanceof \Bitrix\Calendar\Core\Event\Event)
			{
				$eventModel = EventModel::createFromObject($event);
			}
			else
			{
				$eventModel = EventModel::createNew();
			}

			static::$cache[$key] = $eventModel;
		}

		return static::$cache[$key];
	}

	protected function loadUser(int $userId): AccessibleUser
	{
		$key = self::USER_TYPE . '_' . $userId;
		if (!array_key_exists($key, static::$cache))
		{
			static::$cache[$key] = UserModel::createFromId($userId);
		}

		return static::$cache[$key];
	}
}