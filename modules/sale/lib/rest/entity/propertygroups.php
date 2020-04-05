<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class PropertyGroups extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'PERSON_TYPE_ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_INT
			]
		];
	}
}