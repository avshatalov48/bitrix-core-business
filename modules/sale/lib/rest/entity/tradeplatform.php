<?php


namespace Bitrix\Sale\Rest\Entity;


use Bitrix\Sale\Rest\Attributes;

class TradePlatform extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>self::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::ReadOnly]
			],
			'CODE'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'ACTIVE'=>[
				'TYPE'=>self::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'NAME'=>[
				'TYPE'=>self::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::Required]
			],
			'DESCRIPTION'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'SETTINGS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CATALOG_SECTION_TAB_CLASS_NAME'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'CLASS'=>[
				'TYPE'=>self::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>self::TYPE_STRING
			]
		];
	}
}