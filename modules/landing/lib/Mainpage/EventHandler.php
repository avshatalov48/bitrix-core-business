<?php

namespace Bitrix\Landing\Mainpage;

use Bitrix\AI\Integration;
use Bitrix\Bitrix24\Feature;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

class EventHandler
{
	public static function onLicenseHasChanged(Event $event): void
	{
		if (
			Loader::includeModule('bitrix24')
			&& Loader::includeModule('intranet')
			&& $event->getParameter('licenseType')
			&& !Feature::isFeatureEnabledFor('main_page', $event->getParameter('licenseType'))
		)
		{
			EventManager::getInstance()->unregisterEventHandler(
				'intranet',
				'onLicenseHasChanged',
				'bitrix24',
				self::class,
				'onLicenseHasChanged'
			);

			Manager::setFreeTariffMode(false);
		}
	}
}