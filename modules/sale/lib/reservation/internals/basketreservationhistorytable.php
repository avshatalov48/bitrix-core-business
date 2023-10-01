<?php

namespace Bitrix\Sale\Reservation\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

/**
 * Class BasketReservationHistoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BasketReservationHistory_Query query()
 * @method static EO_BasketReservationHistory_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_BasketReservationHistory_Result getById($id)
 * @method static EO_BasketReservationHistory_Result getList(array $parameters = [])
 * @method static EO_BasketReservationHistory_Entity getEntity()
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservationHistory createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservationHistory_Collection createCollection()
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservationHistory wakeUpObject($row)
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservationHistory_Collection wakeUpCollection($rows)
 */
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