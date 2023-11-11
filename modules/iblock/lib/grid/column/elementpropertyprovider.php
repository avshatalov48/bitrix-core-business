<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Iblock;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;

//use CIBlockPropertyEnum;

class ElementPropertyProvider extends BaseElementProvider
{
	protected const COLUMN_ID_MORE_PHOTO = 'MORE_PHOTO';

	protected const PREFIX_ID = 'PROPERTY_';

	protected array $properties;

	public function prepareColumns(): array
	{
		$iblockId = $this->getIblockId();
		if ($iblockId === null)
		{
			return [];
		}

		$isNewCardEnabled = $this->isNewCardEnabled();

		$morePhotoId = $this->getPropertyMorePhotoId();

		$result = [];
		foreach ($this->getProperties() as $row)
		{
			$columnId = self::getColumnIdByPropertyId($row['ID']);
			$columnType = Grid\Column\Type::TEXT;
			$multiple = $row['MULTIPLE'] === 'Y';
			$preventDefault = true; // TODO: what is this

			$description = [
				'type' => $columnType,
				'name' => $row['NAME'],
				'necessary' => false,
				'editable' => $multiple ? false : true,
				'multiple' => $multiple,
				'select' => [
					// EMPTY! Properties must be loaded separately due to multiple values.
				]
			];

			$extendedMorePhoto = $isNewCardEnabled && $row['ID'] === $morePhotoId;
			if ($extendedMorePhoto)
			{
				$columnId = self::COLUMN_ID_MORE_PHOTO;
				$columnType = Grid\Column\Type::CUSTOM;

				$description['editable'] = new Grid\Column\Editable\CustomConfig($columnId);
			}
			elseif (
				$row['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE
				&& $multiple
				&& !$extendedMorePhoto
			)
			{
				$description['editable'] = false;
				$preventDefault = false;
			}

			if (!$multiple)
			{
				$description['sort'] = $columnId;
			}

			if (isset($row['USER_TYPE']))
			{
				$description['type'] = Grid\Column\Type::CUSTOM;
				$description['editable'] = new Grid\Column\Editable\CustomConfig($columnId);
			}
			elseif ($row['PROPERTY_TYPE'] === PropertyTable::TYPE_NUMBER)
			{
				$description['type'] = Grid\Column\Type::NUMBER;
				$description['align'] = 'right';
			}
			elseif ($row['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST)
			{
				$listItems = $this->getPropertyEnumValues($row['ID']);
				if (!empty($listItems))
				{
					$description['type'] =
						$multiple
							? Grid\Column\Type::MULTISELECT
							: Grid\Column\Type::DROPDOWN
					;
					$description['editable'] = new Iblock\Grid\Column\Editable\PropertyEnumerationConfig(
						$columnId,
						$row,
						$listItems,
					);
				}
				else
				{
					$description['editable'] = false;
				}
				unset($listItems);
			}
			elseif ($row['PROPERTY_TYPE'] === PropertyTable::TYPE_ELEMENT)
			{
				$description['type'] = Grid\Column\Type::CUSTOM;
				$description['editable'] = new Grid\Column\Editable\CustomConfig($columnId);
			}
			elseif ($row['PROPERTY_TYPE'] === PropertyTable::TYPE_SECTION)
			{
				$description['type'] = Grid\Column\Type::CUSTOM;
				$description['editable'] = new Grid\Column\Editable\CustomConfig($columnId);
			}
			elseif ($row['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE)
			{
				$description['type'] = Grid\Column\Type::FILE;
			}

			$description['prevent_default'] = $preventDefault; // TODO: what is this

			$result[$columnId] = $description;
		}

		return $this->createColumns($result);
	}

	protected function getProperties(): array
	{
		if (!isset($this->properties))
		{
			$this->loadProperties();
		}

		return $this->properties;
	}

	protected function loadProperties(): void
	{
		$this->properties = [];
		$iblockId = $this->getIblockId();
		if ($iblockId === null)
		{
			return;
		}

		$iterator = PropertyTable::getList([
			'select' => [
				'*',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblockId,
				'=ACTIVE' => 'Y',
			],
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['SORT'] = (int)$row['SORT'];
			$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];
			if ($row['USER_TYPE'] !== null)
			{
				$row['USER_TYPE'] = trim($row['USER_TYPE']);
				if ($row['USER_TYPE'] === '')
				{
					$row['USER_TYPE'] = null;
				}
			}
			$row['USER_TYPE_DESCRIPTION'] = ($row['USER_TYPE'] ? \CIBlockProperty::GetUserType($row['USER_TYPE']) : []);
			if (!is_array($row['USER_TYPE_SETTINGS_LIST']))
			{
				$row['USER_TYPE_SETTINGS_LIST'] = [];
			}
			$row['USER_TYPE_SETTINGS'] = $row['USER_TYPE_SETTINGS_LIST'];
			unset($row['USER_TYPE_SETTINGS_LIST']);

			$this->properties[$row['ID']] = $row;
		}
		unset($row, $iterator);
	}

	protected function getPropertyMorePhotoId(): ?int
	{
		$result = null;
		foreach ($this->getProperties() as $row)
		{
			if (
				$row['PROPERTY_TYPE'] === PropertyTable::TYPE_FILE
				&& $row['CODE'] === \CIBlockPropertyTools::CODE_MORE_PHOTO
			)
			{
				$result = $row['ID'];
				break;
			}
		}

		return $result;
	}

	private function getPropertyEnumValues(int $propertyId): array
	{
		$result = [];

		$iterator = PropertyEnumerationTable::getList([
			'select' => [
				'ID',
				'VALUE',
				'DEF',
				'SORT',
			],
			'filter' => [
				'=PROPERTY_ID' => $propertyId
			],
			'order' => [
				'SORT' => 'ASC',
				'VALUE' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['ID'];
			$result[$id] = [
				'ID' => $id,
				'VALUE' => $row['VALUE'],
				'DEF' => $row['DEF'],
			];
		}
		unset($row, $iterator);

		return $result;
	}

	#region static

	/**
	 * Returns property ids map.
	 *
	 * @param array $columnIds
	 *
	 * @return int[] in format [columnId => propertyId]
	 */
	public static function getPropertyIdsFromColumnsIds(array $columnIds): array
	{
		$result = [];

		$propertyPrefixRe = '/^' . preg_quote(self::PREFIX_ID) . '(\d+)$/';

		foreach ($columnIds as $columnId)
		{
			if (preg_match($propertyPrefixRe, $columnId, $m))
			{
				$result[$columnId] = (int)$m[1];
			}
		}

		return $result;
	}

	/**
	 * @param int $id
	 *
	 * @return string
	 */
	public static function getColumnIdByPropertyId(int $id): string
	{
		return self::PREFIX_ID . $id;
	}

	#endregion static
}
