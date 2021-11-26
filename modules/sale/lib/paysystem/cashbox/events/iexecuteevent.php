<?php

namespace Bitrix\Sale\PaySystem\Cashbox\Events;

use Bitrix\Sale;

/**
 * Interface IExecuteEvent
 * @package Bitrix\Sale\PaySystem\Cashbox\Events
 */
interface IExecuteEvent
{
	public function executeEvent(): Sale\Result;
}
