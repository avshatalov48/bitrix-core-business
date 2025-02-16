<?php

namespace Bitrix\Catalog\v2\Internal;

use Bitrix\Catalog\Model\Product;
use Bitrix\Catalog\v2\Event\Event;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\InheritedProperty\ElementValues;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

final class ProductInternalService
{
	private Result $result;

	public function __construct(
		private readonly bool $shouldRegisterEvents = false,
	)
	{
	}

	public function isShouldRegisterEvents(): bool
	{
		return $this->shouldRegisterEvents;
	}

	public function update(int $id, array $fields): Result
	{
		$this->result = new Result();

		$propertyFields = $fields['PROPERTY_VALUES'] ?? [];

		$elementFields = ProductInternalService::prepareElementFields($fields);

		$elementFields = !empty($propertyFields)
			? array_merge($elementFields, ['PROPERTY_VALUES' => $propertyFields])
			: $elementFields
		;

		if (!empty($elementFields))
		{
			$element = new \CIBlockElement();
			$res = $element->update($id, $elementFields);

			if (!$res)
			{
				$this->result->addError(new Error($element->getLastError()));
			}
		}

		if ($this->result->isSuccess())
		{
			$productFields = ProductInternalService::prepareProductFields($fields);

			if (!empty($productFields))
			{
				$res = Product::update($id, $productFields);

				if (!$res->isSuccess())
				{
					$this->result->addErrors($res->getErrors());
				}
			}
		}

		$this->processAfterUpdate($id, $fields);

		return $this->result;
	}

	private function processAfterUpdate(int $id, array $fields): void
	{
		if ($this->result->isSuccess())
		{
			$ipropValues = new ElementValues(
				\CIBlockElement::GetIBlockByID($id),
				$id
			);

			$ipropValues->clearValues();

			unset($ipropValues);
		}

		if ($this->isShouldRegisterEvents())
		{
			Event::send(
				Event::ENTITY_PRODUCT,
				Event::METHOD_UPDATE,
				Event::STAGE_AFTER,
				[
					'id' => $id,
				],
			);
		}
	}

	public function add(array $fields): Result
	{
		$this->result = new Result();

		$propertyFields = $fields['PROPERTY_VALUES'] ?? [];

		$elementFields = ProductInternalService::prepareElementFields($fields);

		$elementFields = !empty($propertyFields)
			? array_merge($elementFields, ['PROPERTY_VALUES' => $propertyFields])
			: $elementFields
		;

		if (!empty($elementFields))
		{
			$element = new \CIBlockElement();
			$id = $element->add($elementFields);

			if ($id)
			{
				$this->result->setData(['ID' => $id]);
			}
			else
			{
				$this->result->addError(new Error($element->getLastError()));
			}
		}

		if ($this->result->isSuccess())
		{
			$productFields = ProductInternalService::prepareProductFields($fields);

			$productFields['ID'] = $this->result->getData()['ID'];
			$res = Product::add([
				'fields' => $productFields,
				'external_fields' => [
					'IBLOCK_ID' => $elementFields['IBLOCK_ID'],
				],
			]);

			if (!$res->isSuccess())
			{
				$this->result->addErrors($res->getErrors());
			}

		}

		return $this->result;
	}

	public static function prepareElementFields(array $fields): array
	{
		if (array_key_exists('ACTIVE_FROM', $fields) && $fields['ACTIVE_FROM'] === null)
		{
			$fields['ACTIVE_FROM'] = false;
		}

		if (array_key_exists('ACTIVE_TO', $fields) && $fields['ACTIVE_TO'] === null)
		{
			$fields['ACTIVE_TO'] = false;
		}

		if (!array_key_exists('MODIFIED_BY', $fields))
		{
			global $USER;
			if (isset($USER) && $USER instanceof \CUser)
			{
				$fields['MODIFIED_BY'] = $USER->getID();
			}
		}

		return array_intersect_key($fields, ElementTable::getMap());
	}

	public static function prepareProductFields(array $fields): array
	{
		$catalogFields = array_intersect_key(
			$fields,
			array_fill_keys(
				Product::getTabletFieldNames(Product::FIELDS_ALL),
				true
			)
		);

		if (isset($catalogFields['TIMESTAMP_X']))
		{
			$catalogFields['TIMESTAMP_X'] = new DateTime($catalogFields['TIMESTAMP_X']);
		}

		if (isset($catalogFields['TYPE']))
		{
			$catalogFields['TYPE'] = (int)$catalogFields['TYPE'];
		}

		return $catalogFields;
	}
}