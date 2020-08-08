<?php
namespace Bitrix\Sale\PaySystem\Internals\Analytics;

use Bitrix\Sale\Internals\Analytics;

/**
 * Class Agent
 * @package Bitrix\Sale\Cashbox\Internals\Analytics
 */
final class Agent extends Analytics\Agent
{
	protected static function getProviderCode(): string
	{
		return Provider::getCode();
	}
}
