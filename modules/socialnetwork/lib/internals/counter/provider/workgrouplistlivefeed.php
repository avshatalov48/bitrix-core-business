<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Provider;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\UserCounterTable;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;

class WorkgroupListLivefeed implements Base
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
		return [
			'all' => $this->getValue(),
		];
	}

	public function getValue(): int
	{
		//todo oh
		return 0;
	}
}