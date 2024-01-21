<?php

namespace Bitrix\Socialnetwork\Space\List\RecentSearch;

use Bitrix\Main\Type\Contract\Arrayable;

final class SpaceSearchDataCollection implements Arrayable
{

	/** @var array<SpaceSearchData> $items */
	private array $items = [];

	public function add(SpaceSearchData $spaceSearchData): void
	{
		$this->items[] = $spaceSearchData;
	}

	public function toArray(): array
	{
		return $this->items;
	}

	public function getSpaceSearchDataBySpacesId(int $spaceId): ?SpaceSearchData
	{
		$result = null;
		foreach ($this->items as $item)
		{
			if ($spaceId === $item->getSpaceId())
			{
				$result = $item;
				break;
			}
		}

		return $result;
	}
}