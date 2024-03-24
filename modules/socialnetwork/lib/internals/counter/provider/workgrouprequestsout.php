<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Provider;

use Bitrix\Main\ArgumentException;
use Bitrix\Socialnetwork\UserToGroupTable;

class WorkgroupRequestsOut implements Base
{
	private int $groupId;
	private int $counterValue;

	private static array $instance = [];

	public static function getInstance($groupId): self
	{
		if (!array_key_exists($groupId, self::$instance))
		{
			self::$instance[$groupId] = new self($groupId);
		}

		return self::$instance[$groupId];
	}

	private function __construct(int $groupId)
	{
		if ($groupId <= 0)
		{
			throw new ArgumentException('Wrong workgroupId value');
		}

		$this->groupId = $groupId;
		$this->fillCounterValue();
	}

	public function getCounterValue(): array
	{
		return [
			'all' => $this->counterValue,
		];
	}

	private function fillCounterValue(): void
	{
		$this->counterValue = (new \Bitrix\Main\Entity\Query(UserToGroupTable::getEntity()))
			->addFilter('=GROUP_ID', $this->groupId)
			->addFilter('=ROLE', UserToGroupTable::ROLE_REQUEST)
			->addFilter('=INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_GROUP)
			->addSelect('ID')
			->countTotal(true)
			->exec()
			->getCount();
	}
}