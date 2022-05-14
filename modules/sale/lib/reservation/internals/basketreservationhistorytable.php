<?php

namespace Bitrix\Sale\Reservation\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

class BasketReservationHistoryTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_basket_reservation_history';
	}

	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('RESERVATION_ID'))
				->configureRequired()
			,
			(new DatetimeField('DATE_RESERVE'))
				->configureDefaultValue(new DateTime())
				->configureRequired()
			,
			(new FloatField('QUANTITY'))
				->configureRequired()
			,
			// refs
			new Reference('RESERVATION', BasketReservationTable::class, Join::on('this.RESERVATION_ID', 'ref.ID')),
		];
	}
}