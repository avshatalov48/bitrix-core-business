<?php
namespace Bitrix\MessageService\Sender\Traits;

use Bitrix\Main\Loader;
use Bitrix\MessageService\Sender\Util;

trait RussianProvider
{
	public static function isSupported(): bool
	{
		$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();

		return in_array($region, ['ru', 'kz', 'by']);
	}
}