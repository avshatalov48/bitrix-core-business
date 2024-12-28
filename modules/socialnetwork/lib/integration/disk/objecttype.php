<?php

namespace Bitrix\Socialnetwork\Integration\Disk;

use Bitrix\Disk\Internals\ObjectTable;
use Bitrix\Main\Loader;

class ObjectType
{
	public function getFileType(): ?int
	{
		if (!Loader::includeModule('disk'))
		{
			return null;
		}

		return ObjectTable::TYPE_FILE;
	}
}
