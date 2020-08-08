<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Sale\Result;

interface ITestConnection
{
	/**
	 * @return Result
	 */
	public function testConnection();
}

