<?php

namespace Bitrix\Sale\Rest\Entity;

use Bitrix\Sale\Rest\Attributes;

class BasketReservation extends Base
{
	public function getFields()
	{
		return [
			'ID' => [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::ReadOnly,
				],
			],
			'BASKET_ID' => [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::Required,
					Attributes::Immutable,
				]
			],
			'STORE_ID' => [
				'TYPE' => self::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::Required,
				]
			],
			'QUANTITY' => [
				'TYPE' => self::TYPE_FLOAT,
				'ATTRIBUTES' => [
					Attributes::Required,
				]
			],
			'DATE_RESERVE' => [
				'TYPE' => self::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::Required,
				]
			],
			'DATE_RESERVE_END' => [
				'TYPE' => self::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::Required,
				]
			],
			'RESERVED_BY' => [
				'TYPE' => self::TYPE_INT,
			],
		];
	}
}
