<?php

namespace Bitrix\Sale\Cashbox\EventsHandler;

use Bitrix\Main;
use Bitrix\Sale\Cashbox;

class CashboxYooKassa
{
	/**
	 * @param Main\Event $event
	 * @return Main\EventResult
	 */
	public static function onBeforeCashboxAdd(Main\Event $event): Main\EventResult
	{
		$result = $event->getParameters();

		if ($result['HANDLER'] === '\\' . Cashbox\CashboxYooKassa::class)
		{
			$result['OFD'] = '\\' . Cashbox\FirstOfd::class;
		}

		return new Main\EventResult(
			Main\EventResult::SUCCESS,
			$result,
			'sale'
		);
	}
}