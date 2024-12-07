<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class StoreProduct extends Base
{
	public function getFields()
	{
		return [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'PRODUCT_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'AMOUNT' => [
				'TYPE' => DataType::TYPE_FLOAT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'STORE_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'QUANTITY_RESERVED' => [
				'TYPE' => DataType::TYPE_FLOAT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
		];
	}

	public function internalizeArguments($name, $arguments): array
	{
		if ($name === 'bulksave')
		{
			return $arguments;
		}

		return parent::internalizeArguments($name, $arguments);
	}

	public function externalizeResult($name, $fields): array
	{
		if ($name === 'bulksave')
		{
			return $fields;
		}

		return parent::externalizeResult($name, $fields);
	}
}
