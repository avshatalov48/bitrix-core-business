<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\EnumType;
use Bitrix\Highloadblock\HighloadBlockTable;

class CUserTypeHlblock extends BaseType
{
	public const USER_TYPE_ID = 'hlblock';
	public const RENDER_COMPONENT = 'bitrix:highloadblock.field.element';

	public const DISPLAY_LIST = 'LIST';
	public const DISPLAY_CHECKBOX = 'CHECKBOX';
	public const DISPLAY_UI = 'UI';
	public const DISPLAY_DIALOG = 'DIALOG';

	protected static bool $highloadblockIncluded;

	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_HLEL_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT,
		];
	}

	/**
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string
	 */
	public static function renderField(array $userField, ?array $additionalParameters = []): string
	{
		static::getEnumList(
			$userField,
			array_merge(
				$additionalParameters ?? [],
				['mode' => self::MODE_VIEW]
			)
		);

		return parent::renderField($userField, $additionalParameters);
	}

	/**
	 * This function is called when the property values are displayed in the public part of the site.
	 *
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string
	 */
	public static function renderView(array $userField, ?array $additionalParameters = []): string
	{
		static::getEnumList(
			$userField,
			array_merge(
				$additionalParameters ?? [],
				['mode' => self::MODE_VIEW]
			)
		);

		return parent::renderView($userField, $additionalParameters);
	}

	/**
	 * This function is called when editing property values in the public part of the site.
	 *
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string
	 */
	public static function renderEdit(array $userField, ?array $additionalParameters = []): string
	{
		static::getEnumList(
			$userField,
			array_merge(
				$additionalParameters ?? [],
				['mode' => self::MODE_EDIT]
			)
		);

		return parent::renderEdit($userField, $additionalParameters);
	}

	public static function renderEditForm(array $userField, ?array $additionalParameters): string
	{
		$enum = static::getList($userField);
		if(!$enum)
		{
			return '';
		}
		$items = [];
		while($item = $enum->Fetch())
		{
			$items[$item['ID']] = $item;
		}
		$additionalParameters['items'] = $items;

		return parent::renderEditForm($userField, $additionalParameters);
	}

	public static function renderFilter(array $userField, ?array $additionalParameters): string
	{
		$iterator = static::getList($userField);
		if (!$iterator)
		{
			return '';
		}

		$items = [];
		while ($item = $iterator->Fetch())
		{
			$items[$item['ID']] = $item['VALUE'];
		}
		unset($item, $iterator);
		$additionalParameters['items'] = $items;

		return parent::renderFilter($userField, $additionalParameters);
	}

	public static function renderAdminListView(array $userField, ?array $additionalParameters): string
	{
		static $cache = [];
		$emptyCaption = '&nbsp;';

		$value = (int)($additionalParameters['VALUE'] ?? 0);

		if (!isset($cache[$value]))
		{
			$iterator = static::getList($userField);
			if (!$iterator)
			{
				$additionalParameters['VALUE'] = $emptyCaption;

				return parent::renderAdminListView($userField, $additionalParameters);
			}
			while ($item = $iterator->Fetch())
			{
				$cache[(int)$item['ID']] = $item['NAME'];
			}
			unset($item, $iterator);
		}
		if (!isset($cache[$value]))
		{
			$cache[$value] = $emptyCaption;
		}

		$additionalParameters['VALUE'] = $cache[$value];

		return parent::renderAdminListView($userField, $additionalParameters);
	}

	public static function renderAdminListEdit(array $userField, ?array $additionalParameters): string
	{
		$values = [];
		$iterator = static::getList($userField);
		if ($iterator)
		{
			while ($item = $iterator->Fetch())
			{
				$values[$item['ID']] = $item['VALUE'];
			}
			unset($item, $iterator);
		}
		$additionalParameters['enumItems'] = $values;

		return parent::renderAdminListEdit($userField, $additionalParameters);
	}

	public static function getDBColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	public static function checkFields(array $userField, $value): array
	{
		return [];
	}

	public static function prepareSettings(array $userField): array
	{
		$multiple = false;
		if (isset($userField['MULTIPLE']) && $userField['MULTIPLE'] === 'Y')
		{
			$multiple = true;
		}

		$settings = [];
		if (!empty($userField['SETTINGS']) && is_array($userField['SETTINGS']))
		{
			$settings = $userField['SETTINGS'];
		}

		return self::verifySettings($settings, $multiple);
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return array
	 */
	public static function getFilterData(array $userField, array $additionalParameters): array
	{
		$items = [];
		$iterator = static::getList($userField);
		if ($iterator)
		{
			while ($item = $iterator->GetNext())
			{
				$items[$item['ID']] = $item['VALUE'];
			}
			unset($item, $enum);
		}

		return [
			'id' => $additionalParameters['ID'],
			'name' => $additionalParameters['NAME'],
			'type' => 'list',
			'items' => $items,
			'params' => [
				'multiple' => 'Y',
			],
			'filterable' => '',
		];
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 */
	public static function getEnumList(array &$userField, array $additionalParameters = []): void
	{
		if (!isset(self::$highloadblockIncluded))
		{
			self::$highloadblockIncluded = Loader::includeModule('highloadblock');
		}

		$userField['MANDATORY'] ??= 'N';
		$userField['SETTINGS']['HLBLOCK_ID'] ??= 0;
		$userField['SETTINGS']['HLFIELD_ID'] ??= 0;
		$userField['SETTINGS']['SHOW_NO_VALUE'] ??= 'Y';
		$userField['SETTINGS']['DISPLAY'] ??= '';

		if (
			!self::$highloadblockIncluded
			|| (int)$userField['SETTINGS']['HLBLOCK_ID'] <= 0
		)
		{
			return;
		}

		$result = [];
		$showNoValue = (
			$userField['MANDATORY'] !== 'Y'
			|| $userField['SETTINGS']['SHOW_NO_VALUE'] !== 'N'
			|| (
				isset($additionalParameters['SHOW_NO_VALUE'])
				&& $additionalParameters['SHOW_NO_VALUE'] === true
			)
		);

		if (
			$showNoValue
			&& (
				$userField['SETTINGS']['DISPLAY'] !== 'CHECKBOX'
				|| $userField['MULTIPLE'] !== 'Y'
			)
		)
		{
			$result = [
				null => static::getEmptyCaption($userField)
			];
		}

		$filter = [];

		$checkValue = ($additionalParameters['mode'] ?? '') === self::MODE_VIEW;
		if ($checkValue)
		{
			$currentValues = self::getCurrentValue($userField, $additionalParameters);
			if (!empty($currentValues))
			{
				if (is_array($currentValues))
				{
					Type\Collection::normalizeArrayValuesByInt($currentValues);
				}
				else
				{
					$currentValues = (int)$currentValues;
					if ($currentValues <= 0)
					{
						$currentValues = null;
					}
				}
			}
			if (!empty($currentValues))
			{
				$filter['ID'] = $currentValues;
			}
			else
			{
				$userField['USER_TYPE']['FIELDS'] = $result;

				return;
			}
		}

		$elements = self::loadElement(
			$userField['SETTINGS'],
			$filter
		);

		if (!is_array($elements))
		{
			return;
		}

		if (!empty($currentValues))
		{
			$result = $elements;
		}
		else
		{
			$result = array_replace($result, $elements);
		}

		$userField['USER_TYPE']['FIELDS'] = $result;
	}

	public static function verifySettings(array $settings, bool $multiple): array
	{
		$defaultSettings = static::getDefaultSettings($multiple);

		$height = (int)($settings['LIST_HEIGHT'] ?? $defaultSettings['LIST_HEIGHT']);
		if ($height < 1)
		{
			$height = $defaultSettings['LIST_HEIGHT'];
		}

		$display = (string)($settings['DISPLAY'] ?? $defaultSettings['DISPLAY']);
		if (
			$display !== static::DISPLAY_CHECKBOX
			&& $display !== static::DISPLAY_LIST
			&& $display !== static::DISPLAY_UI
			&& $display !== static::DISPLAY_DIALOG
		)
		{
			$display = $defaultSettings['DISPLAY'];
		}

		$hlblockId = (int)($settings['HLBLOCK_ID'] ?? $defaultSettings['HLBLOCK_ID']);
		if ($hlblockId < 0)
		{
			$hlblockId = $defaultSettings['HLBLOCK_ID'];
		}

		$hlfieldId = (int)($settings['HLFIELD_ID'] ?? $defaultSettings['HLFIELD_ID']);
		if ($hlfieldId < 0)
		{
			$hlfieldId = $defaultSettings['HLFIELD_ID'];
		}

		$defaultValue = $settings['DEFAULT_VALUE'] ?? $defaultSettings['DEFAULT_VALUE'];
		if ($multiple)
		{
			if (!is_array($defaultValue))
			{
				$defaultValue = [$defaultValue];
			}
			Main\Type\Collection::normalizeArrayValuesByInt($defaultValue, true);
		}
		else
		{
			if (!is_int($defaultValue) && !is_string($defaultValue))
			{
				$defaultValue = $defaultSettings['DEFAULT_VALUE'];
			}
			$defaultValue = (int)$defaultValue;
			if ($defaultValue <= 0)
			{
				$defaultValue = $defaultSettings['DEFAULT_VALUE'];
			}
		}

		return [
			'DISPLAY' => $display,
			'LIST_HEIGHT' => $height,
			'HLBLOCK_ID' => $hlblockId,
			'HLFIELD_ID' => $hlfieldId,
			'DEFAULT_VALUE' => $defaultValue,
		];
	}

	public static function getDefaultSettings(bool $multiple = false): array
	{
		return [
			'DISPLAY' => static::DISPLAY_LIST,
			'LIST_HEIGHT' => $multiple ? 5 : 1,
			'HLBLOCK_ID' => 0,
			'HLFIELD_ID' => 0,
			'DEFAULT_VALUE' => ($multiple ? [] : ''),
		];
	}

	private static function getHighloadblockSelectorHtml(string $name, array $select): string
	{
		$name = htmlspecialcharsbx($name);

		$list = static::getDropDownData();

		// hlblock selector
		$html = '<select name="' . $name . '[HLBLOCK_ID]" onchange="hlChangeFieldOnHlblockChanged(this)">';
		$html .= '<option value="">'.htmlspecialcharsbx(Loc::getMessage('USER_TYPE_HLEL_SEL_HLBLOCK')).'</option>';

		foreach ($list as $_hlblockId => $hlblockData)
		{
			$html .= '<option value="'.$_hlblockId.'"'
				. ($_hlblockId === $select['HLBLOCK_ID'] ? ' selected' : '') . '>'
				. htmlspecialcharsbx($hlblockData['name']) . '</option>'
			;
		}

		$html .= '</select> &nbsp; ';

		// field selector
		$html .= '<select name="' . $name . '[HLFIELD_ID]" id="hl_ufsett_field_selector">';
		$html .= '<option value="">'.htmlspecialcharsbx(Loc::getMessage('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD')).'</option>';

		if ($select['HLBLOCK_ID'] > 0)
		{
			foreach ($list[$select['HLBLOCK_ID']]['fields'] as $fieldId => $fieldName)
			{
				$html .= '<option value="'.$fieldId.'"'.($fieldId === $select['HLFIELD_ID'] ? ' selected' : '').'>'.htmlspecialcharsbx($fieldName).'</option>';
			}
		}

		$html .= '</select>';

		// js: changing field selector
		$html .= '
			<script>
				function hlChangeFieldOnHlblockChanged(hlSelect)
				{
					var list = '.CUtil::PhpToJSObject($list).';
					var fieldSelect = BX("hl_ufsett_field_selector");

					for(var i=fieldSelect.length-1; i >= 0; i--)
						fieldSelect.remove(i);

					var newOption = new Option(\''.CUtil::JSEscape(Loc::getMessage('USER_TYPE_HLEL_SEL_HLBLOCK_FIELD')).'\', "", false, false);
					fieldSelect.options.add(newOption);

					if (list[hlSelect.value])
					{
						for(var j in list[hlSelect.value]["fields"])
						{
							var newOption = new Option(list[hlSelect.value]["fields"][j], j, false, false);
							fieldSelect.options.add(newOption);
						}
					}
				}
			</script>
		';

		return $html;
	}

	public static function getList($userField)
	{
		$rs = false;

		if (!isset(self::$highloadblockIncluded))
		{
			self::$highloadblockIncluded = Loader::includeModule('highloadblock');
		}

		if (self::$highloadblockIncluded)
		{
			$rows = static::getHlRows($userField, true);

			$rs = new CDBResult();
			$rs->InitFromArray($rows);
		}

		return $rs;
	}

	public static function getEntityReferences($userfield, \Bitrix\Main\Entity\ScalarField $entityField): array
	{
		// here
		if ($userfield['SETTINGS']['HLBLOCK_ID'])
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getByPrimary(
				$userfield['SETTINGS']['HLBLOCK_ID'], ['cache' => ['ttl' => 3600*24*365]]
			)->fetch();

			if ($hlblock)
			{
				$hlentity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);

				$referenceFields = [
					new \Bitrix\Main\Entity\ReferenceField(
						$entityField->getName() . '_REF',
						$hlentity,
						['=this.' . $entityField->getName() => 'ref.ID']
					)
				];

				return $referenceFields;
			}
		}

		return array();
	}

	public static function getHlRows($userfield, $clearValues = false): array
	{
		global $USER_FIELD_MANAGER;

		$rows = array();

		$hlblock_id = (int)($userfield['SETTINGS']['HLBLOCK_ID'] ?? 0);
		$hlfield_id = (int)($userfield['SETTINGS']['HLFIELD_ID'] ?? 0);
		if ($hlfield_id <= 0)
		{
			$hlfield_id = 0;
		}

		if (!empty($hlblock_id))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
		}

		if (!empty($hlblock))
		{
			$userfield = null;

			if ($hlfield_id > 0)
			{
				$iterator = Main\UserFieldTable::getList([
					'select' => [
						'*',
					],
					'filter' => [
						'=ENTITY_ID' => HighloadBlockTable::compileEntityId($hlblock['ID']),
						'=ID' => $hlfield_id,
					],
				]);
				$row = $iterator->fetch();
				unset($iterator);
				if (!empty($row))
				{
					$row['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);
					$userfield = $row;
				}
				else
				{
					$hlfield_id = 0;
				}
			}

			if ($hlfield_id == 0)
			{
				$userfield = array('FIELD_NAME' => 'ID');
			}

			if ($userfield)
			{
				// validated successfully. get data
				$hlDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock)->getDataClass();
				$rows = $hlDataClass::getList(array(
					'select' => array('ID', $userfield['FIELD_NAME']),
					'order' => 'ID'
				))->fetchAll();

				foreach ($rows as &$row)
				{
					$row['ID'] = (int)$row['ID'];
					if ($userfield['FIELD_NAME'] == 'ID')
					{
						$row['VALUE'] = $row['ID'];
					}
					else
					{
						//see #0088117
						if ($userfield['USER_TYPE_ID'] !== EnumType::USER_TYPE_ID && $clearValues)
						{
							$row['VALUE'] = $row[$userfield['FIELD_NAME']];
						}
						else
						{
							$row['VALUE'] = $USER_FIELD_MANAGER->getListView($userfield, $row[$userfield['FIELD_NAME']]);
						}
						$row['VALUE'] .= ' ['.$row['ID'].']';
					}
				}
			}
		}

		return $rows;
	}

	public static function getAdminListViewHtml(array $userField, ?array $additionalParameters): string
	{
		static $cache = [];
		$empty_caption = '&nbsp;';

		$cacheKey = $userField['SETTINGS']['HLBLOCK_ID'].'_v'.$additionalParameters["VALUE"];

		if (!array_key_exists($cacheKey, $cache) && !empty($additionalParameters["VALUE"]))
		{
			$iterator = static::getList($userField);
			if (!$iterator)
			{
				return $empty_caption;
			}
			while ($arEnum = $iterator->GetNext())
			{
				$cache[$userField['SETTINGS']['HLBLOCK_ID'].'_v'.$arEnum["ID"]] = $arEnum["VALUE"];
			}
			unset($arEnum, $iterator);
		}
		if (!array_key_exists($cacheKey, $cache))
		{
			$cache[$cacheKey] = $empty_caption;
		}

		return $cache[$cacheKey];
	}

	public static function getDropDownData(): array
	{
		global $USER_FIELD_MANAGER;

		$hlblocks = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('order' => 'NAME'))->fetchAll();

		$list = [];

		foreach ($hlblocks as $hlblock)
		{
			// add hlblock itself
			$list[$hlblock['ID']] = [
				'name' => $hlblock['NAME'],
				'fields' => [
					0 => 'ID'
				]
			];

			$userfields = $USER_FIELD_MANAGER->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

			foreach ($userfields as $userfield)
			{
				$fieldTitle = $userfield['LIST_COLUMN_LABEL'] <> ''? $userfield['LIST_COLUMN_LABEL'] : $userfield['FIELD_NAME'];
				$list[$hlblock['ID']]['fields'][(int)$userfield['ID']] = $fieldTitle;
			}
		}

		return $list;
	}

	public static function getDropDownHtml($hlblockId = null, $hlfieldId = null, string $name = ''): string
	{
		if ($name === '')
		{
			$name = 'SETTINGS';
		}

		return self::getHighloadblockSelectorHtml(
			$name,
			[
				'HLBLOCK_ID' => (int)$hlblockId,
				'HLFIELD_ID' => (int)$hlfieldId,
			]
		);
	}

	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		if (!isset($userField['MULTIPLE']))
		{
			return null;
		}

		if (!empty($userField['SETTINGS']) && is_array($userField['SETTINGS']))
		{
			if ($userField['MULTIPLE'] === 'Y')
			{
				if (!is_array($userField['SETTINGS']['DEFAULT_VALUE']))
				{
					$userField['SETTINGS']['DEFAULT_VALUE'] = [
						$userField['SETTINGS']['DEFAULT_VALUE']
					];
				}
				$result = $userField['SETTINGS']['DEFAULT_VALUE'];
				Main\Type\Collection::normalizeArrayValuesByInt($result, false);
			}
			else
			{
				$result = (int)($userField['SETTINGS']['DEFAULT_VALUE'] ?? 0);
			}

			return $result;
		}

		return null;
	}

	/**
	 * Don't use. Added only for compatibility with potential box custom children.
	 * @deprecated deprecated since 24.0.0
	 *
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return array|mixed|string|null
	 */
	public static function getFieldValue(array $userField, array $additionalParameters = [])
	{
		return EnumType::getFieldValue($userField, $additionalParameters);
	}

	private static function getCurrentValue(array $userField, array $additionalParameters = [])
	{
		if (isset($additionalParameters['VALUE']))
		{
			return $additionalParameters['VALUE'];
		}

		return $userField['VALUE'] ?? null;
	}

	/**
	 * @param array $userField
	 * @return string
	 */
	public static function getEmptyCaption(array $userField): string
	{
		$message = (string)($userField['SETTINGS']['CAPTION_NO_VALUE'] ?? '');

		return
			$message !== ''
				? HtmlFilter::encode($userField['SETTINGS']['CAPTION_NO_VALUE'])
				: (string)Loc::getMessage('USER_TYPE_HLEL_NO_VALUE')
		;
	}

	public static function getDisplayTypeList(): array
	{
		return [
			static::DISPLAY_LIST => Loc::getMessage('USER_TYPE_HLEL_LIST'),
			static::DISPLAY_CHECKBOX => Loc::getMessage('USER_TYPE_HLEL_CHECKBOX'),
		];
	}

	private static function loadElement(array $settings, array $filter): ?array
	{
		global $USER_FIELD_MANAGER;

		if (!isset(self::$highloadblockIncluded))
		{
			self::$highloadblockIncluded = Loader::includeModule('highloadblock');
		}
		if (!self::$highloadblockIncluded)
		{
			return null;
		}

		$hlblockId = (int)($settings['HLBLOCK_ID'] ?? 0);
		$hlfieldId = (int)($settings['HLFIELD_ID'] ?? 0);
		if ($hlblockId <= 0)
		{
			return null;
		}

		$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::resolveHighloadblock($hlblockId);
		if ($hlblock === null)
		{
			return null;
		}

		$hlblock['ID'] = (int)$hlblock['ID'];
		$field = self::resolveField($hlblock['ID'], $hlfieldId);

		$entity = HighloadBlockTable::compileEntity($hlblock);
		$hlDataClass = $entity->getDataClass();

		$select = [
			'ID',
			$field['FIELD_NAME'],
		];

		$order = [
			'ID' => 'ASC',
		];

		$rows = [];

		$iterator = $hlDataClass::getList([
			'select' => $select,
			'filter' => $filter,
			'order' => $order,
		]);
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			if ($field['FIELD_NAME'] === 'ID')
			{
				$row['VALUE'] = $row['ID'];
			}
			else
			{
				$row['VALUE'] =
					$USER_FIELD_MANAGER->getListView($field, $row[$field['FIELD_NAME']])
					. ' ['.$row['ID'].']'
				;
			}
			$rows[$row['ID']] = $row['VALUE'];
		}
		unset($row, $iterator);

		return $rows;
	}

	private static function resolveField(int $hlblockId, int $hlfieldId): array
	{
		global $USER_FIELD_MANAGER;

		$defaultField = [
			'FIELD_NAME' => 'ID',
			'USER_TYPE_ID' => CUserTypeManager::BASE_TYPE_INT,
		];
		if ($hlfieldId <= 0)
		{
			return $defaultField;
		}

		$row = Main\UserFieldTable::getRow([
			'select' => [
				'*',
			],
			'filter' => [
				'=ENTITY_ID' => HighloadBlockTable::compileEntityId($hlblockId),
				'=ID' => $hlfieldId,
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		if ($row === null)
		{
			return $defaultField;
		}
		$row['USER_TYPE'] = $USER_FIELD_MANAGER->GetUserType($row['USER_TYPE_ID']);

		return $row;
	}
}
