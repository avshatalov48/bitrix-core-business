<?php

namespace Bitrix\Rest\Notification\MarketExpired;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Rest\Infrastructure\Market\MarketSubscription;
use Bitrix\Rest\Infrastructure\Market\MarketOption;
use Bitrix\Rest\Notification\MarketExpired\Curtain\MarketExpiredCurtain;
use Bitrix\Rest\Notification\MarketExpired\Curtain\SubscriptionCurtain;
use Bitrix\Rest\Notification\MarketExpired\Curtain\TransitionCurtain;
use Bitrix\Rest\Notification\MarketExpired\Popup\MarketExpiredPopup;
use Bitrix\Rest\Notification\MarketExpired\Popup\SubscriptionPopup;
use Bitrix\Rest\Notification\MarketExpired\Popup\TransitionPopup;
use Bitrix\Rest\Service\RestOption;
use Bitrix\Rest\Service\RestUserOption;

class MarketSubscriptionNotification extends MarketExpiredNotification
{
	public function getPopup(): MarketExpiredPopup
	{
		return new SubscriptionPopup(new RestUserOption(), $this);
	}

	public function getCurtain(): MarketExpiredCurtain
	{
		return new SubscriptionCurtain(new RestUserOption(), $this);
	}

	public function getEndDateTimestamp(): int
	{
		$endDate = $this->marketSubscription->getEndDate();

		return $endDate ? $endDate->getTimestamp() + 86400 : 0;
	}

	public function isAvailable(): bool
	{
		if (Loader::includeModule('extranet') && \CExtranet::IsExtranetSite())
		{
			return false;
		}

		if (!$this->marketSubscription->isAvailableToPurchase())
		{
			return false;
		}

		if (
			$this->getEndDateTimestamp() + self::AFTER_SHOW_DURATION < time()
			|| $this->getEndDateTimestamp() - self::BEFORE_SHOW_DURATION > time()
		)
		{
			return false;
		}

		if (!$this->marketSubscription->isPaidAppsOrIntegrationsInstalled())
		{
			return false;
		}

		return true;
	}

	public function getType(): MarketExpiredType
	{
		return $this->marketSubscription->isActive()
			? MarketExpiredType::WARNING
			: MarketExpiredType::FINAL;
	}

	public function getCategory(): MarketExpiredCategory
	{
		return $this->marketSubscription->isDemo()
			? MarketExpiredCategory::TRIAL
			: MarketExpiredCategory::SUBSCRIPTION;
	}
}
