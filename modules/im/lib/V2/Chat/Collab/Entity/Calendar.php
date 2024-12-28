<?php

namespace Bitrix\Im\V2\Chat\Collab\Entity;

use Bitrix\Calendar\Internals\Counter;
use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Im\V2\Chat\Collab\Entity;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Link\LinkType;

class Calendar extends Entity
{
	public function getCounterInternal(): int
	{
		return Counter::getInstance($this->getContext()->getUserId())
			->get(CounterDictionary::COUNTER_GROUP_INVITES, $this->groupId)
		;
	}

	protected function getLinkType(): LinkType
	{
		return LinkType::Calendar;
	}

	public static function isAvailable(): bool
	{
		return Loader::includeModule('calendar');
	}

	public static function getRestEntityName(): string
	{
		return 'calendar';
	}
}