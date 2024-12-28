<?php

namespace Bitrix\Bizproc\Integration\Intranet;

use Bitrix\Bizproc\Integration;
use Bitrix\Main;
use Bitrix\Main\Event;

/**
 * Event handlers for module Intranet
 */
class EventHandler
{
	/**
	 * @param Event $event
	 * @return void
	 */
	public static function onSettingsProvidersCollect(Main\Event $event): void
	{
		$providers = $event->getParameter('providers');
		$provider = new Integration\Intranet\Settings\AutomationSettingsPageProvider();

		$employeeProvider = array_values(
			array_filter(
				$providers ?? [],
				fn($item) => $item->getType() === 'employee'
			)
		)[0] ?? null;

		if ($employeeProvider)
		{
			$provider->setSort($employeeProvider->getSort() + 5);
		}

		$providers[$provider->getType()] = $provider;

		$event->addResult(new Main\EventResult(Main\EventResult::SUCCESS, ['providers' => $providers]));
	}
}
