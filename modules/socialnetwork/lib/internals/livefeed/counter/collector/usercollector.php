<?php

namespace Bitrix\Socialnetwork\Internals\LiveFeed\Counter\Collector;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\UserAccessTable;
use Bitrix\Socialnetwork\Item\Log;
use Bitrix\Socialnetwork\Item\LogRight;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterDictionary;

class UserCollector
{
	public const PUBLIC_ACCESS_CODES = ['AU', 'G2'];

	private int $userId;
	private array $sonetLogGroups = [];
	private array|null $userAccessCodes = null;

	private static array $instances = [];

	public static function getInstance(int $userId)
	{
		if (!array_key_exists($userId, self::$instances))
		{
			self::$instances[$userId] = new self($userId);
		}

		return self::$instances[$userId];
	}

	private function __construct(int $userId)
	{
		$this->userId = $userId;
	}

	public function recount(string $counter, array $sonetLogIds = []): array
	{
		if (!$this->userId)
		{
			return [];
		}

		if (empty($sonetLogIds))
		{
			return [];
		}

		$sonetLogIds = array_unique($sonetLogIds);
		sort($sonetLogIds);

		$counters = [];

		switch ($counter)
		{
			case CounterDictionary::COUNTER_NEW_POSTS:
				$counters = $this->recountPosts($sonetLogIds);
				break;
			case CounterDictionary::COUNTER_NEW_COMMENTS:
				$counters = $this->recountComments($sonetLogIds);
				break;
			default:
				break;
		}

		return $counters;
	}

	private function recountPosts(array $sonetLogIds): array
	{
		$counters = [];

		foreach ($sonetLogIds as $logId)
		{
			$logItem = Log::getById($logId);
			if (!$logItem)
			{
				continue;
			}

			$logItemFields = $logItem->getFields();
			// skip if the current user is an author of the post
			if ((int)$logItemFields['USER_ID'] === $this->userId)
			{
				continue;
			}

			if (!in_array($logItemFields['ENTITY_TYPE'], \CSocNetAllowed::GetAllowedEntityTypes(), true))
			{
				continue;
			}

			if (!$this->isItemForEveryOne($logId) && !$this->userHasAccess($logId))
			{
				continue;
			}

			$params = [
				'RATING_ENTITY_ID' => $logItemFields['RATING_ENTITY_ID'],
				'RATING_TYPE_ID' => $logItemFields['RATING_TYPE_ID'],
			];
			if (!$this->isItemSeenByUser($params))
			{
				foreach ($this->findGroupsByLogIdAndUser($logId) as $groupId)
				{
					$counters[] = [
						'USER_ID' => $this->userId,
						'SONET_LOG_ID' => $logId,
						'GROUP_ID' => $groupId,
						'TYPE' => CounterDictionary::COUNTER_NEW_POSTS,
						'VALUE' => 1
					];
				}
			}
		}

		return $counters;
	}

	private function recountComments(array $sonetLogIds): array
	{
		$counters = [];

		foreach ($sonetLogIds as $logId)
		{
			$logItem = Log::getById($logId);
			if (!$logItem)
			{
				continue;
			}

			$logItemFields = $logItem->getFields();
			if (!in_array($logItemFields['ENTITY_TYPE'], \CSocNetAllowed::GetAllowedEntityTypes(), true))
			{
				continue;
			}

			if (!$this->isItemForEveryOne($logId) && !$this->userHasAccess($logId))
			{
				continue;
			}

			$params = [
				'RATING_ENTITY_ID' => $logItemFields['RATING_ENTITY_ID'],
				'RATING_TYPE_ID' => $logItemFields['RATING_TYPE_ID'],
			];
			$commentsCount = $this->isItemSeenByUser($params)
				? $this->getCountCommentsByLogItemAndLastDateSeen($logId, $params)
				: $this->getCountCommentsByLogItem($logId);

			if ($commentsCount > 0)
			{
				foreach ($this->findGroupsByLogIdAndUser($logId) as $groupId)
				{
					$counters[] = [
						'USER_ID' => $this->userId,
						'SONET_LOG_ID' => $logId,
						'GROUP_ID' => $groupId,
						'TYPE' => CounterDictionary::COUNTER_NEW_COMMENTS,
						'VALUE' => $commentsCount
					];
				}
			}
		}

		return $counters;
	}

	private function getCountCommentsByLogItemAndLastDateSeen(int $logId, array $params): int
	{
		$lastTimeSeen = $this->getContentViewByItem($params);
		if (!isset($lastTimeSeen['DATE_VIEW']))
		{
			return 0;
		}

		$res = LogCommentTable::getList([
			'select' => ['CNT'],
			'filter' => [
				'=LOG_ID' => $logId,
				'!USER_ID' => $this->userId,
				'>LOG_DATE' => $lastTimeSeen['DATE_VIEW']
			],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)'),
			]
		])->fetch();

		return $res['CNT'] ?? 0;
	}

	private function getCountCommentsByLogItem(int $logId): int
	{
		$res = LogCommentTable::getList([
			'select' => ['CNT'],
			'filter' => [
				'=LOG_ID' => $logId,
				'!USER_ID' => $this->userId,
			],
			'runtime' => [
				new ExpressionField('CNT', 'COUNT(*)'),
			]
		])->fetch();

		return $res['CNT'] ?? 0;
	}

	private function isItemSeenByUser(array $params): bool
	{
		return (bool)$this->getContentViewByItem($params);
	}

	private function getContentViewByItem(array $params): array|false
	{
		return UserContentViewTable::getList([
			'select' => ['DATE_VIEW'],
			'filter' => [
				'=USER_ID' => $this->userId,
				'=RATING_ENTITY_ID' => $params['RATING_ENTITY_ID'],
				'=RATING_TYPE_ID' => $params['RATING_TYPE_ID']
			]
		])->fetch();
	}

	private function isItemForEveryOne(int $logItemId): bool
	{
		foreach (LogRight::get($logItemId) as $logAccessRight)
		{
			if (in_array($logAccessRight, self::PUBLIC_ACCESS_CODES, true))
			{
				return true;
			}
		}

		return false;
	}

	private function userHasAccess(int $logItemId): bool
	{
		$rights = LogRight::get($logItemId);

		if ($this->userAccessCodes === null && $rights)
		{
			$this->userAccessCodes = [];
			$res = UserAccessTable::getList([
				'select' => ['ACCESS_CODE'],
				'filter' => [
					'=USER_ID' => $this->userId,
					'=ACCESS_CODE' => $rights,
				]
			])->fetchAll();

			foreach ($res as $access)
			{
				if (isset($access['ACCESS_CODE']))
				{
					$this->userAccessCodes[] = $access['ACCESS_CODE'];
				}
			}
		}

		foreach ($rights as $logRight)
		{
			if (in_array($logRight, $this->userAccessCodes, true))
			{
				return true;
			}
		}

		return false;
	}

	private function findGroupsByLogIdAndUser(int $sonetLogId): array
	{
		if (!empty($this->sonetLogGroups[$sonetLogId]))
		{
			return $this->sonetLogGroups[$sonetLogId];
		}

		$this->sonetLogGroups[$sonetLogId] = [];
		$sonetLogRights = LogRight::get($sonetLogId);
		$userAccessCodes = array_merge(self::PUBLIC_ACCESS_CODES, ['U' . $this->userId]);
		foreach ($sonetLogRights as $logRight)
		{
			if (in_array($logRight, $userAccessCodes))
			{
				// append common group
				$commonGroupId = 0;
				$this->sonetLogGroups[$sonetLogId][$commonGroupId] = $commonGroupId;
				break;
			}
		}

		$query = UserAccessTable::query()
			->setDistinct()
			->setSelect([
				'ACCESS_CODE',
			])
			->where('USER_ID', '=', $this->userId)
			->where('PROVIDER_ID', '=', 'socnetgroup')
			->whereIn('ACCESS_CODE', $sonetLogRights)
			->exec();

		foreach ($query->fetchAll() as $group)
		{
			$matches = [];
			preg_match('/SG([0-9]+)/m', $group['ACCESS_CODE'], $matches);
			if (isset($matches[1]))
			{
				$groupId = (int)$matches[1];
				$this->sonetLogGroups[$sonetLogId][$groupId] = $groupId;
			}
		}

		return $this->sonetLogGroups[$sonetLogId];
	}
}