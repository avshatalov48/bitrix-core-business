<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class ProductProperty extends Base
{
	/**
	 * @return array[]
	 */
	public function getFields()
	{
		return [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY
				]
			],
			'TIMESTAMP_X' => [
				'TYPE' => DataType::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'IBLOCK_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				]
			],
			'NAME' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				]
			],
			'ACTIVE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'SORT' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'CODE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'DEFAULT_VALUE' => [
				'TYPE' => DataType::TYPE_TEXT,
			],
			'PROPERTY_TYPE' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'USER_TYPE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'ROW_COUNT' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'COL_COUNT' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'LIST_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'MULTIPLE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'XML_ID' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'MULTIPLE_CNT' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'LINK_IBLOCK_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'WITH_DESCRIPTION' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'SEARCHABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'FILTRABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'IS_REQUIRED' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'HINT' => [
				'TYPE' => DataType::TYPE_STRING,
			],
		];
	}
}
