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
	private $workgroupId;

	public function __construct(array $params = [])
	{
		$this->workgroupId = (int)($params['workgroupId'] ?? 0);

		if ($this->workgroupId <= 0)
		{
			throw new ArgumentException('Wrong workgroupId value');
		}
	}

	public function getCounterValue(): array
	{
		$result = (new \Bitrix\Main\Entity\Query(UserToGroupTable::getEntity()))
			->addFilter('=GROUP_ID', $this->workgroupId)
			->addFilter('=ROLE', UserToGroupTable::ROLE_REQUEST)
			->addFilter('=INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_GROUP)
			->addSelect('ID')
			->countTotal(true)
			->exec()
			->getCount();

		return [
			'all' => $result,
		];
	}
}