<?php

namespace Bitrix\Iblock\Integration\UI\Grid\Property\Type;

use Bitrix\Iblock\Integration\UI\Grid\General\BaseProvider;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Entity;
use Bitrix\Main\ORM\Fields\ScalarField;
use CFile;
use CUserTypeManager;

/**
 * Provider of directory items for properties with the corresponding type.
 */
final class DirectoryItemsProvider extends BaseProvider
{
	private int $propertyId;
	private array $columns;
	private ?int $directoryId;
	private ?Entity $directoryEntity;

	/**
	 * @param int $propertyId
	 * @param int|null $directoryId
	 * @param Entity|null $directoryEntity
	 */
	public function __construct(int $propertyId, ?int $directoryId, ?Entity $directoryEntity)
	{
		$this->propertyId = $propertyId;
		$this->directoryId = $directoryId;
		$this->directoryEntity = $directoryEntity;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string
	{
		return "iblock_property_{$this->propertyId}_directory_items";
	}

	/**
	 * @inheritDoc
	 */
	public function getColumns(): array
	{
		if (isset($this->columns))
		{
			return $this->columns;
		}

		if (!isset($this->directoryEntity))
		{
			// default new directory columns
			$this->columns = [
				[
					'id' => 'ID',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_ID'),
					'default' => true,
				],
				[
					'id' => 'UF_NAME',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_NAME'),
					'default' => true,
					'editable' => true,
					'type' => 'text',
				],
				[
					'id' => 'UF_SORT',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_SORT'),
					'default' => true,
					'editable' => true,
					'type' => 'number',
				],
				[
					'id' => 'UF_XML_ID',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_XML_ID'),
					'default' => true,
					'editable' => true,
					'type' => 'text',
				],
				[
					'id' => 'UF_FILE',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_FILE'),
					'default' => true,
					'editable' => true,
					'type' => 'image',
					'editable' => [
						'TYPE' => Types::IMAGE,
					],
				],
				[
					'id' => 'UF_LINK',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_LINK'),
					'default' => true,
					'editable' => true,
					'type' => 'text',
				],
				[
					'id' => 'UF_DEF',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_DEF'),
					'default' => true,
					'editable' => true,
					'type' => 'checkbox',
				],
				[
					'id' => 'UF_DESCRIPTION',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_DESCRIPTION'),
					'default' => true,
					'editable' => true,
					'type' => 'text',
				],
				[
					'id' => 'UF_FULL_DESCRIPTION',
					'name' => Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_UF_FULL_DESCRIPTION'),
					'default' => true,
					'editable' => true,
					'type' => 'text',
				],
			];
		}
		else
		{
			$this->columns = [];

			$fields = $this->directoryEntity->getFields();
			$userFieldsInfo = $this->getUserFieldsInfo();

			foreach ($fields as $field)
			{
				$item = [
					'default' => true,
					'editable' => true,
					'type' => 'text',
				];

				if (is_array($field))
				{
					$item['id'] = $field['id'];
				}
				elseif ($field instanceof ScalarField)
				{
					$item['id'] = $field->getName();
				}
				else
				{
					continue;
				}

				$info = $userFieldsInfo[$item['id']] ?? null;

				$item['name'] =
					$info['EDIT_FORM_LABEL']
					?: Loc::getMessage('IBLOCK_UI_GRID_PROPERTY_DIRECTORY_ITEMS_PROVIDER_FIELD_' . $item['id'])
					?: $item['id']
				;

				if (isset($info))
				{
					if ($info['USER_TYPE_ID'] === 'file')
					{
						$item['type'] = 'image'; // directory items only image support
						$item['editable'] = [
							'TYPE' => Types::IMAGE,
						];
					}
					elseif ($info['USER_TYPE_ID'] === 'double')
					{
						$item['type'] = 'number';
					}
					elseif ($info['USER_TYPE_ID'] === 'boolean')
					{
						$item['type'] = 'checkbox';
					}
				}

				if (mb_strtoupper($item['id']) === 'ID')
				{
					$item['editable'] = false;
				}

				$this->columns[] = $item;
			}
		}

		return $this->columns;
	}

	/**
	 * User fields info of highload block.
	 *
	 * @return array
	 */
	private function getUserFieldsInfo(): array
	{
		global $USER_FIELD_MANAGER;

		/**
		 * @var CUserTypeManager $USER_FIELD_MANAGER
		 */

		if (!$this->directoryId)
		{
			return $this->directoryId;
		}

		$entityId = 'HLBLOCK_' . $this->directoryId;
		if (!$entityId)
		{
			return [];
		}

		$userFieldsInfo = $USER_FIELD_MANAGER->GetUserFields($entityId, 0, LANGUAGE_ID);
		if (empty($userFieldsInfo))
		{
			return [];
		}

		return $userFieldsInfo;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array
	{
		$result = parent::toArray();

		$result['SHOW_GRID_SETTINGS_MENU'] = false;
		$result['ALLOW_EDIT_SELECTION'] = true;
		$result['ADVANCED_EDIT_MODE'] = true;

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getTemplateRow(): ?array
	{
		return [
			'data' => [
				'SORT' => 500,
				'DEF' => 'N',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	public function prepareRow(array $rawRow): array
	{
		$result = parent::prepareRow($rawRow);

		foreach ($this->getColumns() as $column)
		{
			$id = $column['id'] ?? null;
			if (!$id)
			{
				continue;
			}

			$type = $column['type'] ?? null;
			if ($type === 'image')
			{
				$fileId = (int)($result['data'][$id] ?? 0);
				if ($fileId > 0)
				{
					$result['data'][$id] = CFile::GetPath($fileId);
				}
				else
				{
					$result['data'][$id] = null;
				}
			}
			elseif ($type === 'checkbox')
			{
				$value = (string)($result['data'][$id] ?? '');
				$result['data'][$id] = $value === 'Y' || $value === '1' ? 'Y' : 'N';
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getActionPanel(): ?array
	{
		$snippet = new Snippet();

		// update remove button
		$onchange = new Onchange();
		$onchange->addAction([
			'ACTION' => Actions::CALLBACK,
			'DATA' => [
				[
					// the implementation must be filled in at the place of use
					'JS' => 'javascript:;',
				],
			],
		]);

		$removeButton = $snippet->getRemoveButton();
		$removeButton['ONCHANGE'] = $onchange->toArray();

		return [
			'GROUPS' => [
				[
					'ITEMS' => [
						$removeButton,
					],
				],
			],
		];
	}
}
