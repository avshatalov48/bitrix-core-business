<?php

namespace Bitrix\Socialnetwork\Integration\Bitrix24;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class Portal
{
	public function getCreationDateTime(): ?DateTime
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return null;
		}

		$createTime = (int)(\CBitrix24::getCreateTime());

		if ($createTime <= 0)
		{
			return null;
		}

		return DateTime::createFromTimestamp($createTime);
	}
}