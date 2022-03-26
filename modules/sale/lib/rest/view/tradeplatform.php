<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class TradePlatform extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'DESCRIPTION'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'SETTINGS'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'CATALOG_SECTION_TAB_CLASS_NAME'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'CLASS'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			]
		];
	}
}