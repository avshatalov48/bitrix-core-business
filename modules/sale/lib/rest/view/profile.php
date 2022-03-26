<?php

namespace Bitrix\Sale\Rest\View;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Profile extends Base
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
			'USER_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'PERSON_TYPE_ID'=>[
				'ATTRIBUTES'=>[
					Attributes::REQUIRED,
					Attributes::IMMUTABLE
				]
			],
			'DATE_UPDATE'=>[
				'TYPE'=>DataType::TYPE_DATETIME
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING
			],
			'VERSIO_1C'=>[
				'TYPE'=>DataType::TYPE_STRING
			]
		];
	}
}
