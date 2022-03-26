<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class Status extends Base
{
	public function getFields(): array
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'TYPE'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
					//Attributes::IMMUTABLE
				]
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'NOTIFY'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'COLOR'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			]
		];
	}
}