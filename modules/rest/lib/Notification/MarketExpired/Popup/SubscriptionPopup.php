<?php

namespace Bitrix\Rest\Notification\MarketExpired\Popup;

use Bitrix\Intranet\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredCategory;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredType;

class SubscriptionPopup extends MarketExpiredPopup
{
	protected function isEnabledForCurrentUser(): bool
	{
		return CurrentUser::get()->isAdmin()
			&& !$this->isDismissedByUser()
			&& $this->isTimeToShowForCurrentUser();
	}

	protected function getOptionPrefix(): string
	{
		return 'marketSubscriptionPopup';
	}
}
