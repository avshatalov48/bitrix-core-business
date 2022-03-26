<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

class PropertyGroup extends Base
{
	public function getFields(): array
	{
		return [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY]
			],
			'PERSON_TYPE_ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED]
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_INT
			]
		];
	}
}