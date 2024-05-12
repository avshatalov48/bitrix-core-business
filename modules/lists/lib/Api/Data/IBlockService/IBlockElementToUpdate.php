<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

use Bitrix\Lists\Api\Data\Data;
use Bitrix\Lists\Api\Request\IBlockService\UpdateIBlockElementRequest;
use Bitrix\Lists\Api\Response\IBlockService\IBlockElementToUpdateValues;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class IBlockElementToUpdate extends Data
{
	protected int $modifiedBy;
	protected int $elementId;
	protected int $iBlockId;
	protected int $sectionId;
	protected array $values;
	private array $elementOldFields;
	private array $elementOldProps;
	private array $propsMap = [];
	private array $select = [];

	protected function __construct(
		int $modifiedBy,
		int $elementId,
		int $iBlockId,
		int $sectionId,
		array $values,
	)
	{
		$this->elementId = $elementId;
		$this->iBlockId = $iBlockId;
		$this->sectionId = $sectionId;
		$this->modifiedBy = $modifiedBy;
		$this->values = $values;

		$this->init();
	}

	protected function init(): void
	{
		$this->elementOldFields = $this->getElementFieldsData();
		$this->elementOldProps = $this->getElementPropsData();
	}

	/**
	 * @param UpdateIBlockElementRequest $request
	 * @return self
	 * @throws ArgumentOutOfRangeException
	 */
	public static function createFromRequest($request): self
	{
		$elementId = self::validateId($request->elementId);
		if ($elementId === null || $elementId === 0)
		{
			throw new ArgumentOutOfRangeException('elementId', 1, null);
		}

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

		$modifiedBy = self::validateId($request->modifiedByUserId);
		if ($modifiedBy === null || $modifiedBy === 0)
		{
			throw new ArgumentOutOfRangeException('modifiedBy', 1, null);
		}

		$values = self::validateValues($request->values, $iBlockId, $sectionId);

		return new self($modifiedBy, $elementId, $iBlockId, $sectionId, $values);
	}

	protected static function validateValues(array $values, int $iBlockId, int $sectionId): array
	{
		unset($values['ID']);
		$values['IBLOCK_ID'] = $iBlockId;
		$values['IBLOCK_SECTION_ID'] = $sectionId;

		return $values;
	}

	public function getElementId(): int
	{
		return $this->elementId;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	public function getOriginalValues(): array
	{
		return $this->values;
	}

	public function getFieldValueById(string $fieldsId)
	{
		return $this->values[$fieldsId] ?? null;
	}

	public function getModifiedBy(): int
	{
		return $this->modifiedBy;
	}

	public function getElementValues(array $fields, array $props): IBlockElementToUpdateValues
	{
		$result = new IBlockElementToUpdateValues();
		$result
			->setHasChangedFields(false)
			->setHasChangedProps(false)
		;

		$element = [
			'IBLOCK_ID' => $this->getIBlockId(),
			'IBLOCK_SECTION_ID' => $this->getSectionId(),
			'MODIFIED_BY' => $this->getModifiedBy(),
		];

		unset($fields['TIMESTAMP_X']);
		$preparedFields = $this->prepareFields($fields, $result);
		$element = array_merge($element, $preparedFields);

		$preparedProps = $this->prepareProps($props, $result);
		if ($preparedProps)
		{
			$element['PROPERTY_VALUES'] = $preparedProps;
		}

		return $result->setElementData($element);
	}

	protected function prepareFields(array $fields, IBlockElementToUpdateValues $result): array
	{
		$hasChangedFields = false;

		$prepared = [];
		foreach ($fields as $fieldId => $property)
		{
			if ($fieldId === 'NAME' && empty($property['SETTINGS']))
			{
				$property['SETTINGS'] = ['SHOW_EDIT_FORM' => 'Y'];
			}

			$showEditForm = isset($property['SETTINGS']['SHOW_EDIT_FORM']) && $property['SETTINGS']['SHOW_EDIT_FORM'] === 'Y';
			$isEditReadOnlyField = in_array($fieldId, ['DATE_CREATE', 'TIMESTAMP_X', 'CREATED_BY', 'MODIFIED_BY']);
			if (isset($property['SETTINGS']['EDIT_READ_ONLY_FIELD']))
			{
				$isEditReadOnlyField = $property['SETTINGS']['EDIT_READ_ONLY_FIELD'] === 'Y';
			}

			// to prevent stripping SEARCHABLE_CONTENT
			if (!$showEditForm || $isEditReadOnlyField)
			{
				if (array_key_exists($fieldId, $this->elementOldFields))
				{
					$prepared[$fieldId] = $this->elementOldFields[$fieldId];
				}

				continue;
			}

			if (in_array($fieldId, ['PREVIEW_TEXT', 'DETAIL_TEXT'], true))
			{
				$useEditor = isset($property['SETTINGS']['USE_EDITOR']) && $property['SETTINGS']['USE_EDITOR'] === 'Y';
				$prepared[$fieldId . '_TYPE'] = $useEditor ? 'html' : 'text';
			}

			$value = $this->getFieldValueById($fieldId);
			$prepared[$fieldId] = $value;

			if ($hasChangedFields === false && array_key_exists($fieldId, $this->elementOldFields))
			{
				$oldValue = $this->elementOldFields[$fieldId];
				if (!$this->isEqualAsStrings($oldValue, $value))
				{
					$hasChangedFields = true;
				}
			}
		}

		$result->setHasChangedFields($hasChangedFields);

		return $prepared;
	}

	protected function prepareProps(array $props, IBlockElementToUpdateValues $result): array
	{
		$hasChangedProps = false;
		$prepared = [];
		foreach ($props as $key => $property)
		{
			$propertyId = $property['ID'];

			$showEditForm = ($property['SETTINGS']['SHOW_EDIT_FORM'] ?? 'Y') === 'Y';
			$isEditReadOnlyField = ($property['SETTINGS']['EDIT_READ_ONLY_FIELD'] ?? 'N') === 'Y';

			// to prevent reset props
			if (!$showEditForm || $isEditReadOnlyField)
			{
				if (array_key_exists($key, $this->elementOldProps))
				{
					$prepared[$propertyId] = $this->elementOldProps[$key];
				}

				continue;
			}

			$requestValues = $this->getFieldValueById($key);
			if (is_array($requestValues))
			{
				$prepared[$propertyId] = $this->preparePropValue($requestValues, $property, $result);

				if ($hasChangedProps === false && array_key_exists($key, $this->elementOldProps))
				{
					$oldValue = $this->elementOldProps[$key];

					$newValue = array_filter($requestValues);
					if ($property['TYPE'] === 'S:HTML')
					{
						$newValue = array_map(static fn($text) => ['TYPE' => 'HTML', 'TEXT' => $text], $newValue);
						if (!$this->isEqualAsMultiArrays($newValue, $oldValue))
						{
							$hasChangedProps = true;
						}
					}
					elseif (!$this->isEqualAsArrays($newValue, $oldValue))
					{
						$hasChangedProps = true;
					}
				}
			}
		}

		$result->setHasChangedProps($hasChangedProps);

		return $prepared;
	}

	protected function preparePropValue(array $requestValues, array $property, Result $result)
	{
		$baseType = $property['TYPE'];
		$type = $property['PROPERTY_TYPE'];
		$isMultiple = $property['MULTIPLE'] === 'Y';

		$values = [];
		foreach ($requestValues as $key => $value)
		{
			if (in_array($type, ['L', 'E', 'G'], true))
			{
				if ($isMultiple)
				{
					$values[$key] = $value;

					continue;
				}

				$values = $value;

				break;
			}

			if ($type === 'N' && !empty($value))
			{
				if (is_numeric($value))
				{
					$value = (double)$value;
				}
				else
				{
					// todo: Loc
					$result->addError(new Error('incorrect number value'));

					return [];
				}
			}

			if ($type === 'S' && $baseType === 'S:HTML')
			{
				if (!is_array($value) || !isset($value['TYPE'], $value['TEXT']))
				{
					$value = ['TYPE' => 'html', 'TEXT' => is_scalar($value) ? (string)$value : ''];
				}
			}

			$values[$key] = ['VALUE' => $value];

			if (!$isMultiple)
			{
				break;
			}
		}

		return $values;
	}

	private function getElementFieldsData(): array
	{
		if (empty($this->select))
		{
			$this->select = ['ID', 'IBLOCK_SECTION_ID'];

			$list = new \CList($this->iBlockId);
			foreach ($list->GetFields() as $fieldId => $property)
			{
				if ($list->is_field($fieldId))
				{
					$this->select[] = $fieldId;
				}

				if ($fieldId === 'CREATED_BY')
				{
					$this->select[] = 'CREATED_USER_NAME';
				}

				if ($fieldId === 'MODIFIED_BY')
				{
					$this->select[] = 'USER_NAME';
				}
			}
		}

		$filter = [
			'=IBLOCK_ID' => $this->iBlockId,
			'=ID' => $this->elementId,
			'CHECK_PERMISSIONS' => false,
		];
		$iterator = \CIBlockElement::GetList([], $filter, false, [], $this->select);
		$element = $iterator->Fetch();

		return is_array($element) ? $element : [];
	}

	private function getElementPropsData(): array
	{
		$elementProperties = \CIBlockElement::GetProperty(
			$this->iBlockId, $this->elementId, 'sort', 'asc', ['ACTIVE' => 'Y']
		);

		$properties = [];
		while ($property = $elementProperties->Fetch())
		{
			$propertyId = $property['ID'];
			$propertyValueId = $property['PROPERTY_VALUE_ID'] ?? null;
			$propertyValue = $property['VALUE'] ?? null;

			$key = 'PROPERTY_' . $propertyId;
			if (!array_key_exists($key, $properties))
			{
				$properties[$key] = [];
			}

			if (isset($propertyValueId, $propertyValue))
			{
				$properties[$key][$propertyValueId] = $propertyValue;
			}

			$this->propsMap[$key] = $property['CODE'] ?? $property['ID'];
		}

		return $properties;
	}

	public function getChangedFieldsAfterUpdate(): array
	{
		$oldData = array_merge($this->elementOldFields, $this->elementOldProps);
		$newData = array_merge($this->getElementFieldsData(), $this->getElementPropsData());

		$valuesToChange = $this->getOriginalValues();
		unset($valuesToChange['ID'], $valuesToChange['IBLOCK_ID'], $valuesToChange['DATE_CREATE'], $valuesToChange['CREATED_BY']);

		$changed = [];
		foreach (array_keys($valuesToChange) as $key)
		{
			if (!array_key_exists($key, $oldData) || !array_key_exists($key, $newData))
			{
				continue;
			}

			$oldValue = $oldData[$key];
			$newValue = $newData[$key];
			if (empty($oldValue) && empty($newValue))
			{
				continue;
			}

			$modifiedKey = array_key_exists($key, $this->propsMap) ? 'PROPERTY_' . $this->propsMap[$key] : $key;

			$isArrayOldValue = is_array($oldValue);
			$isArrayNewValue = is_array($newValue);

			if (!$isArrayOldValue && !$isArrayNewValue)
			{
				if (!$this->isEqualAsStrings($oldValue, $newValue))
				{
					$changed[] = $modifiedKey;
				}

				continue;
			}

			$oldValue = $isArrayOldValue ? $oldValue : [$oldValue];
			$newValue = $isArrayNewValue ? $newValue : [$newValue];

			if (is_array(current($newValue)) || is_array(current($oldValue)))
			{
				if (!$this->isEqualAsMultiArrays($newValue, $oldValue))
				{
					$changed[] = $modifiedKey;
				}
			}
			elseif (!$this->isEqualAsArrays($newValue, $oldValue))
			{
				$changed[] = $modifiedKey;
			}
		}

		return $changed;
	}

	private function isEqualAsStrings($string1, $string2): bool
	{
		if (!is_string($string1))
		{
			$string1 =
				(is_scalar($string1) || (is_object($string1) && method_exists($string1, '__toString')))
					? (string)$string1
					: ''
			;
		}

		if (!is_string($string2))
		{
			$string2 =
				(is_scalar($string2) || (is_object($string2) && method_exists($string2, '__toString')))
					? (string)$string2
					: ''
			;
		}

		return strcmp($string1, $string2) === 0;
	}

	private function isEqualAsMultiArrays(array $value1, array $value2): bool
	{
		$oldFieldValues = [];
		$newFieldValues = [];

		foreach ($value1 as $singleValue)
		{
			if (is_array($singleValue))
			{
				if (isset($singleValue['TEXT']))
				{
					$oldFieldValues[] = $singleValue['TEXT'];
				}

				continue;
			}

			$oldFieldValues[] = $singleValue;
		}
		foreach ($value2 as $singleValue)
		{
			if (is_array($singleValue))
			{
				if (isset($singleValue['TEXT']))
				{
					$newFieldValues[] = $singleValue['TEXT'];
				}

				continue;
			}

			$newFieldValues[] = $singleValue;
		}

		return $this->isEqualAsArrays($newFieldValues, $oldFieldValues);
	}

	private function isEqualAsArrays(array $value1, array $value2): bool
	{
		$differences = array_diff($value1, $value2);
		if (!empty($differences))
		{
			return false;
		}

		$differences = array_diff($value2, $value1);

		return empty($differences);
	}
}
