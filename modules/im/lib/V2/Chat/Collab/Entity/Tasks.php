<?php

namespace Bitrix\Im\V2\Chat\Collab\Entity;

use Bitrix\Im\V2\Chat\Collab\Entity;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Link\LinkType;
use Bitrix\Tasks\Internals\Counter;
use Bitrix\Tasks\Internals\Counter\CounterDictionary;

class Tasks extends Entity
{
	public static function getRestEntityName(): string
	{
		return 'tasks';
	}

	public function getCounterInternal(): int
	{
		return Counter::getInstance($this->getContext()->getUserId())
			->get(CounterDictionary::COUNTER_MEMBER_TOTAL, $this->groupId)
		;
	}

	protected function getLinkType(): LinkType
	{
		return LinkType::Tasks;
	}

	public static function isAvailable(): bool
	{
		return Loader::includeModule('tasks');
	}
}