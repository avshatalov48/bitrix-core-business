<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class PropertyVariant extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'ORDER_PROPS_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required
				]
			],
			'VALUE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required
				]
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_INT
			],
			'DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XNL_ID'=>[
				'TYPE'=>self::TYPE_STRING
			]
		];
	}
}