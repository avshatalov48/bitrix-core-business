<?php

namespace Bitrix\Rest\Notification\MarketExpired\Popup;

use Bitrix\Main\Loader;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredCategory;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredType;

class TransitionPopup extends MarketExpiredPopup
{
	protected function getOptionPrefix(): string
	{
		return 'marketTransitionPopup';
	}

	protected function isEnabledForCurrentUser(): bool
	{
		return !$this->isDismissedByUser() && $this->isTimeToShowForCurrentUser();
	}
}
