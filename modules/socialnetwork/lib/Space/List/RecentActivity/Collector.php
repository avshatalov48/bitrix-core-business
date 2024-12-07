<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity;

use Bitrix\Socialnetwork\Integration\Calendar\RecentActivity\CalendarCommentProvider;
use Bitrix\Socialnetwork\Integration\Tasks\RecentActivity\TaskCommentProvider;
use Bitrix\Socialnetwork\Integration\Tasks\RecentActivity\TaskProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\LiveFeedCommentProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\LiveFeedProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\MembershipProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Collector\ProviderInterface;
use Bitrix\Socialnetwork\Integration\Calendar\RecentActivity\CalendarProvider;
use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

final class Collector
{
	public static function getDefaultProviders(): array
	{
		return [
			new CalendarProvider(),
			new LiveFeedProvider(),
			new TaskProvider(),
			new MembershipProvider(),
			new LiveFeedCommentProvider(),
			new CalendarCommentProvider(),
			new TaskCommentProvider(),
		];
	}

	/** @var array<ProviderInterface> $providers */
	public function __construct(private array $providers)
	{}

	public function addRecentActivityData(RecentActivityData $recentActivityData): void
	{
		foreach ($this->providers as $provider)
		{
			if ($provider->getTypeId() === $recentActivityData->getTypeId())
			{
				$provider->addItem($recentActivityData);
				break;
			}
		}
	}

	public function fillData(): void
	{
		foreach ($this->providers as $provider)
		{
			if ($provider->isAvailable())
			{
				$provider->fillData();
			}
		}
	}
}