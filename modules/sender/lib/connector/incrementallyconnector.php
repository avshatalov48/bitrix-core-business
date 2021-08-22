<?php

namespace Bitrix\Sender\Connector;

use Bitrix\Main\Entity\Query;

/**
 *
 * Interface IncrementallyConnector
 * @package Bitrix\Sender\Connector
 */
interface IncrementallyConnector
{
	/**
	 * @param int $offset
	 * @param int $limit
	 * @param string|null $excludeType
	 *
	 * @return Query[]
	 */
	public function getLimitedQueries(int $offset, int $limit, string $excludeType = null): array;

	/**
	 * @return array
	 */
	public function getEntityLimitInfo(): array;

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return \Bitrix\Main\DB\Result
	 */
	public function getLimitedData(int $offset, int $limit): ?\Bitrix\Main\DB\Result;
}