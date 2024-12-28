<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Internals\Counter\Provider\GroupInvite;
use Bitrix\Calendar\Internals\Counter\Provider\OpenEvent;
use Bitrix\Calendar\Internals\Counter\State\Loader;
use Bitrix\Calendar\Internals\Counter\State\State;
use Bitrix\Main;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\Provider\Invite;
use Bitrix\Calendar\Internals\Counter\Provider\Sync;

/**
 * Class Counter
 *
 * @package Bitrix\Calendar\Internals
 */
class Counter
{
	private static $instance = [];
	private int $userId;
	private State $state;

	/**
	 * @param $userId
	 * @return static
	 * @throws Main\ArgumentException
	 * @throws Main\DB\SqlQueryException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getInstance($userId): self
	{
		if (!array_key_exists($userId, self::$instance))
		{
			self::$instance[$userId] = new self($userId);
		}

		return self::$instance[$userId];
	}

	/**
	 * Counter constructor.
	 *
	 * @param $userId
	 * @param int $groupId
	 * @throws Main\ArgumentException
	 * @throws Main\DB\SqlQueryException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function __construct($userId)
	{
		$this->userId = (int)$userId;
		$this->state = new State($this->userId, new Loader($this->userId));
	}

	/**
	 * @param string $name
	 * @param int $entityId
	 * @return int
	 */
	public function get(string $name, int $entityId = 0): int
	{
		return match ($name) {
			CounterDictionary::COUNTER_TOTAL => $this->get(CounterDictionary::COUNTER_INVITES, $entityId)
				+ $this->get(CounterDictionary::COUNTER_SYNC_ERRORS, $entityId)
				+ $this->get(CounterDictionary::COUNTER_OPEN_EVENTS),
			CounterDictionary::COUNTER_MY => $this->get(CounterDictionary::COUNTER_INVITES, $entityId)
				+ $this->get(CounterDictionary::COUNTER_SYNC_ERRORS, $entityId),
			CounterDictionary::COUNTER_INVITES => (new Invite($this->userId, $entityId))->getValue(),
			CounterDictionary::COUNTER_SYNC_ERRORS => (new Sync($this->userId, $entityId))->getValue(),
			CounterDictionary::COUNTER_OPEN_EVENTS => (new OpenEvent($this->state, $entityId))->getValue(),
			CounterDictionary::COUNTER_NEW_EVENT => $this->state->get(CounterDictionary::META_PROP_NEW_EVENTS)[$entityId] ?? 0,
			CounterDictionary::COUNTER_GROUP_INVITES => (new GroupInvite($this->userId, $entityId))->getValue(),
			default => 0,
		};
	}
}
