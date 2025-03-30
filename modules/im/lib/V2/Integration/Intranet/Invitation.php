<?php

namespace Bitrix\Im\V2\Integration\Intranet;

use Bitrix\Main\Loader;

class Invitation
{
	public static function isAvailable(): bool
	{
		return
			Loader::includeModule('intranet')
			&& \Bitrix\Intranet\Invitation::canCurrentUserInvite()
		;
	}
}