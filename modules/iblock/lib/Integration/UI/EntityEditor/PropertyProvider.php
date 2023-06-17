<?php

namespace Bitrix\Iblock\Integration\UI\EntityEditor;

use Bitrix\Iblock\Helpers\Admin\Property;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\Integration\UI\EntityEditor\Property\PropertyFeatureEditorFields;
use Bitrix\Iblock\Property\Type\PropertyTypeSettings;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\Type\Collection;
use Bitrix\UI\EntityEditor\BaseProvider;
use CIBlockProperty;
use CIBlockSectionPropertyLink;

Loader::requireModule('ui');

class PropertyProvider extends BaseProvider
{
	/**
	 * Entity field values.
	 *
	 * @var array
	 */
	private array $entity;
	private string $propertyType;
	private ?string $userType;
	private PropertyTypeSettings $typeSettings;
	private PropertyFeatureEditorFields $futureEditor;

	/**
	 * @param string $propertyType
	 * @param string|null $userType
	 * @param array $entityFields
	 */
	public function __construct(string $propertyType, ?string $userType, array $entityFields)
	{
		$this->propertyType = $propertyType;
		$this->userType = $userType;
		$this->entity = $entityFields;

		$this->initEntityDefaultValues();
	}

	private function initEntityDefaultValues(): void
	{
		$fields = $this->getEntityFields();
		foreach ($fields as $field)
		{
			$name = $field['name'];
			if (array_key_exists($name, $this->entity))
			{
				continue;
			}

			if (isset($field['default_value']))
			{
				$this->entity[$name] = $field['default_value'];
			}
			elseif ($field['type'] === 'boolean')
			{
				$this->entity[$name] = 'N';
			}
		}
	}

	public function getEntityId(): ?int
	{
		return $this->entity['ID'] ?? null;
	}

	public function getEntityData(): array
	{
		$values = $this->entity;

		$setValues = $this->getPropertyTypeSettings()->getSetValues();
		if (!empty($setValues))
		{
			$values = array_merge($values, $setValues);
		}

		return $values;
	}

	public function getEntityFields(): array
	{
		$fields = [
			[
				'name' => 'PROPERTY_TYPE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_PROPERTY_TYPE'),
				'type' => 'list',
				'data' => [
					'items' => $this->getPropertyTypeItems(),
				],
				'required' => true,
				'disabled' => !is_null($this->getEntityId()) && $this->getEntityId() > 0,
			],
			[
				'name' => 'CODE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_CODE'),
				'type' => 'text',
			],
			[
				'name' => 'NAME',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_NAME'),
				'type' => 'text',
				'required' => true,
			],
			[
				'name' => 'SORT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_SORT'),
				'type' => 'number',
				'default_value' => 100,
				'hint' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_SORT_HINT'),
			],
			[
				'name' => 'ACTIVE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_ACTIVE'),
				'type' => 'boolean',
				'default_value' => 'Y',
			],
			[
				'name' => 'MULTIPLE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_MULTIPLE'),
				'type' => 'boolean',
			],
			[
				'name' => 'IS_REQUIRED',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_IS_REQUIRED'),
				'type' => 'boolean',
			],
			[
				'name' => 'SEARCHABLE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_SEARCHABLE'),
				'type' => 'boolean',
			],
		];

		return $this->clearHiddenFields($fields);
	}

	public function getAdditionalFields(): array
	{
		$fields = [
			[
				'name' => 'FILTERABLE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_FILTERABLE'),
				'type' => 'boolean',
			],
			[
				'name' => 'WITH_DESCRIPTION',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_WITH_DESCRIPTION'),
				'type' => 'boolean',
			],
			[
				'name' => 'MULTIPLE_CNT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_MULTIPLE_CNT'),
				'type' => 'number',
			],
			[
				'name' => 'HINT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_HINT'),
				'type' => 'text',
			],
			[
				'name' => 'SECTION_PROPERTY',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_SECTION_PROPERTY'),
				'type' => 'boolean',
			],
			[
				'name' => 'SMART_FILTER',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_SMART_FILTER'),
				'type' => 'boolean',
			],
			[
				'name' => 'DISPLAY_TYPE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_DISPLAY_TYPE'),
				'type' => 'list',
				'data' => [
					'items' => $this->getDisplayTypeItems(),
				],
			],
			[
				'name' => 'DISPLAY_EXPANDED',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_DISPLAY_EXPANDED'),
				'type' => 'boolean',
			],
			[
				'name' => 'FILTER_HINT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_FILTER_HINT'),
				'type' => 'textarea',
			],
			[
				'name' => 'ROW_COUNT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_ROW_COUNT'),
				'type' => 'number',
			],
			[
				'name' => 'COL_COUNT',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_COL_COUNT'),
				'type' => 'number',
			],
			[
				'name' => 'FILE_TYPE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_FILE_TYPE'),
				'type' => 'text',
			],
			[
				'name' => 'LINK_IBLOCK_ID',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_LINK_IBLOCK_ID'),
				'type' => 'list',
				'data' => [
					'items' => $this->getLinkIblockIdItems(),
				],
			],
			[
				'name' => 'LIST_TYPE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_LIST_TYPE'),
				'type' => 'list',
				'data' => [
					'items' => $this->getListTypeItems(),
				],
			],
		];

		if (Option::get('iblock', 'show_xml_id') === 'Y')
		{
			$fields[] = [
				'name' => 'XML_ID',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_XML_ID'),
				'type' => 'text',
			];
		}

		// add default value
		$html = $this->getPropertyTypeSettings()->getDefaultValueHtml();
		if (isset($html))
		{
			$fields[] = [
				'name' => 'DEFAULT_VALUE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_DEFAULT_VALUE'),
				'type' => 'custom',
				'data' => [
					'html' => $html,
				],
			];
		}
		else
		{
			$fields[] = [
				'name' => 'DEFAULT_VALUE',
				'title' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_DEFAULT_VALUE'),
				'type' => 'text',
			];
		}

		// add feature fields
		$futureFields = $this->getPropertyFeatureEditorFields();
		if ($futureFields->isHasFields())
		{
			array_push($fields, ... $futureFields->getEntityFields());
		}

		return $this->clearHiddenFields($fields);
	}

	private function clearHiddenFields(array $fields): array
	{
		return array_filter(
			$fields,
			function(array $item)
			{
				return
					isset($item['name'])
					&& $this->getPropertyTypeSettings()->isShownField($item['name'])
				;
			}
		);
	}

	public function getSettingsHtml(): ?string
	{
		return $this->getPropertyTypeSettings()->getSettingsHtml();
	}

	/**
	 * Property features fields for entity editor.
	 *
	 * @return PropertyFeatureEditorFields
	 */
	private function getPropertyFeatureEditorFields(): PropertyFeatureEditorFields
	{
		$this->futureEditor ??= new PropertyFeatureEditorFields(
			$this->entity
		);

		return $this->futureEditor;
	}

	/**
	 * Property type settings.
	 *
	 * @return PropertyTypeSettings
	 */
	private function getPropertyTypeSettings(): PropertyTypeSettings
	{
		if (isset($this->typeSettings))
		{
			return $this->typeSettings;
		}

		if (isset($this->userType))
		{
			$this->typeSettings = PropertyTypeSettings::createByUserType($this->propertyType, $this->userType, $this->entity);
		}
		else
		{
			$this->typeSettings = new PropertyTypeSettings($this->propertyType, null);
		}

		return $this->typeSettings;
	}

	/**
	 * Display types for dropdown.
	 *
	 * @return array
	 */
	private function getDisplayTypeItems(): array
	{
		$result = [];

		$types = CIBlockSectionPropertyLink::getDisplayTypes($this->propertyType, $this->userType);
		foreach ($types as $type => $name)
		{
			$result[] = [
				'NAME' => $name,
				'VALUE' => $type,
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
		$result = [];

		$baseTypes = Property::getBaseTypeList(true);
		foreach ($baseTypes as $type => $name)
		{
			$result[] = [
				'NAME' => $name,
				'VALUE' => $type,
			];
		}

		$userTypes = CIBlockProperty::GetUserType();
		Collection::sortByColumn($userTypes, [
			'DESCRIPTION' => SORT_STRING,
		]);

		foreach ($userTypes as $type => $item)
		{
			$result[] = [
				'NAME' => $item['DESCRIPTION'],
				'VALUE' => "{$item['PROPERTY_TYPE']}:{$type}",
			];
		}

		return $result;
	}

	/**
	 * List types for dropdown.
	 *
	 * @return array
	 */
	private function getListTypeItems(): array
	{
		$result = [];

		$result[] = [
			'NAME' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_LIST_TYPE_CHECKBOX'),
			'VALUE' => PropertyTable::CHECKBOX,
		];
		$result[] = [
			'NAME' => Loc::getMessage('IBLOCK_ENTITY_EDITOR_PROPERTY_LIST_TYPE_LISTBOX'),
			'VALUE' => PropertyTable::LISTBOX,
		];

		return $result;
	}

	/**
	 * Iblock ids for dropdown.
	 *
	 * @return array
	 */
	private function getLinkIblockIdItems(): array
	{
		$result = [];

		$filter = [
			'=ACTIVE' => 'Y',
		];

		$iblockId = ($this->entity['IBLOCK_ID'] ?? 0);
		if ($iblockId > 0)
		{
			$filter['!=ID'] = $iblockId;
		}

		$rows = IblockTable::getList([
			'select' => [
				'ID',
				'NAME',
				'TYPE_NAME' => 'TYPE.LANG_MESSAGE.NAME',
			],
			'filter' => $filter,
		]);
		foreach ($rows as $row)
		{
			$result[] = [
				'NAME' => "{$row['TYPE_NAME']}. {$row['NAME']}",
				'VALUE' => $row['ID'],
			];
		}

		return $result;
	}

	#region not used parent methods

	public function getFields(): array
	{
		throw new NotImplementedException('Not used');
	}

	public function getGUID(): string
	{
		throw new NotImplementedException('Not used');
	}

	public function getEntityTypeName(): string
	{
		throw new NotImplementedException('Not used');
	}

	public function getEntityConfig(): array
	{
		throw new NotImplementedException('Not used');
	}

	#endregion not used parent methods
}
