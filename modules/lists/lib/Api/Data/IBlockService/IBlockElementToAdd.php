<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

use Bitrix\Lists\Api\Request\IBlockService\AddIBlockElementRequest;
use Bitrix\Lists\Api\Response\IBlockService\IBlockElementToAddValues;
use Bitrix\Lists\Api\Response\IBlockService\IBlockElementToUpdateValues;
use Bitrix\Main\ArgumentOutOfRangeException;

final class IBlockElementToAdd extends IBlockElementToUpdate
{
	private int $createdBy;

	protected function init(): void
	{
		$this->elementId = 0;
		$this->createdBy = $this->modifiedBy;
	}

	/**
	 * @param AddIBlockElementRequest $request
	 * @return self
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest($request): self
	{
		$iBlockId = self::validateId($request->iBlockId);
		if ($iBlockId === null || $iBlockId === 0)
		{
			throw new ArgumentOutOfRangeException('iBlockId', 1, null);
		}

		$sectionId = self::validateId($request->sectionId);
		if ($sectionId === null)
		{
			throw new ArgumentOutOfRangeException('sectionId', 0, null);
		}

		$createdBy = self::validateId($request->createdByUserId);
		if ($createdBy === null || $createdBy === 0)
		{
			throw new ArgumentOutOfRangeException('createdBy', 1, null);
		}

		$values = self::validateValues($request->values, $iBlockId, $sectionId);

		return new self($createdBy, 0, $iBlockId, $sectionId, $values);
	}

	public function getCreatedBy(): int
	{
		return $this->createdBy;
	}

	public function getElementValues(array $fields, array $props): IBlockElementToAddValues
	{
		$result = new IBlockElementToAddValues();
		$result
			->setHasChangedFields(true)
			->setHasChangedProps(true)
		;

		$element = [
			'IBLOCK_ID' => $this->getIBlockId(),
			'IBLOCK_SECTION_ID' => $this->getSectionId(),
			'MODIFIED_BY' => $this->getCreatedBy(),
		];

		$preparedFields = $this->prepareFields($fields, $result);
		$element = array_merge($element, $preparedFields);
		unset($element['TIMESTAMP_X']);

		$preparedProps = $this->prepareProps($props, $result);
		if ($preparedProps)
		{
			$element['PROPERTY_VALUES'] = $preparedProps;
		}

		return $result->setElementData($element);
	}

	protected function prepareFields(array $fields, IBlockElementToUpdateValues $result): array
	{
		$prepared = [];
		foreach ($fields as $fieldId => $property)
		{
			$showAddForm = !in_array($fieldId, ['DATE_CREATE', 'TIMESTAMP_X', 'CREATED_BY', 'MODIFIED_BY']);
			if (isset($property['SETTINGS']['SHOW_ADD_FORM']))
			{
				$showAddForm = $property['SETTINGS']['SHOW_ADD_FORM'] === 'Y';
			}

			$isAddReadOnlyField = ($property['SETTINGS']['ADD_READ_ONLY_FIELD'] ?? 'N') === 'Y';

			if (in_array($fieldId, ['PREVIEW_TEXT', 'DETAIL_TEXT'], true))
			{
				$useEditor = isset($property['SETTINGS']['USE_EDITOR']) && $property['SETTINGS']['USE_EDITOR'] === 'Y';
				$prepared[$fieldId . '_TYPE'] = $useEditor ? 'html' : 'text';
			}

			$prepared[$fieldId] =
				$showAddForm && !$isAddReadOnlyField ? $this->getFieldValueById($fieldId) : ($property['DEFAULT_VALUE'] ?? null)
			;
		}

		return $prepared;
	}

	protected function prepareProps(array $props, IBlockElementToUpdateValues $result): array
	{
		$prepared = [];
		foreach ($props as $propId => $property)
		{
			$showAddForm =
				isset($property['SETTINGS']['SHOW_ADD_FORM']) && $property['SETTINGS']['SHOW_ADD_FORM'] === 'Y'
			;
			$defaultValue = ($property['DEFAULT_VALUE'] ?? null);
			$requestValues = $this->getFieldValueById($propId);

			if ($property['TYPE'] === 'N:Sequence' && !$showAddForm && empty($defaultValue))
			{
				$defaultValue = (new \CIBlockSequence($this->getIBlockId(), $property['ID']))->GetNext();
			}

			if ($showAddForm && is_array($requestValues))
			{
				$prepared[$property['ID']] = $this->preparePropValue($requestValues, $property, $result);
			}
			elseif ($defaultValue)
			{
				$prepared[$property['ID']] = ['n0' => ['VALUE' => $defaultValue]];
			}
		}

		return $prepared;
	}

	public function getChangedFieldsAfterUpdate(): array
	{
		return [];
	}
}
