<?php

namespace Bitrix\Sale\Rest\View;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\DataType;

class ShipmentPropertyValue extends Base
{
	public function getFields()
	{
		return [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
					Attributes::IMMUTABLE,
				],
			],
			'SHIPMENT_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
					Attributes::IMMUTABLE,
				],
			],
			'SHIPMENT_PROPS_XML_ID' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				]
			],
			'NAME'=> [
				'TYPE' => DataType::TYPE_STRING,
			],
			'CODE'=> [
				'TYPE' => DataType::TYPE_STRING,
			],
			'VALUE' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
			'SHIPMENT_PROPS_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
				],
			],
		];
	}

	protected function getRewriteFields(): array
	{
		return [
			'SHIPMENT_ID' => [
				'REFERENCE_FIELD' => 'ENTITY_ID',
			],
			'SHIPMENT_PROPS_XML_ID' => [
				'REFERENCE_FIELD' => 'ORDER_PROPS.XML_ID',
			],
			'SHIPMENT_PROPS_ID' => [
				'REFERENCE_FIELD' => 'ORDER_PROPS_ID',
			],
		];
	}

	public function checkRequiredFieldsModify($fields): Result
	{
		$r = new Result();

		$propertyValues =
			(
				isset($fields['SHIPMENT']['PROPERTY_VALUES'])
				&& is_array($fields['SHIPMENT']['PROPERTY_VALUES'])
			)
				? $fields['SHIPMENT']['PROPERTY_VALUES']
				: []
		;

		foreach ($propertyValues as $k => $item)
		{
			$required = $this->checkRequiredFields(
				$item,
				$this->isNewItem($item)
					? $this->getListFieldsInfoAdd()
					: $this->getListFieldsInfoUpdate()
			);

			if (!$required->isSuccess())
			{
				$r->addError(
					new Error(
						'[propertyValues]['.$k.'] - '
						. implode(', ', $required->getErrorMessages()) . '.'
					)
				);
			}
		}

		return $r;
	}

	public function internalizeFieldsModify($fields): array
	{
		$result = [];

		if (isset($fields['SHIPMENT']['ID']))
		{
			$result['SHIPMENT']['ID'] = (int)$fields['SHIPMENT']['ID'];
		}

		if (
			isset($fields['SHIPMENT']['PROPERTY_VALUES'])
			&& is_array($fields['SHIPMENT']['PROPERTY_VALUES'])
		)
		{
			foreach ($fields['SHIPMENT']['PROPERTY_VALUES'] as $k => $item)
			{
				$result['SHIPMENT']['PROPERTY_VALUES'][$k] = $this->internalizeFields(
					$item,
					$this->isNewItem($item)
						? $this->getListFieldsInfoAdd()
						: $this->getListFieldsInfoUpdate()
				);
			}
		}

		return $result;
	}

	public function externalizeFieldsModify($fields)
	{
		return $this->externalizeListFields($fields);
	}

	private function getListFieldsInfoAdd(): array
	{
		$fields = $this->getFields();

		return $this->getListFieldInfo(
			$fields,
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::HIDDEN,
						Attributes::READONLY,
					],
					'ignoredFields' => [
						'SHIPMENT_ID',
					],
				],
			]
		);
	}

	private function getListFieldsInfoUpdate(): array
	{
		$fields = $this->getFields();

		return $this->getListFieldInfo(
			$fields,
			[
				'filter' => [
					'ignoredAttributes' => [
						Attributes::HIDDEN,
						Attributes::READONLY,
						Attributes::IMMUTABLE,
					],
				],
			]
		);
	}
}
