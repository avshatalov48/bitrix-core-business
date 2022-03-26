<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

class PersonType extends Base
{
	public function getFields()
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'LID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			]
		];
	}
}