<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class PriceTypeLang extends Base
{
	public function getFields()
	{
		return [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::IMMUTABLE,
				],
			],
			'CATALOG_GROUP_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'LANG' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'NAME'=>[
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			]
		];
	}

	/**
	 * @inheritDoc
	 */
	public function internalizeArguments($name, $arguments): array
	{
		if ($name !== 'getlanguages')
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
		if ($name !== 'getlanguages')
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}
}
