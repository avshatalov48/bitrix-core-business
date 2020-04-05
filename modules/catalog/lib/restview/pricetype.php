<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class PriceType extends Base
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
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'BASE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'TIMESTAMP_X'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'MODIFIED_BY'=>[
				'TYPE'=>DataType::TYPE_INT
			],
			'DATE_CREATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'CREATED_BY'=>[
				'TYPE'=>DataType::TYPE_INT
			],
		];
	}
}