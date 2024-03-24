<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\UserAccessTable;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Item\LogRight;

class SonetRightsRecepient implements Collector
{
	private int $sonetLogId;
	private ?array $logRights = null;

	public function __construct(int $sonetLogId, ?array $logRights)
	{
		$this->sonetLogId = $sonetLogId;
		$this->logRights = $logRights;
	}

	public function fetch(int $limit, int $offset): RecepientCollection
	{
		$accessCodes = (is_array($this->logRights) && !empty($this->logRights))
			? $this->logRights
			: LogRight::get($this->sonetLogId)
		;

		$recipients = [];

		$query = in_array('AU', $accessCodes, true)
			? $this->getAllAuthorisedQuery($limit, $offset)
			: $this->getAllByAccessCodeQuery($limit, $offset, $accessCodes)
		;

		foreach ($query->fetchAll() as $user)
		{
			$userId = $user['ID'] ?? 0;
			$isOnline = ($user['IS_ONLINE'] ?? 'Y') === 'Y';
			$recipients[] = new Recepient((int)$userId, $isOnline);
		}

		return new RecepientCollection(...$recipients);
	}

	private function getAllAuthorisedQuery(int $limit, int $offset): \Bitrix\Main\ORM\Query\Query
	{
		return UserTable::query()
			->setDistinct()
			->setSelect(['ID', 'ACTIVE', 'IS_REAL_USER', 'UF_DEPARTMENT', 'IS_ONLINE'])
			->where('ACTIVE', '=', 'Y')
			->where('IS_REAL_USER', '=', 'Y')
			->where('UF_DEPARTMENT', '!=', false)
			->setLimit($limit)
			->setOffset($offset)
		;
	}

	private function getAllByAccessCodeQuery(int $limit, int $offset, array $accessCodes): \Bitrix\Main\ORM\Query\Query
	{
		$subQuery = (new Query(UserAccessTable::getEntity()));
		$subQuery->setSelect(['USER_ID']);
		$subQuery->whereIn('ACCESS_CODE', $accessCodes);

		return UserTable::query()
			->setDistinct()
			->setSelect([
				'ID',
				'ACTIVE',
				'IS_REAL_USER',
				'UF_DEPARTMENT',
				'IS_ONLINE'
			])
			->where('ACTIVE', '=', 'Y')
			->where('IS_REAL_USER', '=', 'Y')
			->where('UF_DEPARTMENT', '!=', false)
			->whereIn('ID', new SqlExpression($subQuery->getQuery()))
			->setLimit($limit)
			->setOffset($offset)
		;
	}
}