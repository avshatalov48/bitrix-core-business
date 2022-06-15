<?php

namespace Bitrix\Sale\PaySystem\Internals\Analytics;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class EventHandler
 * @package Bitrix\Sale\PaySystem\Internals\Analytics
 * @internal
 */
final class EventHandler
{
	/**
	 * @param Main\Event $event
	 * @return void
	 */
	public static function onSaleAfterPsServiceProcessRequest(Main\Event $event): void
	{
		$parameters = $event->getParameters();

		/** @var Sale\Payment $payment */
		$payment = $parameters['payment'];
		/** @var Sale\PaySystem\ServiceResult $serviceResult */
		$serviceResult = $parameters['serviceResult'];

		if ($payment instanceof Sale\Payment && $serviceResult->isSuccess())
		{
			$provider = new Sale\PaySystem\Internals\Analytics\Provider($payment);
			(new Sale\Internals\Analytics\Storage($provider))->save();
		}
	}
}
