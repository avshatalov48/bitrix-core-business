<?php

namespace Bitrix\Rest\Infrastructure\Market;

use Bitrix\Main\Application;
use Bitrix\Main\Type\Date;
use Bitrix\Rest\Marketplace\Client;
use Bitrix\Rest\Marketplace\Url;
use Bitrix\Rest\Service\RestOption;
use Bitrix\Rest\Service\ServiceContainer;

class MarketSubscription
{
	private const BASE_CACHE_DIR = 'rest/market_subscription';

	public function __construct(
		private readonly MarketOption $marketOption
	)
	{}

	public static function createByDefault(): self
	{
		return new self(new MarketOption(new RestOption()));
	}

	public function isRequiredSubscriptionModelStarted(): bool
	{
		if (!Client::isSubscriptionAccess())
		{
			return false;
		}

		if (Application::getInstance()->getLicense()->getRegion() !== 'ru')
		{
			return false;
		}

		if ($this->marketOption->isNewPoliticsEnabled())
		{
			return true;
		}

		if (!$this->isTransitionPeriodEnabled() && !$this->isPaidAppsOrIntegrationsInstalled())
		{
			$this->marketOption->enableNewPolitics();

			return true;
		}

		return $this->isTransitionPeriodEnds();
	}

	public function isAvailableToPurchase(): bool
	{
		return Client::isSubscriptionAccess();
	}

	public function isActive(): bool
	{
		return Client::isSubscriptionAvailable();
	}

	public function isDemo(): bool
	{
		return Client::isSubscriptionDemo();
	}

	public function isDemoAvailable(): bool
	{
		return Client::isSubscriptionDemoAvailable();
	}

	public function getEndDate(): ?Date
	{
		return Client::getSubscriptionFinalDate();
	}

	public function isDiscountAvailable(): bool
	{
		return $this->marketOption->isDiscountAvailable();
	}

	public function isPaidAppsOrIntegrationsInstalled(): bool
	{
		$cache = Application::getInstance()->getCache();

		if (
			$cache->initCache(
				86400,
				'has_subscription_app_or_integration',
				self::BASE_CACHE_DIR,
			)
		)
		{
			return $cache->getVars();
		}

		$hasSubscriptionIntegrations =
			ServiceContainer::getInstance()->getIntegrationService()->hasPaidIntegrations()
			|| ServiceContainer::getInstance()->getAppService()->hasPaidApps()
		;
		$cache->startDataCache();
		$cache->endDataCache($hasSubscriptionIntegrations);

		return $hasSubscriptionIntegrations;
	}

	public function getBuyUrl(): string
	{
		return Url::getSubscriptionBuyUrl();
	}

	public function getTransitionPeriodEndDate(): Date
	{
		$demoEndDate = $this->isDemo() ? $this->getEndDate() : null;
		$endDate = $this->marketOption->getSavedTransitionPeriodEndDate();

		if ($demoEndDate)
		{
			return max($demoEndDate, $endDate);
		}

		return $endDate;
	}

	public function isTransitionPeriodEnds(): bool
	{
		return $this->isTransitionPeriodEnabled()
			&& $this->getTransitionPeriodEndDate()->getTimestamp() < time();
	}

	public function isTransitionPeriodEnabled(): bool
	{
		return $this->marketOption->isTransitionPeriodEnabled();
	}
}