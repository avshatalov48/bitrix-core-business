<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter;

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor\CommandTrait;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor\UserProcessor;
use Bitrix\Socialnetwork\UserContentViewTable;

class CounterController
{
	use CommandTrait;

	public const STEP_LIMIT = 1000;
	private int $userId;

	/**
	 * CounterBroker constructor.
	 * @param int $userId
	 */
	public function __construct(int $userId = 0)
	{
		$this->userId = $userId;
	}

	/**
	 * Checks if this counter is enabled
	 * @return bool
	 */
	public static function isEnabled(int $userId = 0): bool
	{
		$isLegacyEnabled = Option::get('socialnetwork', CounterDictionary::LEGACY_COUNTER_ENABLED, 'null', '-');
		if ($isLegacyEnabled === 'null')
		{
			// new counters enabled for all users
			return true;
		}

		if (
			$userId
			&& \CUserOptions::GetOption(
				'socialnetwork',
				CounterDictionary::COUNTER_ENABLED_FOR_USER,
				false,
				$userId
			)
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * Processes event based on a given event type
	 * @param Event $event
	 * @return void
	 */
	public function process(Event $event): void
	{
		if ($event->getType() === EventDictionary::EVENT_SPACE_LIVEFEED_COUNTER_UPD)
		{
			return;
		}

		if (!in_array($event->getType(), CounterDictionary::SUPPORTED_EVENTS, true))
		{
			return;
		}

		switch ($event->getType())
		{
			case EventDictionary::EVENT_SPACE_LIVEFEED_READ_ALL:
				$this->readAll($event);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_VIEW:
				$this->viewed($event);
				break;
			default:
				$this->recount($event);
		}
	}

	/**
	 * Processes item from b_sonet_scorer_queue
	 * @param array $queueItem
	 * @return void
	 * @throws Exception\UnknownCounterException
	 */
	public function processQueueItem(array $queueItem): void
	{
		$type = $queueItem['TYPE'] ?? null;
		$items = $queueItem['SONET_LOGS'] ?? null;
		if (!$type || !is_array($items))
		{
			return;
		}

		switch ($type)
		{
			case CounterDictionary::COUNTER_NEW_POSTS:
			case CounterDictionary::COUNTER_NEW_COMMENTS:
				$userProcessor = UserProcessor::getInstance($this->userId);
				$userProcessor->recount($type, $items);
				break;
		}
	}

	/**
	 * Updates the left menu counter
	 * @return void
	 */
	public function updateInOptionCounter()
	{
		$value = Counter::getInstance($this->userId)->get(CounterDictionary::COUNTER_TOTAL);
		if (!$this->isSameValueCached($value))
		{
			\CUserCounter::Set(
				$this->userId,
				CounterDictionary::LEFT_MENU_SONET,
				$value,
				'**',
				'',
				true
			);
		}
	}

	private function recount(Event $event): void
	{
		$sonetLogId = $event->getData()['SONET_LOG_ID'] ?? null;
		if (!$sonetLogId)
		{
			return;
		}

		$userProcessor = UserProcessor::getInstance($this->userId);
		$userProcessor->recount(CounterDictionary::COUNTER_NEW_POSTS, [$sonetLogId]);
		$userProcessor->recount(CounterDictionary::COUNTER_NEW_COMMENTS, [$sonetLogId]);
	}

	private function viewed(Event $event): void
	{
		$data = $event->getData();
		if (!isset($data['ENTITY_ID'], $data['TYPE_ID'], $data['USER_ID']))
		{
			return;
		}

		$contentViewed = UserContentViewTable::query()
			->where('USER_ID', $data['USER_ID'])
			->where('RATING_TYPE_ID', $data['TYPE_ID'])
			->where('RATING_ENTITY_ID', $data['ENTITY_ID'])
			->setLimit(1)
			->exec()->fetch();

		if ($contentViewed)
		{
			$this->recount($event);
		}
	}

	public function recountAll(): void
	{
		if (!$this->userId)
		{
			return;
		}

		self::reset($this->userId);

		$userProcessor = UserProcessor::getInstance($this->userId);
		$userProcessor->recountAll(CounterDictionary::COUNTER_NEW_POSTS);

		$this->saveFlag($this->userId);
	}

	private function readAll(Event $event): void
	{
		if (!$this->userId)
		{
			return;
		}

		$groupId = (int)($event->getData()['GROUP_ID'] ?? 0);
		UserProcessor::getInstance($this->userId)->readAll($groupId);
	}

	private function isSameValueCached(int $value): bool
	{
		global $CACHE_MANAGER;

		$cache = $CACHE_MANAGER->Get('user_counter' . $this->userId);
		if (!$cache)
		{
			return false;
		}

		foreach ($cache as $item)
		{
			if (
				$item['CODE'] === CounterDictionary::LEFT_MENU_SONET
				&& $item['SITE_ID'] === '**'
				&& (int)$item['CNT'] === $value
			)
			{
				return true;
			}
		}

		return false;
	}
}