<?php

namespace Bitrix\Rest\Notification;

use Bitrix\Rest\Contract\OptionContract;
use Bitrix\Rest\Infrastructure\Market\MarketSubscription;
use Bitrix\Main\Application;
use Bitrix\Rest\Service\RestUserOption;

class MarketExpiredPopup
{
	private const POPUP_SHOW_DELAY = 60 * 60 * 24; // 1 day
	private const BEFORE_DEMO_ENDS_DELAY = 60 * 60 * 24 * 7; // 1 week
	private const AFTER_TRANSITION_SHOW_DURATION = 60 * 60 * 24 * 7; // 1 week
	private const SHOW_TIMESTAMP_OPTION = 'marketTransitionPopupTs';
	private const DISMISS_OPTION = 'marketTransitionPopupDismiss';

	public function __construct(
		private readonly OptionContract $userOption,
		public readonly MarketSubscription $marketSubscription,
	)
	{}

	public static function createByDefault(): MarketExpiredPopup
	{
		return new self(new RestUserOption(), MarketSubscription::createByDefault());
	}

	public function isReadyToShow(): bool
	{
		if (!$this->marketSubscription->isAvailableToPurchase())
		{
			return false;
		}

		if (!$this->marketSubscription->isTransitionPeriodEnabled())
		{
			return false;
		}

		$transitionEndTs = $this->marketSubscription->getTransitionPeriodEndDate()->getTimestamp();

		if ($transitionEndTs + self::AFTER_TRANSITION_SHOW_DURATION < time())
		{
			return false;
		}

		if (!$this->marketSubscription->isPaidAppsOrIntegrationsInstalled())
		{
			return false;
		}

		if ($this->marketSubscription->isDemo())
		{
			return $transitionEndTs < time() + self::BEFORE_DEMO_ENDS_DELAY
				&& $this->isEnabledForCurrentUser();
		}

		return !$this->marketSubscription->isActive()
			&& $this->isEnabledForCurrentUser();
	}

	public function getFormattedTransitionPeriodEndDate(): string
	{
		return FormatDate(
			Application::getInstance()->getContext()->getCulture()->get('LONG_DATE_FORMAT'),
			$this->marketSubscription->getTransitionPeriodEndDate()->getTimestamp(),
		);
	}

	public function isDismissedByUser(): bool
	{
		return $this->userOption->get(self::DISMISS_OPTION, 'N') === 'Y';
	}

	public function getCurrentType(): MarketExpiredPopupType
	{
		return $this->marketSubscription->isTransitionPeriodEnds()
			? MarketExpiredPopupType::FINAL
			: MarketExpiredPopupType::WARNING;
	}

	public function getOpenLinesWidgetCode(): string
	{
		return match (Application::getInstance()->getLicense()->getRegion())
		{
			'ru' => '160_j8zdo1',
			default => '',
		};
	}

	public function isEnabledForCurrentUser(): bool
	{
		if ($this->isDismissedByUser())
		{
			return false;
		}

		$lastShowTimestamp = $this->userOption->get(self::SHOW_TIMESTAMP_OPTION, null);

		if (!is_numeric($lastShowTimestamp) || !$lastShowTimestamp)
		{
			return true;
		}

		return $lastShowTimestamp + self::POPUP_SHOW_DELAY < time();
	}
}