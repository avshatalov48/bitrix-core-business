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

class MarketTransitionNotification extends MarketExpiredNotification
{
	private const BEFORE_DEMO_ENDS_DELAY = 60 * 60 * 24 * 7; // 1 week

	public function getPopup(): MarketExpiredPopup
	{
		return new TransitionPopup(new RestUserOption(), $this);
	}

	public function getCurtain(): MarketExpiredCurtain
	{
		return new TransitionCurtain(new RestUserOption(), $this);
	}

	public function getEndDateTimestamp(): int
	{
		return $this->marketSubscription->getTransitionPeriodEndDate()->getTimestamp();
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

		if (!$this->marketSubscription->isTransitionPeriodEnabled())
		{
			return false;
		}

		$transitionEndTs = $this->marketSubscription->getTransitionPeriodEndDate()->getTimestamp();

		if ($transitionEndTs + self::AFTER_SHOW_DURATION < time())
		{
			return false;
		}

		if (!$this->marketSubscription->isPaidAppsOrIntegrationsInstalled())
		{
			return false;
		}

		if ($this->marketSubscription->isDemo())
		{
			return $transitionEndTs < time() + self::BEFORE_DEMO_ENDS_DELAY;
		}

		return !$this->marketSubscription->isActive();
	}

	public function getType(): MarketExpiredType
	{
		return $this->marketSubscription->isTransitionPeriodEnds()
			? MarketExpiredType::FINAL
			: MarketExpiredType::WARNING;
	}

	public function getCategory(): MarketExpiredCategory
	{
		return MarketExpiredCategory::TRANSITION;
	}

	public function getOpenLinesWidgetCode(): string
	{
		return match (Application::getInstance()->getLicense()->getRegion())
		{
			'ru' => '160_j8zdo1',
			default => '',
		};
	}
}
