<?php

namespace Bitrix\Calendar\Internals\Counter\Provider;

use Bitrix\Calendar\Internals\Counter\CounterDictionary;
use Bitrix\Calendar\Internals\Counter\State\State;

class OpenEvent implements Base
{
	private int $entityId;
	private State $state;

	public function __construct(State $state, int $entityId)
	{
		$this->entityId = $entityId;
		$this->state = $state;
	}

	public function getValue(): int
	{
		// get by selected category
		if ($this->entityId)
		{
			return $this->state->get(CounterDictionary::META_PROP_OPEN_EVENTS)['category'][$this->entityId] ?? 0;
		}

		// get sum of all categories
		return $this->state->get(CounterDictionary::META_PROP_OPEN_EVENTS)['total'] ?? 0;
	}
}
