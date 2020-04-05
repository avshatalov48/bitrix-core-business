<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Sale\Result;

interface IPrintImmediately
{
	/**
	 * @param Check $check
	 * @return Result
	 */
	public function printImmediately(Check $check);
}

