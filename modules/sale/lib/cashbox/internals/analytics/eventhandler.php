<?php
namespace Bitrix\Sale\Cashbox\Internals\Analytics;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class EventHandler
 * @package Bitrix\Sale\Cashbox\Internals\Analytics
 * @internal
 */
class EventHandler
{
	/**
	 * @param Main\Event $event
	 */
	public static function onPrintableCheckSend(Main\Event $event): void
	{
		$check = $event->getParameter('CHECK');
		if (is_array($check) && isset($check['ID']))
		{
			$check = Sale\Cashbox\CheckManager::getObjectById($check['ID']);
			if ($check)
			{
				$provider = new Sale\Cashbox\Internals\Analytics\Provider($check);
				(new Sale\Internals\Analytics\Storage($provider))->save();
			}
		}
	}
}
