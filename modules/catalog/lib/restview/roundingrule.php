<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class RoundingRule extends Base
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
			'CATALOG_GROUP_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'PRICE'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'ROUND_TYPE'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'ROUND_PRECISION'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'CREATED_BY'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'DATE_CREATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'MODIFIED_BY'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'DATE_MODIFY'=>[
				'TYPE'=>DataType::TYPE_DATETIME,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			]
		];
	}
}