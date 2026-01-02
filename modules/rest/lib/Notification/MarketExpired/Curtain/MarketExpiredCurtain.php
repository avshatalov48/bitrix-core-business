<?php

namespace Bitrix\Rest\Notification\MarketExpired\Curtain;

use Bitrix\Rest\Notification\MarketExpired\MarketExpiredNotification;
use Bitrix\Rest\Service\RestUserOption;

abstract class MarketExpiredCurtain
{
	public function __construct(
		protected readonly RestUserOption $userOption,
		protected readonly MarketExpiredNotification $marketSubscriptionNotification,
	)
	{}

	abstract protected function isEnabledForCurrentUser(CurtainPageType $type): bool;

	abstract protected function isTimeToShowForCurrentUser(CurtainPageType $type): bool;

	final public function isReadyToShow(CurtainPageType $type = CurtainPageType::ANY_PAGE): bool
	{
		return $this->marketSubscriptionNotification->isAvailable() && $this->isEnabledForCurrentUser($type);
	}
}
