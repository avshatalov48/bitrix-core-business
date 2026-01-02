<?php

namespace Bitrix\Rest\Notification\MarketExpired\Popup;

use Bitrix\Rest\Contract\OptionContract;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredNotification;

abstract class MarketExpiredPopup
{
	private const POPUP_SHOW_DELAY = 60 * 60 * 24; // 1 day

	public function __construct(
		protected readonly OptionContract $userOption,
		protected readonly MarketExpiredNotification $marketSubscriptionNotification,
	)
	{}

	abstract protected function getOptionPrefix(): string;

	abstract protected function isEnabledForCurrentUser(): bool;

	final public function isReadyToShow(): bool
	{
		return $this->marketSubscriptionNotification->isAvailable() && $this->isEnabledForCurrentUser();
	}

	final public function isDismissedByUser(): bool
	{
		return $this->userOption->get($this->getOptionPrefix() . 'Dismiss', 'N') === 'Y';
	}

	final protected function isTimeToShowForCurrentUser(): bool
	{
		$lastShowTimestamp = $this->userOption->get($this->getOptionPrefix() . 'Ts', null);

		if (!is_numeric($lastShowTimestamp) || !$lastShowTimestamp)
		{
			return true;
		}

		return $lastShowTimestamp + self::POPUP_SHOW_DELAY < time();
	}
}