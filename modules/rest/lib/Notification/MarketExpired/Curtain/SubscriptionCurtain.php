<?php

namespace Bitrix\Rest\Notification\MarketExpired\Curtain;

use Bitrix\Intranet\CurrentUser;

class SubscriptionCurtain extends MarketExpiredCurtain
{
	private const SHOW_DELAY = 86400; // 1 day

	protected function isEnabledForCurrentUser(CurtainPageType $type = CurtainPageType::ANY_PAGE): bool
	{
		return CurrentUser::get()->isAdmin()
			? $this->isEnabledForAdmin($type)
			: $this->isEnabledForUser($type);
	}

	protected function isTimeToShowForCurrentUser(CurtainPageType $type): bool
	{
		$lastShowTimestamp = $this->userOption->get("marketSubscriptionCurtain{$type->value}Ts", null);

		if (!$lastShowTimestamp || !is_numeric($lastShowTimestamp))
		{
			return true;
		}

		return $lastShowTimestamp + self::SHOW_DELAY < time();
	}

	private function isDismissedByUser(CurtainPageType $type): bool
	{
		return $this->userOption->get("marketSubscriptionCurtain{$type->value}Dismiss", 'N') === 'Y';
	}

	private function isEnabledForAdmin(CurtainPageType $type): bool
	{
		if ($type === CurtainPageType::ANY_PAGE)
		{
			return false;
		}

		return !$this->isDismissedByUser($type)
			&& $this->isTimeToShowForCurrentUser($type)
			&& $this->marketSubscriptionNotification->getPopup()->isDismissedByUser();
	}

	private function isEnabledForUser(CurtainPageType $type): bool
	{
		if ($this->marketSubscriptionNotification->marketSubscription->isActive())
		{
			return false;
		}

		return !$this->isDismissedByUser($type)
			&& $this->isTimeToShowForCurrentUser($type);
	}
}