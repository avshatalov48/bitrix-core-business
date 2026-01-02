<?php

namespace Bitrix\Im\V2\Integration\Intranet;

use Bitrix\Intranet\Public\Provider\Portal\LanguageProvider;
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

	public static function isChangeLanguageAvailable(): bool
	{
		return
			Loader::includeModule('intranet')
			&& (new LanguageProvider())->isLanguageIdChangeAvailable()
		;
	}
}
