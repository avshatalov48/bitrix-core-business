<?php
namespace Bitrix\MessageService\Sender\Traits;

use Bitrix\Main\Loader;
use Bitrix\MessageService\Sender\Util;

trait RussianProvider
{
	public static function isSupported(): bool
	{
		return in_array(Util::getPortalZone(), ['ru', 'kz', 'by']);
	}
}