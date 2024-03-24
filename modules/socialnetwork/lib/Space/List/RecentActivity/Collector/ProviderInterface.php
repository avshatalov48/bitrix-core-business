<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;

use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

interface ProviderInterface
{
	public function fillData(): void;
	public function addItem(RecentActivityData $recentActivityData): void;
	public function getTypeId(): string;
}
