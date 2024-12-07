<?php

namespace Bitrix\Calendar\EventOption;

use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;

final class EventOptionRepository
{
	public static function getIdsByCategoryId(int $categoryId): array
	{
		$query = OpenEventOptionTable::query();
		$query->where('CATEGORY_ID', $categoryId);
		$query->addSelect('ID');
		//TODO: possible too many items, memory usage?
		$eventOptions = $query->fetchAll();

		return array_column($eventOptions, 'ID');
	}
}
