<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;
use Bitrix\Rest\Integration\View\Base;

final class ProductPropertySection extends Base
{
	/**
	 * @return array[]
	 */
	public function getFields()
	{
		return [
			'PROPERTY_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'SMART_FILTER' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'DISPLAY_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'DISPLAY_EXPANDED' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'FILTER_HINT' => [
				'TYPE' => DataType::TYPE_STRING,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function internalizeArguments($name, $arguments): array
	{
		if ($name === 'set')
		{
			$fields = $arguments['fields'];
			if (!empty($fields))
			{
				$arguments['fields'] = $this->internalizeFieldsUpdate($fields);
			}
		}
		else
		{
			parent::internalizeArguments($name, $arguments);
		}

		return $arguments;
	}

	/**
	 * @inheritDoc
	 */
	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if ($name === 'set')
		{
			if (isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if (!empty($fields))
				{
					$arguments['fields'] = $this->convertKeysToSnakeCaseFields($fields);
				}
			}
		}
		else
		{
			$arguments = parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	/**
	 * @inheritDoc
	 */
	public function externalizeResult($name, $fields): array
	{
		if ($name !== 'set')
		{
			parent::externalizeResult($name, $fields);
		}

		return $fields;
	}
}
