<?php

namespace Bitrix\Im\Update;

use Bitrix\Main\Update\Stepper;

final class DeleteTSMessageParam extends Stepper
{
	function execute(array &$option)
	{
		return self::FINISH_EXECUTION;
	}
}