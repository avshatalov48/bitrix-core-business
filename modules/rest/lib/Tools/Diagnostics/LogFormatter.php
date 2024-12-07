<?php

namespace Bitrix\Rest\Tools\Diagnostics;

use Bitrix\Main;
use Bitrix\Rest\LogTable;

class LogFormatter extends Main\Diag\LogFormatter
{
	public function format($message, array $context = []): string
	{
		LogTable::filterResponseData($context);

		return parent::format($message, $context);
	}
}