<?php

namespace Bitrix\Rest\Notification\MarketExpired\Curtain;

use Bitrix\Rest\Notification\MarketExpired\MarketExpiredCategory;
use Bitrix\Rest\Notification\MarketExpired\MarketExpiredType;
use Bitrix\Rest\Notification\MarketExpired\Popup\MarketExpiredPopup;

class TransitionCurtain extends MarketExpiredCurtain
{
	private const SHOW_DELAY = 86400; // 1 day

	protected function isEnabledForCurrentUser(CurtainPageType $type): bool
	{
		if ($type === CurtainPageType::ANY_PAGE)
		{
			return false;
		}

		return $this->isTimeToShowForCurrentUser($type)
			&& !$this->marketSubscriptionNotification->getPopup()->isDismissedByUser();
	}

	protected function isTimeToShowForCurrentUser(CurtainPageType $type): bool
	{
		$lastShowTimestamp = $this->userOption->get("marketTransitionCurtain{$type->value}Ts", null);

		if (!$lastShowTimestamp || !is_numeric($lastShowTimestamp))
		{
			return true;
		}

		return $lastShowTimestamp + self::SHOW_DELAY < time();
	}
}