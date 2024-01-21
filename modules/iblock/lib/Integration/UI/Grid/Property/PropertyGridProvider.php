<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Property;

use Bitrix\Iblock\Helpers\Admin\Property;
use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type\Collection;

use CIBlockProperty;

class PropertyGridProvider extends BaseProvider
{
	private int $iblockId;
	private LinksBuilder $linksBuilder;

	/**
	 * @param int $iblockId
	 * @param LinksBuilder $linksBuilder
	 */
	public function __construct(int $iblockId, LinksBuilder $linksBuilder)
	{
		$this->iblockId = $iblockId;
		$this->linksBuilder = $linksBuilder;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return "iblock_property_{$this->iblockId}";
	}

	/**
	 * Field name.
	 *
	 * @param string $fieldId
	 *
	 * @return string|null
	 */
	public function getFieldName(string $fieldId): ?string
	{
		$columns = $this->getColumns();
		foreach ($columns as $item)
		{
			if ($item['id'] === $fieldId)
			{
				return (string)$item['name'];
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getColumns(): array
	{
		return [
			[
				'id' => 'ID',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ID'),
				'sort' => 'ID',
			],
			[
				'id' => 'NAME',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_NAME'),
				'sort' => 'NAME',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'CODE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_CODE'),
				'sort'  =>  'CODE',
				'editable' => true,
			],
			[
				'id' => 'PROPERTY_TYPE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_PROPERTY_TYPE'),
				'sort'  =>  'PROPERTY_TYPE',
				'type' => 'list',
				'default' => true,
				'editable' => false,
			],
			[
				'id' => 'SORT',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_SORT'),
				'sort' => 'SORT',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'ACTIVE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTIVE'),
				'sort' => 'ACTIVE',
				'type' => 'checkbox',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'IS_REQUIRED',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_IS_REQUIRED'),
				'sort'  =>  'IS_REQUIRED',
				'type' => 'checkbox',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'MULTIPLE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_MULTIPLE'),
				'sort'  =>  'MULTIPLE',
				'type' => 'checkbox',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'SEARCHABLE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_SEARCHABLE'),
				'sort' => 'SEARCHABLE',
				'type' => 'checkbox',
				'default' => true,
				'editable' => true,
			],
			[
				'id' => 'FILTRABLE',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_FILTRABLE'),
				'sort' => 'FILTRABLE',
				'type' => 'checkbox',
				'editable' => true,
			],
			[
				'id' => 'XML_ID',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_XML_ID'),
				'sort'  =>  'XML_ID',
				'editable' => true,
			],
			[
				'id' => 'WITH_DESCRIPTION',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_WITH_DESCRIPTION'),
				'sort'  =>  'WITH_DESCRIPTION',
				'type' => 'checkbox',
				'editable' => true,
			],
			[
				'id' => 'HINT',
				'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_HINT'),
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getRowActions(array $row, bool $isEditable): array
	{
		$id = (int)$row['ID'];

		$result = [
			[
				'TEXT' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_OPEN'),
				'HREF' => $this->linksBuilder->getActionOpenLink($id),
				'ONCLICK' => $this->linksBuilder->getActionOpenClick($id),
				'DEFAULT' => true,
			],
		];

		if ($isEditable)
		{
			$result[] = [
				'TEXT' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_DELETE'),
				'HREF' => $this->linksBuilder->getActionDeleteLink($id),
				'ONCLICK' => $this->linksBuilder->getActionDeleteClick($id),
			];
		}

		return $result;
	}

	/**
	 * Property types for dropdown.
	 *
	 * @return array
	 */
	private function getPropertyTypeItems(): array
	{
		$result = Property::getBaseTypeList(true);

		$userTypes = CIBlockProperty::GetUserType();
		Collection::sortByColumn($userTypes, [
			'DESCRIPTION' => SORT_STRING,
		]);

		foreach ($userTypes as $type => $item)
		{
			$key = "{$item['PROPERTY_TYPE']}:{$type}";
			$result[$key] = $item['DESCRIPTION'];
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getRowColumns(array $row): array
	{
		$result = parent::getRowColumns($row);

		// prepare property type
		if (isset($result['PROPERTY_TYPE']))
		{
			$type = $row['PROPERTY_TYPE'] ?? null;
			if ($type)
			{
				$userType = $row['USER_TYPE'] ?? null;
				if ($userType)
				{
					$type .= ':' . $userType;
				}

				$typeNames = $this->getPropertyTypeItems();
				$result['PROPERTY_TYPE'] = $typeNames[$type] ?? null;
			}
		}

		return $result;
	}

	/**
	 * Prepare row.
	 *
	 * @param array $rawRow
	 *
	 * @return array
	 */
	public function prepareRow(array $rawRow): array
	{
		if (isset($rawRow['NAME']))
		{
			$rawRow['NAME'] = HtmlFilter::encode($rawRow['NAME']);
		}

		return parent::prepareRow($rawRow);
	}
}
