<?php


namespace Bitrix\Catalog\RestView;


use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Extra extends Base
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
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			],
			'PERCENTAGE'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED
				]
			]
		];
	}
}