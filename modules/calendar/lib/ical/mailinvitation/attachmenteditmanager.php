<?php

namespace Bitrix\Calendar\ICal\MailInvitation;

use Bitrix\Calendar\Internals\EventTable;

/**
 * Class AttachmentEditManager
 * @package Bitrix\Calendar\ICal\MailInvitation
 */
class AttachmentEditManager extends AttachmentManager
{
	/**
	 * AttachmentEditManager constructor.
	 * @param array $event
	 */
	public function __construct(array $event)
	{
		parent::__construct($event);
		$this->uid = $event['DAV_XML_ID'];
	}

	public function getUid(): ?string
	{
		if ($this->uid)
		{
			return $this->uid;
		}

		if ($this->event['ID'])
		{
			$eventFromDb = EventTable::getById($this->event['ID'])->fetch();

			if ($eventFromDb && $eventFromDb['DAV_XML_ID'] && $eventFromDb['DELETED'] === 'N')
			{
				$this->uid = $eventFromDb['DAV_XML_ID'];

				return $this->uid;
			}
		}

		return null;
	}
}
