<?php

namespace Bitrix\Sale\Cashbox\Errors;

use Bitrix\Main;

class Warning extends Main\Error
{
	const TYPE = 'WARNING';
	const LEVEL_TRACE = 2;
}