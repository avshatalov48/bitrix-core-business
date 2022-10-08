<?php

namespace Bitrix\Calendar\Sync\Google\Builders;

use Bitrix\Calendar\Core\Event\Event;

class BuilderEventFromExternalEvent implements \Bitrix\Calendar\Core\Builders\Builder
{
	private array $externalEvent;

	public function __construct(array $externalEvent)
	{
		$this->externalEvent = $externalEvent;
	}

	public function build()
	{
		return (new Event())
			->set
		;
	}
}
