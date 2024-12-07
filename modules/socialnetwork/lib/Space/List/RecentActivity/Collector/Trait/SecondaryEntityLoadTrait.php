<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\Trait;

use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

trait SecondaryEntityLoadTrait
{
	/** @return array<int> */
	protected function getEntityIdsFromRecentActivityItems(): array
	{
		return array_map(fn(RecentActivityData $item): int => $item->getSecondaryEntityId(), $this->recentActivityDataItems);
	}

	protected function getEntityIdFromRecentActivityItem(RecentActivityData $recentActivityData): int
	{
		return $recentActivityData->getSecondaryEntityId();
	}
}