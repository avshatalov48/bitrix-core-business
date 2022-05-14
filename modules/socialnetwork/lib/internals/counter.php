<?php

namespace Bitrix\Socialnetwork\Internals;

use Bitrix\Main;
use Bitrix\Socialnetwork\Internals\Counter\CounterController;

/**
 * Class Counter
 *
 * @package Bitrix\Socialnetwork\Internals
 */
class Counter
{
	private static $instance = [];

	private $userId;

	/**
	 * @param $userId
	 * @return bool
	 */
	public static function isReady($userId): bool
	{
		return array_key_exists($userId, self::$instance);
	}

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
	}

	/**
	 * @param $name
	 * @return bool|int|mixed
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function get($name, int $entityId = 0)
	{
		return CounterController::getValue($name, $entityId, $this->userId);
	}
}
