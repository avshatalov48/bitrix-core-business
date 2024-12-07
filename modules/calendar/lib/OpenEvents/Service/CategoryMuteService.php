<?php

namespace Bitrix\Calendar\OpenEvents\Service;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryUpdate;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable;

class CategoryMuteService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function setMutedByDefault(int $categoryId): void
	{
		OpenEventCategoryMutedTable::insertIgnore([
			'USER_ID' => Common::SYSTEM_USER_ID,
			'CATEGORY_ID' => $categoryId,
		]);
	}

	public function setMute(int $userId, int $categoryId, bool $isMuted): void
	{
		$mutedByDefaultQuery = OpenEventCategoryMutedTable::query()
			->setSelect(['USER_ID', 'CATEGORY_ID'])
			->where('USER_ID', Common::SYSTEM_USER_ID)
			->where('CATEGORY_ID', $categoryId)
		;

		$isMutedByDefault = $mutedByDefaultQuery->fetchObject() !== null;

		if (
			!$isMutedByDefault && $isMuted
			|| $isMutedByDefault && !$isMuted
		)
		{
			OpenEventCategoryMutedTable::insertIgnore([
				'USER_ID' => $userId,
				'CATEGORY_ID' => $categoryId,
			]);
		}
		else
		{
			OpenEventCategoryMutedTable::deleteByFilter([
				'USER_ID' => $userId,
				'CATEGORY_ID' => $categoryId,
			]);
		}

		(new AfterEventCategoryUpdate(
			$categoryId,
			['isMuted' => $isMuted],
			$userId,
		))->emit();
	}
}
