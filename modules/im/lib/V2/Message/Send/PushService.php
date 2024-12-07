<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Im\V2\Message\PushFormat;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Common\ContextCustomer;

abstract class PushService
{
	use ContextCustomer;

	protected SendingConfig $sendingConfig;
	protected PushFormat $pushFormatter;
	protected Message\Send\Push\MobilePush $mobilePush;
	protected Message $message;

	/**
	 * @param SendingConfig|null $sendingConfig
	 */
	public function __construct(Message $message, ?SendingConfig $sendingConfig = null)
	{
		if ($sendingConfig === null)
		{
			$sendingConfig = new SendingConfig();
		}
		$this->sendingConfig = $sendingConfig;
		$this->message = $message;
		$this->pushFormatter = new PushFormat($message);
		$this->mobilePush = new Message\Send\Push\MobilePush($message, $sendingConfig);
	}

	public function isPullEnable(): bool
	{
		static $enable;
		if ($enable === null)
		{
			$enable = \Bitrix\Main\Loader::includeModule('pull');
		}
		return $enable;
	}


	//region Push Private chat

	abstract public function sendPush(array $counters = []): void;

	//endregion


	//region Push in Group Chat

	public static function getEventGroups(array $event, array $userIds, int $chatId): array
	{
		$counters = (new Message\CounterService())->getByChatForEachUsers($chatId, $userIds, 100);

		return self::getEventGroupsByCounters($event, $counters);
	}

	public static function getEventGroupsByCounters(array $event, array $counters): array
	{
		$events = [];

		foreach ($counters as $userId => $counter)
		{
			$eventForUser = $event;
			$eventForUser['groupId'] = 'im_chat_' . $event['params']['chatId'] . $event['params']['message']['id'] . $counter;
			$eventForUser['params']['counter'] = $counter;
			$events[$userId] = $eventForUser;
		}

		return self::getEventByCounterGroup($events, 100);
	}

	/**
	 * @param array $events
	 * @param int $maxUserInGroup
	 * @return array
	 */
	public static function getEventByCounterGroup(array $events, int $maxUserInGroup = 100): array
	{
		$groups = [];
		foreach ($events as $userId => $event)
		{
			$eventCode = $event['groupId'];
			if (!isset($groups[$eventCode]))
			{
				$groups[$eventCode]['event'] = $event;
			}
			$groups[$eventCode]['users'][] = $userId;
			$groups[$eventCode]['count'] = count($groups[$eventCode]['users']);
		}

		\Bitrix\Main\Type\Collection::sortByColumn($groups, ['count' => \SORT_DESC]);

		$count = 0;
		$finalGroup = [];
		foreach ($groups as $eventCode => $event)
		{
			if ($count >= $maxUserInGroup)
			{
				if (isset($finalGroup['other']))
				{
					$finalGroup['other']['users'] = array_unique(array_merge($finalGroup['other']['users'], $event['users']));
				}
				else
				{
					$finalGroup['other'] = $event;
					$finalGroup['other']['event']['params']['counter'] = 100;
				}
			}
			else
			{
				$finalGroup[$eventCode] = $event;
			}
			$count++;
		}

		\Bitrix\Main\Type\Collection::sortByColumn($finalGroup, ['count' => \SORT_ASC]);

		return $finalGroup;
	}


	//endregion


	//region Notification Push


	//endregion
}