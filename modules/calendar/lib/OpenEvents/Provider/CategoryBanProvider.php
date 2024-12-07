<?php

namespace Bitrix\Calendar\OpenEvents\Provider;

use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable;
use Bitrix\Main\Engine\CurrentUser;

final class CategoryBanProvider
{
	protected static array $cache = [];

	protected int $userId;

	public function __construct(?int $userId = null)
	{
		$this->userId = $userId ?? (int)CurrentUser::get()->getId();
	}

	/**
	 * @return int[]
	 */
	public function listIds(): array
	{
		if (empty(self::$cache[$this->userId]))
		{
			self::$cache[$this->userId] = OpenEventCategoryBannedTable::query()
				->setSelect(['CATEGORY_ID'])
				->where('USER_ID', $this->userId)
				->fetchCollection()
				->getCategoryIdList()
			;
		}

		return self::$cache[$this->userId];
	}

	/**
	 * @return int[]
	 */
	public function getUsersWhoBannedTheCategory(array $userIds, int $categoryId): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$bannedQuery = OpenEventCategoryBannedTable::query()
			->setSelect(['USER_ID', 'CATEGORY_ID'])
			->whereIn('USER_ID', $userIds)
			->where('CATEGORY_ID', $categoryId)
		;

		$bannedQueryResult = $bannedQuery->exec();

		$usersWhoBanned = [];
		while($ban = $bannedQueryResult->fetchObject())
		{
			$usersWhoBanned[$ban->getUserId()] = true;
		}

		$bans = array_map(static fn(int $userId) => !empty($usersWhoBanned[$userId]), $userIds);

		return array_combine($userIds, $bans);
	}
}
