<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\Event;

use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;

class ExternalEventData extends EventData
{
	public function __construct(public readonly EventResponse $item, EventConnection $eventConnection)
	{
		parent::__construct($eventConnection);
	}

	public function getAction(): ?string
	{
		return $this->item->getAction();
	}
}
