<?php

namespace Bitrix\Sale\PaySystem\Cashbox;

/**
 * Interface ISupportPrintCheck
 * @package Bitrix\Sale\PaySystem\Cashbox
 */
interface ISupportPrintCheck
{
	public static function getCashboxClass(): string;
}
