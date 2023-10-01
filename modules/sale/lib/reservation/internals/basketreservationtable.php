<?php

namespace Bitrix\Sale\Reservation\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Sale\Internals\BasketTable;

/**
 * Class BasketReservationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_BasketReservation_Query query()
 * @method static EO_BasketReservation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_BasketReservation_Result getById($id)
 * @method static EO_BasketReservation_Result getList(array $parameters = [])
 * @method static EO_BasketReservation_Entity getEntity()
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservation createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservation_Collection createCollection()
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservation wakeUpObject($row)
 * @method static \Bitrix\Sale\Reservation\Internals\EO_BasketReservation_Collection wakeUpCollection($rows)
 */
class BasketReservationTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_sale_basket_reservation';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			],
			'QUANTITY' => [
				'data_type' => 'float',
				'required' => true
			],
			'DATE_RESERVE' => [
				'data_type' => 'datetime',
				'required' => true
			],
			'DATE_RESERVE_END' => [
				'data_type' => 'datetime',
				'required' => true,
			],
			'RESERVED_BY'  => [
				'data_type' => 'integer',
			],
			'BASKET_ID' => [
				'data_type' => 'integer',
				'required' => true
			],
			'STORE_ID' => [
				'data_type' => 'integer',
			],
			// refs
			new Reference('BASKET', BasketTable::class, Join::on('this.BASKET_ID', 'ref.ID')),
		];
	}
}