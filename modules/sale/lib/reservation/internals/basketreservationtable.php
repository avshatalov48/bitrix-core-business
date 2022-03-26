<?php

namespace Bitrix\Sale\Reservation\Internals;

use Bitrix\Main;

class BasketReservationTable extends Main\Entity\DataManager
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
		];
	}
}