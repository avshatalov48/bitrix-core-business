<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Managers\ServiceBase;

abstract class AbstractManager extends ServiceBase
{
	/**
	 * @return string
	 */
	protected function initServiceName(): string
	{
		return Helper::ACCOUNT_TYPE;
	}
}
