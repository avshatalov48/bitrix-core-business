<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class Status extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::Required,
					Attributes::Immutable
				]
			],
			'TYPE'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::Required
					//Attributes::Immutable
				]
			],
			'SORT'=>[
				'TYPE'=>self::TYPE_INT
			],
			'NOTIFY'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'COLOR'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			]
		];
	}
}