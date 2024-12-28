<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Message\CounterService;

use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\V2\Message\Counter\CounterOverflowService;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\SystemException;
use CAgent;

final class CounterServiceAgent
{
	private const UNREAD_DELETE_ALL_LIMIT = 100000;
	private const UNREAD_DELETE_ALL_INTERVAL = 10;

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private static function getLastUnreadId(int $userId, bool $withNotify): int
	{
		$result = MessageUnreadTable::getList([
			'select' => ['ID'],
			'filter' => self::buildDeleteAllFilter($userId, $withNotify),
			'order' => ['ID' => 'DESC'],
			'limit' => 1,
		]);

		if (is_array($row = $result->fetch()))
		{
			return (int)$row['ID'];
		}

		return 0;
	}

	private static function buildDeleteAllFilter(int $userId, bool $withNotify): array
	{
		$filter = ['=USER_ID' => $userId];

		if (!$withNotify)
		{
			$filter['!=CHAT_TYPE'] = \IM_MESSAGE_SYSTEM;
		}

		return $filter;
	}

	private static function formatDeleteAllAgentName(int $userId, bool $withNotify, int $lastUnreadId): string
	{
		$params = [
			$userId,
			$withNotify ? 'true' : 'false',
			$lastUnreadId,
		];

		return __CLASS__ . '::deleteAll(' . implode(', ', $params) . ');';
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function deleteAllViaAgent(int $userId, bool $withNotify): void
	{
		$lastUnreadId = self::getLastUnreadId($userId, $withNotify);
		$agentName = self::deleteAll($userId,$withNotify, $lastUnreadId);

		if ($agentName !== '')
		{
			CAgent::addAgent(
				$agentName,
				'im',
				'N',
				self::UNREAD_DELETE_ALL_INTERVAL,
			);
		}
	}

	/**
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function deleteAll(int $userId, bool $withNotify, int $lastUnreadId): string
	{
		$filter = self::buildDeleteAllFilter($userId, $withNotify);
		$limit = (int)Option::get('im', 'unread_delete_all_limit', self::UNREAD_DELETE_ALL_LIMIT);
		$result = MessageUnreadTable::getList([
			'select' => ['ID'],
			'filter' => $filter,
			'limit' => $limit,
		]);

		$ids = [];

		foreach ($r = $result->fetchAll() as $row)
		{
			$ids[] = (int)$row['ID'];
		}

		if (empty($ids))
		{
			return '';
		}

		MessageUnreadTable::deleteByFilter(['@ID' => $ids]);
		CounterService::clearCache($userId);
		CounterOverflowService::deleteAllByUserId($userId);

		if (count($ids) < $limit)
		{
			return '';
		}

		return self::formatDeleteAllAgentName($userId, $withNotify, $lastUnreadId);
	}
}