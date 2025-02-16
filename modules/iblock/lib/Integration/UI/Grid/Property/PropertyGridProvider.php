<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Property;

use Bitrix\Iblock\Helpers\Admin\Property;
use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Main\Grid\Column\Type;
use Bitrix\Main\Grid\Panel;
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
	 * @param string $fieldId Field indentifier.
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
				'type' => 'int',
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
				'sort'  => 'CODE',
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
				'type' => 'int',
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
	 * @param array $rawRow Row data from database.
	 *
	 * @return array
	 */
	public function prepareRow(array $rawRow): array
	{
		foreach($this->getColumns() as $field)
		{
			$id = $field['id'];
			$type = $field['type'] ?? Type::TEXT;
			switch ($type)
			{
				case Type::TEXT:
					if (isset($rawRow[$id]))
					{
						$rawRow[$id] = HtmlFilter::encode($rawRow[$id]);
					}
					break;
				case Type::INT:
					if (isset($rawRow[$id]))
					{
						$rawRow[$id] = (int)$rawRow[$id];
					}
					break;
			}
		}

		return parent::prepareRow($rawRow);
	}

	/**
	 * Returns the description of the group action panel for the property grid.
	 *
	 * @return array|null
	 */
	public function getActionPanel(): ?array
	{
		$items = [];
		$items[] = $this->getRemoveActionItem();
		$items[] = $this->getEditActionItem();

		return [
			'GROUPS' => [
				[
					'ITEMS' => $items,
				],
			],
		];
	}

	protected function getRemoveActionItem(): array
	{
		$onchange = new Panel\Snippet\Onchange();
		$onchange->addAction([
			'ACTION' => Panel\Actions::CALLBACK,
			'CONFIRM' => true,
			'CONFIRM_APPLY_BUTTON' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_CONFIRM_APPLY_REMOVE_BUTTON_TEXT'),
			'CONFIRM_MESSAGE' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_CONFIRM_MESSAGE_REMOVE'),
			'DATA' => [
				[
					'JS' => 'Grid.removeSelected()',
				],
			]
		]);

		$removeButton = new Panel\Snippet\Button();
		$removeButton->setClass(Panel\DefaultValue::REMOVE_BUTTON_CLASS)
			->setId(Panel\DefaultValue::REMOVE_BUTTON_ID)
			->setOnchange($onchange)
			->setText(Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_DELETE'))
			->setTitle(Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_DELETE_TITLE'))
		;

		return $removeButton->toArray();
	}

	protected function getEditActionItem(): array
	{
		$snippet = new Panel\Snippet();

		$actions = [];
		$actions[] = [
			'ACTION' => Panel\Actions::CREATE,
			'DATA' => [
				$snippet->getSaveEditButton(),
				$snippet->getCancelEditButton(),
			],
		];
		$actions[] = [
			'ACTION' => Panel\Actions::CALLBACK,
			'DATA' => [
				[
					'JS' => 'Grid.editSelected()',
				],
			],
		];
		$actions[] = [
			'ACTION' => Panel\Actions::HIDE_ALL_EXPECT,
			'DATA' => [
				[
					'ID' => Panel\DefaultValue::SAVE_BUTTON_ID,
				],
				[
					'ID' => Panel\DefaultValue::CANCEL_BUTTON_ID,
				],
			],
		];

		$editButton = new Panel\Snippet\Button();
		$editButton->setClass(Panel\DefaultValue::EDIT_BUTTON_CLASS);
		$editButton->setId(Panel\DefaultValue::EDIT_BUTTON_ID);
		$editButton->setText(Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_EDIT'));
		$editButton->setOnchange(new Panel\Snippet\Onchange($actions));
		$editButton->setTitle(Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_PROVIDER_ACTION_EDIT_TITLE'));

		return $editButton->toArray();
	}
}
