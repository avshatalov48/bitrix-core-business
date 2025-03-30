<?php

namespace Bitrix\Rest\Notification;

use Bitrix\Rest\Infrastructure\Market\MarketSubscription;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\Infrastructure\Market\MarketOption;
use Bitrix\Rest\Service\RestOption;
use Bitrix\Rest\Service\RestUserOption;

class MarketExpiredCurtain
{
	private const SECONDS_IN_DAY = 86400; // 60 * 60 * 24

	public function __construct(
		private readonly RestUserOption $userOption,
		private readonly MarketOption $marketOptions,
		public readonly MarketSubscription $marketSubscription,
	)
	{}

	public static function getByDefault(): MarketExpiredCurtain
	{
		return new self(new RestUserOption(), new MarketOption(new RestOption()), MarketSubscription::createByDefault());
	}

	public function isReadyToShow(string $type): bool
	{
		return !MarketExpiredPopup::createByDefault()->isDismissedByUser()
			&& $this->marketOptions->isTransitionPeriodEnabled()
			&& !$this->marketSubscription->isActive()
			&& $this->isEnabledForCurrentUser($type)
			&& $this->marketSubscription->isPaidAppsOrIntegrationsInstalled()
			&& $this->marketSubscription->isAvailableToPurchase();
	}

	private function isEnabledForCurrentUser(string $type): bool
	{
		$lastShowTimestamp = $this->userOption->get("marketTransitionCurtain{$type}Ts", null);

		if (!$lastShowTimestamp || !is_numeric($lastShowTimestamp))
		{
			return true;
		}

		return $lastShowTimestamp + self::SECONDS_IN_DAY < time();
	}
}