<?php

namespace Bitrix\Im\V2\Integration\Sign;

use Bitrix\Main\Loader;
use Bitrix\Sign\Config\User;

class DocumentSign
{
	public static function isAvailable(): bool
	{
		if (!Loader::includeModule('sign'))
		{
			return false;
		}

		return User::instance()->isB2bCreateDocumentAvailableForCurrentUser();
	}
}
