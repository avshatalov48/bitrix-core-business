<?php


namespace Bitrix\Sale\Rest\View;


use Bitrix\Main\Loader;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

class PersonType extends Base
{
	public function getFields()
	{
		$fieldList = [
			'ID'=>[
				'TYPE'=>DataType::TYPE_INT,
				'ATTRIBUTES'=>[Attributes::READONLY],
			],
			'NAME'=>[
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=>[Attributes::REQUIRED],
			],
			'CODE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'SORT'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'ACTIVE'=>[
				'TYPE'=>DataType::TYPE_STRING,
			],
			'XML_ID'=>[
				'TYPE'=>DataType::TYPE_STRING,
			]
		];

		if (!Loader::includeModule('bitrix24'))
		{
			$fieldList['LID'] = [
				'TYPE'=>DataType::TYPE_STRING,
				'ATTRIBUTES'=> [Attributes::REQUIRED],
			];
		}

		return $fieldList;
	}
}