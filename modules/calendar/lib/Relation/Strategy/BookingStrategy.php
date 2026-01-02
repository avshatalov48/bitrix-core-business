<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Relation\Strategy;

use Bitrix\Booking\Entity\ExternalData\ItemType\CalendarEventItemType;
use Bitrix\Booking\Provider\BookingProvider;
use Bitrix\Booking\Provider\Params\Booking\BookingFilter;
use Bitrix\Booking\Provider\Params\GridParams;
use Bitrix\Calendar\Relation\Builder\Entity\EntityBuilderFromBooking;
use Bitrix\Calendar\Relation\Builder\Owner\OwnerBuilder;
use Bitrix\Calendar\Relation\Item\Relation;
use Bitrix\Calendar\Relation\Exception\RelationException;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\CurrentUser;

class BookingStrategy extends RelationStrategy
{
	/**
	 * @inheritdoc
	 */
	public function getRelation(): Relation
	{
		if (!Loader::includeModule('booking'))
		{
			throw new RelationException('Booking module not found');
		}

		$eventId = $this->event->getId();

		$relation = new Relation($eventId);

		$booking = (new BookingProvider())->getList(
			new GridParams(
				limit: 1,
				filter: new BookingFilter([
					'EXTERNAL_DATA_ITEM' => (new CalendarEventItemType())->createItem()->setValue((string)$eventId)
				]),
			),
			(int)CurrentUser::get()->getId(),
		)->getFirstCollectionItem();

		if (!$booking)
		{
			throw new RelationException('Booking not found');
		}

		$relation
			->setEntity((new EntityBuilderFromBooking($booking))->build())
			->setOwner(
				(new OwnerBuilder($booking->getCreatedBy()))->build()
			)
		;

		return $relation;
	}
}
