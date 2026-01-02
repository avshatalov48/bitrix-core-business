<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\PropertyTable;
use CIBlockProperty;
use Closure;

final class UserTypePropertyFieldAssembler extends BaseFieldAssembler
{
	public function __construct(int $iblockId, array $customEditableColumnIds)
	{
		parent::__construct($iblockId, $customEditableColumnIds);

		$this->preloadResources();
	}

	/**
	 * Preload resources.
	 *
	 * It is necessary for correct display in case inline edit.
	 *
	 * @return void
	 */
	private function preloadResources(): void
	{
		global $APPLICATION;

		$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
	}

	protected function getPropertyFilter(): array
	{
		return [
			'!USER_TYPE' => null,
		];
	}

	protected function validateProperty(array $property): ?array
	{
		$property['USER_TYPE_SETTINGS'] = $property['USER_TYPE_SETTINGS_LIST'];
		$property['PROPERTY_USER_TYPE'] = CIBlockProperty::GetUserType($property['USER_TYPE']);

		if (
			$property['PROPERTY_TYPE'] === PropertyTable::TYPE_ELEMENT
			|| $property['PROPERTY_TYPE'] === PropertyTable::TYPE_SECTION
		)
		{
			$property['LINK_IBLOCK_ID'] = (int)$property['LINK_IBLOCK_ID'];
			if ($property['LINK_IBLOCK_ID'] <= 0)
			{
				$property['LINK_IBLOCK_ID'] = null;
			}
		}

		// TODO: remove this hack after refactoring \CIBlockPropertyEmployee::GetPublicEditHTMLMulty and \CIBlockPropertyEmployee::GetPublicEditHTML
		if (
			$property['PROPERTY_TYPE'] === PropertyTable::TYPE_STRING
			&& $property['USER_TYPE'] === PropertyTable::USER_TYPE_EMPLOYEE
		)
		{
			$property['SETTINGS'] ??= [];
			$property['SETTINGS']['USE_ENTITY_SELECTOR'] = true;
		}

		return $property;
	}

	protected function prepareRow(array $row): array
	{
		if (!self::isElementRow($row))
		{
			return $row;
		}

		$columnIds = $this->getColumnIds();
		if (empty($columnIds))
		{
			return $row;
		}

		$rowId = RowType::getIndex(self::getRowType($row), (string)($row['data']['ID'] ?? ''));

		$row['columns'] ??= [];

		/*
		foreach ($this->getPropertiesWithUserType() as $propertyId => $property)
		{
			$columnId = ElementPropertyProvider::getColumnIdByPropertyId($propertyId);
			$value = $row['data'][$columnId];

			// view
			$viewValue = $this->getColumnValue($columnId, $property, $value);
			if (isset($viewValue))
			{
				$row['columns'][$columnId] = is_array($viewValue) ? implode(' / ', $viewValue) : $viewValue;
			}

			// edit custom
			$isCustom = in_array($columnId, $this->customEditableColumnIds, false);
			if ($isCustom)
			{
				$row['data']['~' . $columnId] = $this->getEditValue($columnId, $property, $value);
			}
		}
		*/

		foreach ($columnIds as $columnId)
		{
			$property = $this->properties[$columnId];
			$value = $row['data'][$columnId] ?? null;

			// view
			$viewValue = $this->getColumnValue($rowId, $columnId, $property, $value);
			if (isset($viewValue))
			{
				$row['columns'][$columnId] = is_array($viewValue) ? implode(' / ', $viewValue) : $viewValue;
			}

			// edit custom
			if ($this->isCustomEditable($columnId))
			{
				$row['data']['~' . $columnId] = $this->getEditValue($rowId, $columnId, $property, $value);
			}
		}

		return $row;
	}

	private function renderUserTypeFunction(Closure $callback, string $rowId, string $name, $value, array $property)
	{
		return call_user_func_array(
			$callback,
			[
				$property,
				$value,
				[
					'ROW_ID' => $rowId,
					'FIELD_NAME' => $name,
					'GRID' => 'PUBLIC',
					'VALUE' => $name . '[VALUE]',
					'DESCRIPTION' => $name . '[DESCRIPTION]',
				],
			]
		);
	}

	private function getColumnValue(string $rowId, string $columnId, array $property, $value)
	{
		if ($value === null)
		{
			return null;
		}
		if (isset($property['PROPERTY_USER_TYPE']['GetPublicViewHTML']))
		{
			if ($property['MULTIPLE'] === 'Y')
			{
				$tmp = [];
				foreach ($value as $i => $valueItem)
				{
					$tmp[] = $this->renderUserTypeFunction(
						Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicViewHTML']),
						$rowId,
						$columnId . "[n{$i}]",
						$valueItem,
						$property
					);
				}

				$separator = '';

				// TODO: replace this hack (custom separator for some types of properties)
				if (in_array(
						$property['USER_TYPE'],
						[
							PropertyTable::USER_TYPE_DATE,
							PropertyTable::USER_TYPE_DATETIME,
						]
				))
				{
					$separator = ', ';
				}
				elseif ($property['USER_TYPE'] === PropertyTable::USER_TYPE_DIRECTORY)
				{
					$separator = ' / ';
				}

				return join($separator, $tmp);
			}
			else
			{
				return $this->renderUserTypeFunction(
					Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicViewHTML']),
					$rowId,
					$columnId,
					$value,
					$property
				);
			}
		}

		return null;
	}

	private function getEditValue(string $rowId, string $columnId, array $property, $value)
	{
		if ($property['MULTIPLE'] === 'Y')
		{
			if (isset($property['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty']))
			{
				return $this->renderUserTypeFunction(
					Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty']),
					$rowId,
					$columnId,
					$value,
					$property
				);
			}
			elseif (isset($property['PROPERTY_USER_TYPE']['GetPublicEditHTML']))
			{
				$tmp = [];
				foreach ($value as $i => $valueItem)
				{
					$tmp[] = $this->renderUserTypeFunction(
						Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicEditHTML']),
						$rowId,
						$columnId . "[n{$i}]",
						$valueItem,
						$property
					);
				}

				return join('', $tmp);
			}
		}
		elseif (isset($property['PROPERTY_USER_TYPE']['GetPublicEditHTML']))
		{
			return $this->renderUserTypeFunction(
				Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicEditHTML']),
				$rowId,
				$columnId,
				$value,
				$property
			);
		}

		return null;
	}
}
