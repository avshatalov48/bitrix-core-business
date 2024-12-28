<?php

namespace Bitrix\Im\V2\Chat\Collab\Entity;

use Bitrix\Im\V2\Chat\Collab\Entity;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Main\Loader;

class Factory
{
	use ContextCustomer;

	private int $groupId;

	public function __construct(int $groupId)
	{
		$this->groupId = $groupId;
	}

	/**
	 * @return Entity[]
	 */
	public function getEntities(): array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return [];
		}

		return [
			new Tasks($this->groupId),
			new Files($this->groupId),
			new Calendar($this->groupId),
		];
	}
}