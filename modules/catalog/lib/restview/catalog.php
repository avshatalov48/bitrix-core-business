<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Catalog extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'IBLOCK_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'YANDEX_EXPORT'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'SUBSCRIPTION'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'VAT_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'PRODUCT_IBLOCK_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'SKU_PROPERTY_ID'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'IBLOCK_TYPE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'LID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::READONLY
				]
			],
			'OFFERS'=>[
				'TYPE'=>DataType::TYPE_CHAR,
				'ATTRIBUTES'=>[
					Attributes::READONLY,
					Attributes::HIDDEN
				]
			]
		];
	}

	public function internalizeArguments($name, $arguments): array
	{
		if($name == 'isoffers'
		){}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}
}