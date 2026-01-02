<?php

namespace Bitrix\Rest\Integration\Main;

use Bitrix\Main\Event;
use Bitrix\Rest\Notification\MarketExpired\Curtain\CurtainPageType;

class EventHandler
{
	public static function onSubscriptionChange(Event $event): void
	{
		\CUserOptions::DeleteOption('rest', 'marketSubscriptionPopupTs');
		\CUserOptions::DeleteOption('rest', 'marketSubscriptionPopupDismiss');

		foreach (CurtainPageType::cases() as $curtainPageType)
		{
			\CUserOptions::DeleteOption('rest', "marketSubscriptionCurtain{$curtainPageType->value}Dismiss");
			\CUserOptions::DeleteOption('rest', "marketSubscriptionCurtain{$curtainPageType->value}Ts");
		}
	}
}
