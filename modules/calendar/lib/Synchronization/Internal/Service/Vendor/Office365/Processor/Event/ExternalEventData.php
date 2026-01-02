<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor\Event;

use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;

class ExternalEventData extends EventData
{
	public function __construct(EventConnection $eventConnection, private readonly string $action = 'save')
	{
		parent::__construct($eventConnection);
	}

	public function getAction(): string
	{
		return $this->action;
	}
}
