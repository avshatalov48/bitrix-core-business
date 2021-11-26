<?php

namespace Bitrix\Sale\PaySystem\Cashbox;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\PaySystem\Cashbox\Events;

/**
 * Class EventHandler
 * @package Bitrix\Sale\PaySystem\Cashbox
 */
class EventHandler
{
	/**
	 * @param Main\Event $event
	 * @return Sale\Result
	 */
	public static function onBusinessValueUpdate(Main\Event $event): Sale\Result
	{
		return (new Events\UpdateCashboxesOnBusinessValueUpdate($event))->executeEvent();
	}

	/**
	 * @param Main\Event $event
	 * @return Sale\Result
	 */
	public static function onUpdatePaySystem(Main\Event $event): Sale\Result
	{
		return (new Events\ToggleCashboxesOnUpdatePaySystem($event))->executeEvent();
	}

	/**
	 * @param Sale\PaySystem\Service $service
	 * @return Sale\Result
	 */
	public static function onDeletePaySystem(Sale\PaySystem\Service $service): Sale\Result
	{
		return (new Events\DeleteCashboxesOnDeletePaySystem($service))->executeEvent();
	}

	/**
	 * @param Sale\PaySystem\Service $service
	 * @param string $kkmId
	 * @return Sale\Result
	 */
	public static function onDisabledFiscalization(Sale\PaySystem\Service $service, string $kkmId): Sale\Result
	{
		return (new Events\DeleteCashboxesOnDisabledFiscalization($service, $kkmId))->executeEvent();
	}
}
