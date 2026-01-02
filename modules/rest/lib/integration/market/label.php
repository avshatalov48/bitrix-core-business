<?php

namespace Bitrix\Rest\Integration\Market;

use Bitrix\Bitrix24\License;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

class Label
{
	public static function isRenamedMarket(): bool
	{
		return Loader::includeModule('bitrix24')
			&& License::getCurrent()->getRegion() === 'ru'
			|| Application::getInstance()->getLicense()->getRegion() === 'ru';
	}
}