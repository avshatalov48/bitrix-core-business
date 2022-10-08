<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class Document extends Base
{
	/**
	 * Returns entity fields.
	 *
	 * @return array
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
			'DOC_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::IMMUTABLE,
					Attributes::REQUIRED,
				],
			],
			'SITE_ID' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'CONTRACTOR_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'RESPONSIBLE_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED_ADD,
				],
			],
			'DATE_MODIFY' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'DATE_CREATE' => [
				'TYPE' => DataType::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::IMMUTABLE,
				],
			],
			'CREATED_BY' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::IMMUTABLE,
				],
			],
			'MODIFIED_BY' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'CURRENCY' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
					Attributes::IMMUTABLE,
				],
			],
			'STATUS' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'DATE_STATUS' => [
				'TYPE' => DataType::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'DATE_DOCUMENT' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'STATUS_BY' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'TOTAL' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'COMMENTARY' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'TITLE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function internalizeArguments($name, $arguments): array
	{
		$name = mb_strtolower($name);
		if (!in_array($name, ['fields', 'conductlist', 'cancellist', 'deletelist', 'confirm', 'unconfirm', 'conduct', 'cancel'], true))
		{
			return parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}


	/**
	 * @inheritDoc
	 */
	public function externalizeResult($name, $fields): array
	{
		$name = mb_strtolower($name);
		if (!in_array($name, ['fields', 'conductlist', 'cancellist', 'deletelist', 'confirm', 'unconfirm', 'conduct', 'cancel'], true))
		{
			return parent::externalizeResult($name, $fields);
		}

		return $fields;
	}
}
