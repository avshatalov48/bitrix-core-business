<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Grid\Row\FieldAssembler;
use CIBlockProperty;
use Closure;

final class UserTypePropertyFieldAssembler extends FieldAssembler
{
	private int $iblockId;
	private array $customEditableColumnIds;
	private array $properties;

	public function __construct(int $iblockId, array $customEditableColumnIds)
	{
		$this->iblockId = $iblockId;
		$this->customEditableColumnIds = $customEditableColumnIds;

		parent::__construct(
			$this->getPropertyColumnsIdsWithUserType()
		);

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

	private function getPropertyColumnsIdsWithUserType(): array
	{
		$result = [];

		$rows = PropertyTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->iblockId,
				'!USER_TYPE' => null,
			],
		]);
		foreach ($rows as $row)
		{
			$result[] = ElementPropertyProvider::getColumnIdByPropertyId($row['ID']);
		}

		return $result;
	}

	private function getPropertiesWithUserType(): array
	{
		if (!isset($this->properties))
		{
			$this->properties = [];

			$usedPropertyIds = ElementPropertyProvider::getPropertyIdsFromColumnsIds($this->getColumnIds());
			if (!empty($usedPropertyIds))
			{
				$rows = PropertyTable::getList([
					'filter' => [
						'=IBLOCK_ID' => $this->iblockId,
						'!USER_TYPE' => null,
						'@ID' => $usedPropertyIds,
					],
				]);
				foreach ($rows as $row)
				{
					$id = $row['ID'];

					$this->properties[$id] = $row;
					$this->properties[$id]['USER_TYPE_SETTINGS'] = $row['USER_TYPE_SETTINGS_LIST'];
					$this->properties[$id]['PROPERTY_USER_TYPE'] = CIBlockProperty::GetUserType($row['USER_TYPE']);
				}
			}
		}

		return $this->properties;
	}

	protected function prepareRow(array $row): array
	{
		$row['columns'] ??= [];

		if (($row['data']['ROW_TYPE'] ?? '') !== RowType::ELEMENT)
		{
			return $row;
		}

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

		return $row;
	}

	private function renderUserTypeFunction(Closure $callback, string $name, $value, array $property)
	{
		return call_user_func_array(
			$callback,
			[
				$property,
				$value,
				[
					'GRID' => 'PUBLIC',
					'VALUE' => $name . '[VALUE]',
					'DESCRIPTION' => $name . '[DESCRIPTION]',
				],
			]
		);
	}

	private function getColumnValue(string $columnId, array $property, $value)
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
					$columnId,
					$value,
					$property
				);
			}
		}

		return null;
	}

	private function getEditValue(string $columnId, array $property, $value)
	{
		if ($property['MULTIPLE'] === 'Y')
		{
			if (isset($property['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty']))
			{
				return $this->renderUserTypeFunction(
					Closure::fromCallable($property['PROPERTY_USER_TYPE']['GetPublicEditHTMLMulty']),
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
				$columnId,
				$value,
				$property
			);
		}

		return null;
	}
}
