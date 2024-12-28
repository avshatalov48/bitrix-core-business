<?php

namespace Bitrix\Im\V2\Message\Counter;

use Bitrix\Im\Model\CounterOverflowTable;
use Bitrix\Im\V2\Chat;

class CounterOverflowService
{
	protected const PARTIAL_INSERT_ROWS = 500;

	/**
	 * @var CounterOverflowInfo[]
	 */
	protected static array $overflowInfoStaticCache = [];
	protected static int $overflowValue = 100;
	protected int $chatId;

	public function __construct(int $chatId)
	{
		$this->chatId = $chatId;
	}

	public function insertOverflowed(array $counters): void
	{
		if (!$this->shouldInsert())
		{
			return;
		}

		$overflowedCounters = $this->filterOverflowedCounters($counters);
		$userIds = array_keys($overflowedCounters);
		$this->insert($userIds);
		foreach ($userIds as $userId)
		{
			self::cleanCacheByChatId($this->chatId, $userId, true);
		}
	}

	public function getOverflowInfo(array $userIds): CounterOverflowInfo
	{
		$infoFromCache = $this->getOverflowInfoFromCache($userIds);
		if ($infoFromCache)
		{
			return $infoFromCache;
		}

		$usersWithOverflowedCounters = $this->getUsersWithOverflow($userIds);
		$usersWithoutOverflowedCounters = $this->filterUsersWithoutOverflow($usersWithOverflowedCounters, $userIds);
		$overflowInfo = new CounterOverflowInfo(
			$usersWithOverflowedCounters,
			$usersWithoutOverflowedCounters
		);
		self::$overflowInfoStaticCache[$this->chatId] = $overflowInfo;

		return $overflowInfo;
	}

	protected function getOverflowInfoFromCache(array $userIds): ?CounterOverflowInfo
	{
		if (!isset(self::$overflowInfoStaticCache[$this->chatId]))
		{
			return null;
		}

		$info = self::$overflowInfoStaticCache[$this->chatId];
		foreach ($userIds as $userId)
		{
			if (!$info->has($userId))
			{
				return null;
			}
		}

		return $info;
	}

	public static function getOverflowValue(): int
	{
		return self::$overflowValue;
	}

	public function delete(int $userId): void
	{
		CounterOverflowTable::deleteByFilter(['=CHAT_ID' => $this->chatId, '=USER_ID' => $userId]);
		self::cleanCacheByChatId($this->chatId, $userId);
	}

	public static function deleteByChatIdForAll(int $chatId): void
	{
		CounterOverflowTable::deleteByFilter(['=CHAT_ID' => $chatId]);
		self::cleanCacheByChatId($chatId);
	}

	public static function deleteByChatIds(array $chatIds, ?int $userId = null): void
	{
		if (empty($chatIds))
		{
			return;
		}

		$filter = ['=CHAT_ID' => $chatIds];
		if (isset($userId))
		{
			$filter['=USER_ID'] = $userId;
		}

		CounterOverflowTable::deleteByFilter($filter);
		foreach ($chatIds as $chatId)
		{
			self::cleanCacheByChatId($chatId, $userId);
		}
	}

	public static function deleteAllByUserId(int $userId): void
	{
		CounterOverflowTable::deleteByFilter(['=USER_ID' => $userId]);
		$chatIds = [];

		foreach (self::$overflowInfoStaticCache as $chatId => $overflowInfo)
		{
			if ($overflowInfo->hasOverflow($userId))
			{
				$chatIds[] = $chatId;
			}
		}

		foreach ($chatIds as $chatId)
		{
			unset(self::$overflowInfoStaticCache[$chatId]);
		}
	}

	protected function getUsersWithOverflow(array $userIds): array
	{
		$result = [];
		if (empty($userIds))
		{
			return [];
		}

		$raw = CounterOverflowTable::query()
			->setSelect(['USER_ID'])
			->where('CHAT_ID', $this->chatId)
			->whereIn('USER_ID', $userIds)
			->exec()
		;

		foreach ($raw as $row)
		{
			$userId = (int)$row['USER_ID'];
			$result[$userId] = $userId;
		}

		return $result;
	}

	protected function filterUsersWithoutOverflow(array $overflowedUsers, array $allUsers): array
	{
		return array_filter($allUsers, static fn (int $userId) => !isset($overflowedUsers[$userId]));
	}

	protected function filterOverflowedCounters(array $counters): array
	{
		return array_filter($counters, static fn (int $counter) => $counter >= self::$overflowValue);
	}

	protected function insert(array $userIds): void
	{
		if (empty($userIds))
		{
			return;
		}

		$rows = $this->getRowsToInsert($userIds);

		foreach (array_chunk($rows, self::PARTIAL_INSERT_ROWS, true) as $part)
		{
			CounterOverflowTable::multiplyInsertWithoutDuplicate(
				$part,
				[
					'DEADLOCK_SAFE' => true,
					'UNIQUE_FIELDS' => ['CHAT_ID', 'USER_ID'],
				]
			);
		}
	}

	protected function getRowsToInsert(array $userIds): array
	{
		return array_map(fn (int $userId) => $this->getRowToInsert($userId), $userIds);
	}

	protected function getRowToInsert(int $userId): array
	{
		return ['CHAT_ID' => $this->chatId, 'USER_ID' => $userId];
	}

	protected function shouldInsert(): bool
	{
		return Chat::getInstance($this->chatId)->getType() !== Chat::IM_TYPE_SYSTEM;
	}

	protected static function cleanCacheByChatId(int $chatId, ?int $userId = null, bool $hasOverflowNow = false): void
	{
		if (!isset(self::$overflowInfoStaticCache[$chatId]))
		{
			return;
		}

		if ($userId === null)
		{
			unset(self::$overflowInfoStaticCache[$chatId]);

			return;
		}

		$wasOverflowed = self::$overflowInfoStaticCache[$chatId]->hasOverflow($userId);

		if ($wasOverflowed !== $hasOverflowNow)
		{
			self::$overflowInfoStaticCache[$chatId]->changeOverflowStatus($userId, $hasOverflowNow);
		}
	}
}