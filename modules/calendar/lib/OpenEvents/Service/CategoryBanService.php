<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryBannedTable;

final class CategoryBanService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function setBan(int $userId, int $categoryId, bool $isBanned): void
	{
		if ($isBanned)
		{
			$this->banCategoryMulti($categoryId, [$userId]);
		}
		else
		{
			$this->unbanCategoryMulti($categoryId, [$userId]);
		}
	}

	public function banCategoryMulti(int $categoryId, array $userIds): void
	{
		OpenEventCategoryBannedTable::insertIgnoreMulti(
			array_map(
				static fn(int $userId) => ['USER_ID' => $userId, 'CATEGORY_ID' => $categoryId],
				$userIds,
			),
		);
	}

	public function unbanCategoryMulti(int $categoryId, array $userIds): void
	{
		OpenEventCategoryBannedTable::deleteByFilter([
			'USER_ID' => $userIds,
			'CATEGORY_ID' => $categoryId,
		]);
	}

	private function __construct()
	{
	}
}
