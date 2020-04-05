<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Sale\Result;

interface ICheckable
{
	/**
	 * @param Check $check
	 * @return Result
	 */
	public function check(Check $check);
}

