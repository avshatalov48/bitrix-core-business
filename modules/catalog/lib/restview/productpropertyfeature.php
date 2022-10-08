<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class ProductPropertyFeature extends Base
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
					Attributes::READONLY,
				],
			],
			'PROPERTY_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'MODULE_ID' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'FEATURE_ID' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'IS_ENABLED' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function internalizeArguments($name, $arguments): array
	{
		if ($name !== 'getavailablefeaturesbyproperty')
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	/**
	 * @inheritDoc
	 */
	public function externalizeResult($name, $fields): array
	{
		if ($name !== 'getavailablefeaturesbyproperty')
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}
}
