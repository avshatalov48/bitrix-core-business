<?php

namespace Bitrix\Im\V2\Chat\Collab\Entity;

use Bitrix\Im\V2\Chat\Collab\Entity;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Link\LinkType;

class Files extends Entity
{

	public function getCounterInternal(): int
	{
		return 0;
	}

	protected function getLinkType(): LinkType
	{
		return LinkType::Disk;
	}

	public static function isAvailable(): bool
	{
		return Loader::includeModule('disk');
	}

	public static function getRestEntityName(): string
	{
		return 'files';
	}
}