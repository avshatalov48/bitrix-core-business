<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed;

use Bitrix\Socialnetwork\Internals\EventService\Service;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterDictionary;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterController;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterState;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\State\Factory;

class Counter
{
	private const DEFAULT_LIMIT = 4999;
	private static array $instance = [];
	private int $userId;

	public static function isReady($userId): bool
	{
		return array_key_exists($userId, self::$instance);
	}

	public static function getGlobalLimit(): int
	{
		$limit = \COption::GetOptionString('socialnetwork', 'sonetCounterLimit', '');
		if ($limit === '')
		{
			return self::DEFAULT_LIMIT;
		}
		return (int)$limit;
	}

	public static function getInstance($userId): self
	{
		if (!array_key_exists($userId, self::$instance))
		{
			self::$instance[$userId] = new self($userId);
			(new CounterController($userId))->updateLeftMenuCounter();
		}

		return self::$instance[$userId];
	}

	private function __construct($userId)
	{
		$this->userId = (int)$userId;

		$state = $this->getState();

		if (
			$this->userId
			&& !$state->isCounted()
		)
		{
			(new CounterController($this->userId))->recountAll();
		}

		Service::getInstance();

		//$this->dropOldCounters();
	}

	public function isCounted(): bool
	{
		return $this->getState()->isCounted();
	}

	public function getRawCounters(string $meta = CounterDictionary::META_PROP_ALL): array
	{
		return $this->getState()->getRawCounters($meta);
	}

	public function getCounters(int $groupId = 0, array $params = []): array
	{
		$skipAccessCheck = (isset($params['SKIP_ACCESS_CHECK']) && $params['SKIP_ACCESS_CHECK']);

		if (!$skipAccessCheck && !$this->isAccessToCounters())
		{
			return [];
		}

		return [
			'total' => [
				'counter' => $this->get(CounterDictionary::COUNTER_TOTAL, $groupId),
				'code' => '',
			],
			'new_posts' => [
				'counter' => $this->get(CounterDictionary::COUNTER_NEW_POSTS, $groupId),
				'code' => '',
			],
			'new_comments' => [
				'counter' => $this->get(CounterDictionary::COUNTER_NEW_COMMENTS, $groupId),
				'code' => '',
			],
		];
	}

	public function get($name, int $groupId = 0)
	{
		switch ($name)
		{
			case CounterDictionary::COUNTER_TOTAL:
				$value = $this->get(CounterDictionary::COUNTER_NEW_POSTS, $groupId)
					+ $this->get(CounterDictionary::COUNTER_NEW_COMMENTS, $groupId);
				break;
			default:
				$value = $this->getState()->getValue($name, $groupId);
				break;
		}

		return $value;
	}

	private function dropOldCounters(): void
	{
		$state = $this->getState();

		if (!$state->isCounted())
		{
			return;
		}

		if (Counter\Queue\Queue::isInQueue($this->userId))
		{
			return;
		}

		if ($state->getClearedDate() >= (int) date('ymd'))
		{
			return;
		}

		Service::addEvent(
			\Bitrix\Socialnetwork\Internals\EventService\EventDictionary::EVENT_GARBAGE_COLLECT,
			[
				'USER_ID' => $this->userId,
			]
		);
	}

	private function isAccessToCounters(): bool
	{
		return $this->userId === \Bitrix\Socialnetwork\Helper\User::getCurrentUserId();
	}

	/**
	 * @return CounterState
	 */
	private function getState(): CounterState
	{
		return Factory::getState($this->userId);
	}
}