<?php
namespace Bitrix\Calendar\Integration\UI\EntitySelector\Attendee;

use Bitrix\Calendar\Sharing;
use Bitrix\UI\EntitySelector\BaseFilter;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;

class Filter extends BaseFilter
{
	public function __construct(array $options)
	{
		parent::__construct();
		$this->options = $options;
	}

	public function isAvailable(): bool
	{
		return $GLOBALS['USER']->isAuthorized();
	}

	public function apply(array $items, Dialog $dialog): void
	{
		$eventId = (int)$this->getOption('eventId', 0);
		$isSharing = $this->getOption('isSharingEvent', false);
		if ($eventId > 0 && $isSharing)
		{
			/** @var Sharing\Link\EventLink $eventLink */
			$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
			if ($eventLink)
			{
				foreach ($items as $item)
				{
					if (!($item instanceof Item))
					{
						continue;
					}
					if ($item->getId() === $eventLink->getOwnerId() || $item->getId() === $eventLink->getHostId())
					{
						$item->setDeselectable(false);
					}
				}
			}
		}
	}
}