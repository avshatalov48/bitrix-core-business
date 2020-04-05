<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Vat extends Base
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
			'TIMESTAMP_X'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_CHAR
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'RATE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			]
		];
	}

}