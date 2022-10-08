<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\Integration\View\DataType;

final class Measure extends Base
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
			'CODE'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED_ADD
				]
			],
			'MEASURE_TITLE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED_ADD
				]
			],
			'SYMBOL'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'SYMBOL_INTL'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'SYMBOL_LETTER_INTL'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'IS_DEFAULT'=>[
				'TYPE'=>DataType::TYPE_CHAR,
			]
		];
	}
}