<?php

namespace Sale\Handlers\Delivery\Spsr;

use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Sale\Shipment;

/**
 * Class Calculator
 *
 * @package Sale\Handlers\Delivery\Spsr
 */
class Calculator
{
	/**
	 * @param Shipment $shipment
	 * @param $additional
	 * @return Result
	 */
	public static function calculate(Shipment $shipment, $additional)
	{
		return (new Result())->addError(new Error('The company no longer exists'));
	}
}
