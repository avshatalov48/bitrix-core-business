<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Relation\Builder\Entity;

use Bitrix\Booking\Entity\Booking\Booking;

class EntityBuilderFromBooking extends EntityBuilder
{
	public function __construct(private readonly Booking $booking)
	{}

	protected function getEntityId(): int
	{
		return (int)$this->booking->getId();
	}

	protected function getEntityType(): string
	{
		return 'booking';
	}

	protected function getLink(): string
	{
		return '';
	}
}
