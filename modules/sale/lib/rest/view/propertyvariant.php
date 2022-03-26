<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class PropertyVariant extends Base
{
	public function getFields(): array
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'ORDER_PROPS_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'VALUE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'DESCRIPTION'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XNL_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			]
		];
	}
}