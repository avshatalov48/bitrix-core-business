<?php

namespace Bitrix\Calendar\Integration\Intranet;

use Bitrix\Intranet\UserAbsence;
use Bitrix\Main\Loader;

class Absence
{
	public function cleanCache(): void
	{
		if (Loader::includeModule('intranet'))
		{
			UserAbsence::cleanCache();
		}
	}
}