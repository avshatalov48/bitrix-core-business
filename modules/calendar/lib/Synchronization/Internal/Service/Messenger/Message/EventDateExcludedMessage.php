<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Messenger\Message;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Main\Messenger\Entity\MessageInterface;
use Bitrix\Main\ObjectException;

class EventDateExcludedMessage extends EventMessage
{
	private Date $excludedDate;

	/**
	 * @throws ObjectException
	 */
	public function __construct(int $eventId, string $excludedDate)
	{
		parent::__construct($eventId);

		$this->excludedDate = new Date(\Bitrix\Main\Type\Date::createFromText($excludedDate));
	}

	public function jsonSerialize(): mixed
	{
		return [
			'eventId' => $this->eventId,
			'excludedDate' => $this->excludedDate->toString(),
		];
	}

	public function getExcludedDate(): Date
	{
		return $this->excludedDate;
	}

	public static function createFromData(array $data): MessageInterface
	{
		return new static($data['eventId'], $data['excludedDate']);
	}
}
