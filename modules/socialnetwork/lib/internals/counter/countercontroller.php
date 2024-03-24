<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter;

use Bitrix\Socialnetwork\Internals\Counter\Exception\UnknownCounterException;

class CounterController
{
	public static function getValue(string $name = '', int $entityId = 0, int $userId = 0): array
	{
		$instance = self::getInstance($name, $entityId, $userId);

		return $instance->getCounterValue();
	}

	/**
	 * @param string $name
	 * @param integer $entityId
	 * @param integer $userId
	 * @return Provider\Base
	 * @throws UnknownCounterException
	 */
	public static function getInstance(string $name = '', int $entityId = 0, int $userId = 0): Provider\Base
	{
		switch ($name)
		{
			case CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN:
				$result = Provider\WorkgroupRequestsIn::getInstance($entityId);
				break;
			case CounterDictionary::COUNTER_WORKGROUP_REQUESTS_OUT:
				$result = Provider\WorkgroupRequestsOut::getInstance($entityId);
				break;
			case CounterDictionary::COUNTER_WORKGROUP_LIST_LIVEFEED:
				$result = new Provider\WorkgroupListLivefeed([
					'userId' => $userId,
				]);
				break;
			case CounterDictionary::COUNTER_WORKGROUP_LIST_TASKS:
				$result = new Provider\WorkgroupListTasks([
					'userId' => $userId,
				]);
				break;
			default:
				throw new UnknownCounterException();
		}

		return $result;
	}
}