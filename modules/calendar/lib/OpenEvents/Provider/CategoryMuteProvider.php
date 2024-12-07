<?php

namespace Bitrix\Calendar\OpenEvents\Provider;

use Bitrix\Calendar\Core\Common;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryMutedTable;
use Bitrix\Main\Engine\CurrentUser;

final class CategoryMuteProvider
{
	private int $userId;

	public function __construct(?int $userId = null)
	{
		$this->userId = $userId ?? (int)CurrentUser::get()->getId();
	}

	public function isMuted(int $categoryId): bool
	{
		$mutes = $this->getByCategoryIds([$categoryId]);

		return $mutes[$categoryId];
	}

	/**
	 * b_calendar_open_event_category_muted stores mutes
	 * But if category is muted by default, b_calendar_open_event_category_muted stores unmutes
	 *
	 * Category is muted by default if b_calendar_open_event_category_muted stores row with USER_ID=0
	 *
	 * @param int[] $categoryIds
	 * @return Array<int, bool>
	 */
	public function getByCategoryIds(array $categoryIds): array
	{
		if (empty($categoryIds))
		{
			return [];
		}

		$mutedQuery = OpenEventCategoryMutedTable::query()
			->setSelect(['USER_ID', 'CATEGORY_ID'])
			->whereIn('USER_ID', [Common::SYSTEM_USER_ID, $this->userId])
			->whereIn('CATEGORY_ID', $categoryIds)
		;

		$mutedQueryResult = $mutedQuery->exec();

		$mutedCategoryIds = [];
		$defaultMutedCategoryIds = [];
		while ($mute = $mutedQueryResult->fetchObject())
		{
			$userId = $mute->getUserId();
			$categoryId = $mute->getCategoryId();
			if ($userId === Common::SYSTEM_USER_ID)
			{
				$defaultMutedCategoryIds[$categoryId] = $categoryId;
			}
			else
			{
				$mutedCategoryIds[$categoryId] = $categoryId;
			}
		}

		$mutes = array_map(static function(int $id) use ($defaultMutedCategoryIds, $mutedCategoryIds) {
			$isMutedByDefault = isset($defaultMutedCategoryIds[$id]);
			if ($isMutedByDefault)
			{
				$isUnmutedByUser = isset($mutedCategoryIds[$id]);
				$isMuted = !$isUnmutedByUser;
			}
			else
			{
				$isMutedByUser = isset($mutedCategoryIds[$id]);
				$isMuted = $isMutedByUser;
			}

			return $isMuted;
		}, $categoryIds);

		return array_combine($categoryIds, $mutes);
	}
}
