<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Ratio extends Base
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
			'PRODUCT_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'RATIO'=>[
				'TYPE'=>DataType::TYPE_FLOAT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'IS_DEFAULT'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			]
		];
	}
}