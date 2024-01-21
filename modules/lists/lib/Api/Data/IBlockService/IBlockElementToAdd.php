<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

use Bitrix\Lists\Api\Data\Data;
use Bitrix\Lists\Api\Request\IBlockService\AddIBlockElementRequest;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class IBlockElementToAdd extends Data
{
	private int $iBlockId;
	private int $sectionId;
	private array $values;
	private int $createdBy;

	/**
	 * @param int $iBlockId
	 * @param int $sectionId
	 * @param array $values
	 * @param int $createdBy
	 */
	private function __construct(
		int $iBlockId,
		int $sectionId,
		array $values,
		int $createdBy,
	)
	{
		$this->iBlockId = $iBlockId;
		$this->sectionId = $sectionId;
		$this->values = $values;
		$this->createdBy = $createdBy;
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

		return new self($iBlockId, $sectionId, $values, $createdBy);
	}

	/**
	 * @param array $values
	 * @param int $iBlockId
	 * @param int $sectionId
	 * @return array
	 */
	private static function validateValues(array $values, int $iBlockId, int $sectionId): array
	{
		unset($values['ID']);
		$values['IBLOCK_ID'] = $iBlockId;
		$values['IBLOCK_SECTION_ID'] = $sectionId;

		return $values;
	}

	/**
	 * @return int
	 */
	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	/**
	 * @return int
	 */
	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	/**
	 * @return array
	 */
	public function getOriginalValues(): array
	{
		return $this->values;
	}

	/**
	 * @return int
	 */
	public function getCreatedBy(): int
	{
		return $this->createdBy;
	}

	/**
	 * @param string $fieldsId
	 * @return mixed
	 */
	public function getFieldValueById(string $fieldsId): mixed
	{
		return $this->values[$fieldsId] ?? null;
	}

	public function getElementValues(array $fields, array $props): Result
	{
		$result = new Result();

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

		return $result->setData(['element' => $element]);
	}

	private function prepareFields(array $fields, Result $result): array
	{
		$prepared = [];
		foreach ($fields as $fieldId => $property)
		{
			$showAddForm = !in_array($fieldId, ['DATE_CREATE', 'TIMESTAMP_X', 'CREATED_BY', 'MODIFIED_BY']);
			if (isset($property['SETTINGS']['SHOW_ADD_FORM']))
			{
				$showAddForm = $property['SETTINGS']['SHOW_ADD_FORM'] === 'Y';
			}

			if (in_array($fieldId, ['PREVIEW_TEXT', 'DETAIL_TEXT'], true))
			{
				$useEditor = isset($property['SETTINGS']['USE_EDITOR']) && $property['SETTINGS']['USE_EDITOR'] === 'Y';
				$prepared[$fieldId . '_TYPE'] = $useEditor ? 'html' : 'text';
			}

			$prepared[$fieldId] =
				$showAddForm ? $this->getFieldValueById($fieldId) : ($property['DEFAULT_VALUE'] ?? null)
			;
		}

		return $prepared;
	}

	private function prepareProps(array $props, Result $result): array
	{
		$prepared = [];
		foreach ($props as $propId => $property)
		{
			$baseType = $property['TYPE'];
			$type = $property['PROPERTY_TYPE'];
			$isMultiple = $property['MULTIPLE'] === 'Y';

			$showAddForm =
				isset($property['SETTINGS']['SHOW_ADD_FORM']) && $property['SETTINGS']['SHOW_ADD_FORM'] === 'Y'
			;
			$defaultValue = ($property['DEFAULT_VALUE'] ?? null);
			$requestValues = $this->getFieldValueById($propId);

			if ($showAddForm && is_array($requestValues))
			{
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
							break 2;
						}
					}

					if ($type === 'S' && $baseType === 'S:HTML')
					{
						$value =
							is_array($value) && isset($value['TYPE'], $value['TEXT'])
								? $value
								: ['TYPE' => 'html', 'TEXT' => (string)$value]
						;
					}

					$values[$key] = ['VALUE' => $value];

					if (!$isMultiple)
					{
						break;
					}
				}

				$prepared[$property['ID']] = $values;
			}
			elseif ($defaultValue)
			{
				$prepared[$property['ID']] = [
					'n0' => ['VALUE' => $defaultValue],
				];
			}
		}

		return $prepared;
	}
}
