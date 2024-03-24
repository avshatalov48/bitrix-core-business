<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter;

use Bitrix\Socialnetwork\Internals\EventService\Event;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor\CommandTrait;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Processor\UserProcessor;
use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Socialnetwork\Space\Service;
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
		return Service::isAvailable(true);
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
				$this->seen($event);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_ADD:
				$this->add($event, [CounterDictionary::COUNTER_NEW_POSTS]);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_ADD:
				$this->add($event, [CounterDictionary::COUNTER_NEW_COMMENTS]);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_POST_DEL:
				$this->delete($event, [CounterDictionary::COUNTER_NEW_POSTS]);
				break;
			case EventDictionary::EVENT_SPACE_LIVEFEED_COMMENT_DEL:
				$this->delete($event, [CounterDictionary::COUNTER_NEW_COMMENTS]);
				break;
			default:
				$this->recount($event, [CounterDictionary::COUNTER_NEW_POSTS, CounterDictionary::COUNTER_NEW_COMMENTS]);
		}

		$this->updateLeftMenuCounter();
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
	public function updateLeftMenuCounter(): void
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
				false
			);
		}
	}

	private function recount(Event $event, array $counters = []): void
	{
		$sonetLogId = $event->getData()['SONET_LOG_ID'] ?? null;
		if (!$sonetLogId)
		{
			return;
		}

		$userProcessor = UserProcessor::getInstance($this->userId);
		foreach ($counters as $counter)
		{
			$userProcessor->recount($counter, [$sonetLogId]);
		}
	}

	private function add(Event $event, array $counters = []): void
	{
		$sonetLogId = $event->getData()['SONET_LOG_ID'] ?? null;
		if (!$sonetLogId)
		{
			return;
		}

		$userProcessor = UserProcessor::getInstance($this->userId);
		foreach ($counters as $counter)
		{
			$userProcessor->add($counter, [$sonetLogId]);
		}
	}

	private function delete(Event $event, array $counters = []): void
	{
		$sonetLogId = $event->getData()['SONET_LOG_ID'] ?? null;
		if (!$sonetLogId)
		{
			return;
		}

		$userProcessor = UserProcessor::getInstance($this->userId);
		foreach ($counters as $counter)
		{
			$userProcessor->seen($counter, [$sonetLogId]);
		}
	}

	private function seen(Event $event): void
	{
		$data = $event->getData();
		if (!isset($data['ENTITY_ID'], $data['ENTITY_TYPE_ID'], $data['USER_ID'], $data['SONET_LOG_ID']))
		{
			return;
		}

		$userProcessor = UserProcessor::getInstance($this->userId);
		$userProcessor->seen(CounterDictionary::COUNTER_NEW_POSTS, [$data['SONET_LOG_ID']]);
		$userProcessor->seen(CounterDictionary::COUNTER_NEW_COMMENTS, [$data['SONET_LOG_ID']]);
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

		// save the timestamp
		$viewParams = [
			'userId' => $this->userId,
			'typeId' => Provider::DATA_ENTITY_TYPE_LIVE_FEED_VIEW,
			'entityId' => $groupId,
			'logId' => $groupId,
			'save' => true,
		];
		UserContentViewTable::set($viewParams);
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