<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Provider;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Internals\Counter;

class WorkgroupListTasks implements Base
{
	private int $userId;

	public function __construct(array $params = [])
	{
		$this->userId = (int)($params['userId'] ?? 0);

		if ($this->userId <= 0)
		{
			throw new ArgumentException('Wrong userId value');
		}
	}

	public function getCounterValue(): array
	{
		if (!Loader::includeModule('tasks'))
		{
			return [];
		}

		$counter = Counter::getInstance($this->userId);

		return [
			Counter\CounterDictionary::COUNTER_PROJECTS_MAJOR => (
				$counter->get(Counter\CounterDictionary::COUNTER_GROUPS_TOTAL_COMMENTS)
				+ $counter->get(Counter\CounterDictionary::COUNTER_PROJECTS_TOTAL_COMMENTS)
				+ $counter->get(Counter\CounterDictionary::COUNTER_GROUPS_TOTAL_EXPIRED)
				+ $counter->get(Counter\CounterDictionary::COUNTER_PROJECTS_TOTAL_EXPIRED)
			),
			Counter\CounterDictionary::COUNTER_SCRUM_TOTAL_COMMENTS => $counter->get(Counter\CounterDictionary::COUNTER_SCRUM_TOTAL_COMMENTS),
		];
	}
}