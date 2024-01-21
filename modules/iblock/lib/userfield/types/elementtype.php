<?php

namespace Bitrix\Iblock\UserField\Types;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\Type;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Iblock;
use CDBResult;
use CUserTypeManager;

/**
 * Class ElementType
 * @package Bitrix\Iblock\UserField\Types
 */
class ElementType extends BaseType
{
	public const USER_TYPE_ID = 'iblock_element';
	public const RENDER_COMPONENT = 'bitrix:iblock.field.element';

	public const DISPLAY_LIST = 'LIST';
	public const DISPLAY_CHECKBOX = 'CHECKBOX';
	public const DISPLAY_UI = 'UI';
	public const DISPLAY_DIALOG = 'DIALOG';

	protected static ?bool $iblockIncluded = null;

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_IBEL_DESCRIPTION'),
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
		static::getEnumList($userField, $additionalParameters);

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
		$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
		if(!$enum)
		{
			return '';
		}
		$items = [];
		while($item = $enum->GetNext())
		{
			$items[$item['ID']] = $item;
		}
		$additionalParameters['items'] = $items;

		return parent::renderEditForm($userField, $additionalParameters);
	}

	public static function renderFilter(array $userField, ?array $additionalParameters): string
	{
		$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
		if(!$enum)
		{
			return '';
		}
		$items = [];
		while($item = $enum->GetNext())
		{
			$items[$item['ID']] = $item['VALUE'];
		}
		$additionalParameters['items'] = $items;
		return parent::renderFilter($userField, $additionalParameters);
	}

	public static function renderAdminListView(array $userField, ?array $additionalParameters): string
	{
		static $cache = [];
		$empty_caption = '&nbsp;';

		if(!array_key_exists($additionalParameters['VALUE'], $cache))
		{
			$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
			if(!$enum)
			{
				$additionalParameters['VALUE'] = $empty_caption;
				return parent::renderAdminListView($userField, $additionalParameters);
			}
			while($item = $enum->GetNext())
			{
				$cache[$item['ID']] = $item['VALUE'];
			}
		}
		if(!array_key_exists($additionalParameters['VALUE'], $cache))
		{
			$cache[$additionalParameters['VALUE']] = $empty_caption;
		}

		$additionalParameters['VALUE'] = $cache[$additionalParameters['VALUE']];
		return parent::renderAdminListView($userField, $additionalParameters);
	}

	public static function renderAdminListEdit(array $userField, ?array $additionalParameters): string
	{
		$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
		$values = [];
		if ($enum)
		{
			while($item = $enum->GetNext())
			{
				$values[$item['ID']] = $item['VALUE'];
			}
		}
		$additionalParameters['enumItems'] = $values;

		return parent::renderAdminListEdit($userField, $additionalParameters);
	}

	/**
	 * @return string
	 */
	public static function getDbColumnType(): string
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		return $helper->getColumnTypeByField(new \Bitrix\Main\ORM\Fields\IntegerField('x'));
	}

	/**
	 * @param array $userField
	 * @param string|array $value
	 * @return array
	 */
	public static function checkFields(array $userField, $value): array
	{
		return [];
	}

	/**
	 * @param array $userField
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$height = (int)($userField['SETTINGS']['LIST_HEIGHT'] ?? 1);
		$disp = ($userField['SETTINGS']['DISPLAY'] ?? '');

		if ($disp !== static::DISPLAY_CHECKBOX && $disp !== static::DISPLAY_LIST)
		{
			$disp = static::DISPLAY_LIST;
		}

		$iblockId = (int)($userField['SETTINGS']['IBLOCK_ID'] ?? 0);

		if($iblockId <= 0)
		{
			$iblockId = '';
		}

		$elementId = (int)($userField['SETTINGS']['DEFAULT_VALUE'] ?? 0);

		if($elementId <= 0)
		{
			$elementId = '';
		}

		$activeFilter = (($userField['SETTINGS']['ACTIVE_FILTER'] ?? '') === 'Y' ? 'Y' : 'N');

		return [
			'DISPLAY' => $disp,
			'LIST_HEIGHT' => (max($height, 1)),
			'IBLOCK_ID' => $iblockId,
			'DEFAULT_VALUE' => $elementId,
			'ACTIVE_FILTER' => $activeFilter,
		];
	}

	/**
	 * @param array $userField
	 * @return string|null
	 */
	public static function onSearchIndex(array $userField): ?string
	{
		$res = '';
		if (!isset($userField['VALUE']))
		{
			return $res;
		}

		if (is_array($userField['VALUE']))
		{
			$val = $userField['VALUE'];
		}
		else
		{
			$val = [$userField['VALUE']];
		}

		Type\Collection::normalizeArrayValuesByInt($val);

		if (!empty($val) && Loader::includeModule('iblock'))
		{
			$iterator = Iblock\ElementTable::getList([
				'select' => [
					'NAME',
				],
				'filter' => [
					'@ID' => $val,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$res .= $row['NAME'] . "\r\n";
			}
			unset($row, $iterator);
		}
		unset($val);

		return $res;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return array
	 */
	public static function getFilterData(array $userField, array $additionalParameters): array
	{
		$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
		$items = [];
		if($enum)
		{
			while($item = $enum->GetNext())
			{
				$items[$item['ID']] = $item['VALUE'];
			}
		}
		return [
			'id' => $additionalParameters['ID'],
			'name' => $additionalParameters['NAME'],
			'type' => 'list',
			'items' => $items,
			'params' => ['multiple' => 'Y'],
			'filterable' => ''
		];
	}

	/**
	 * @param array $userField
	 * @return bool|CDBResult
	 */
	public static function getList(array $userField)
	{
		$iblockId = (int)($userField['SETTINGS']['IBLOCK_ID'] ?? 0);
		$activeFilter = (string)($userField['SETTINGS']['ACTIVE_FILTER'] ?? 'N');

		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}
		if ($iblockId <= 0 || !self::$iblockIncluded)
		{
			return false;
		}

		$cacheTtl = 86400;

		$iblockRights = self::getIblockRightsMode($iblockId, $cacheTtl);
		if ($iblockRights === null)
		{
			return false;
		}

		$result = false;
		$filter = [
			'IBLOCK_ID' => $iblockId
		];
		if ($iblockRights === Iblock\IblockTable::RIGHTS_SIMPLE)
		{
			if ($activeFilter === 'Y')
			{
				$filter['=ACTIVE'] = 'Y';
			}

			$rows = [];
			$elements = \Bitrix\Iblock\ElementTable::getList([
				'select' => [
					'ID',
					'NAME',
				],
				'filter' => \CIBlockElement::getPublicElementsOrmFilter($filter),
				'order' => [
					'NAME' => 'ASC',
					'ID' => 'ASC',
				],
				'cache' => [
					'ttl' => $cacheTtl,
				],
			]);

			while($element = $elements->fetch())
			{
				$rows[] = $element;
			}
			unset($elements);

			if (!empty($rows))
			{
				$result = new \CIBlockElementEnum();
				$result->InitFromArray($rows);
			}
			unset($rows);
		}
		else
		{
			$filter['CHECK_PERMISSIONS'] = 'Y';
			$filter['MIN_PERMISSION'] = \CIBlockRights::PUBLIC_READ;
			if ($activeFilter === 'Y')
			{
				$filter['ACTIVE'] = 'Y';
			}

			$result = \CIBlockElement::GetList(
				[
					'NAME' => 'ASC',
					'ID' => 'ASC',
				],
				$filter,
				false,
				false,
				[
					'ID',
					'NAME',
				]
			);

			if($result)
			{
				$result = new \CIBlockElementEnum($result);
			}
		}

		return $result;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 */
	public static function getEnumList(array &$userField, array $additionalParameters = []): void
	{
		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		$userField['MANDATORY'] ??= 'N';
		$userField['SETTINGS']['IBLOCK_ID'] ??= 0;
		$userField['SETTINGS']['SHOW_NO_VALUE'] ??= 'Y';
		$userField['SETTINGS']['DISPLAY'] ??= '';
		$userField['SETTINGS']['ACTIVE_FILTER'] ??= 'N';

		if (
			!self::$iblockIncluded
			|| (int)$userField['SETTINGS']['IBLOCK_ID'] <= 0
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
			$currentValues = static::getFieldValue($userField, $additionalParameters);
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
		$filter['ACTIVE'] = $userField['SETTINGS']['ACTIVE_FILTER'] === 'Y';

		$elements = self::getElements(
			(int)$userField['SETTINGS']['IBLOCK_ID'],
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

	/**
	 * @param array $userField
	 * @return string
	 */
	public static function getEmptyCaption(array $userField): string
	{
		$message = ($userField['SETTINGS']['CAPTION_NO_VALUE'] ?? '');
		return
			$message !== ''
				? HtmlFilter::encode($userField['SETTINGS']['CAPTION_NO_VALUE'])
				: Loc::getMessage('USER_TYPE_IBEL_NO_VALUE')
		;
	}

	/**
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string
	 */
	public static function getAdminListEditHtmlMulty(array $userField, ?array $additionalParameters): string
	{
		return static::renderAdminListEdit($userField, $additionalParameters);
	}

	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		$value = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '');
		return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
	}

	public static function onBeforeSave($userField, $value)
	{
		return ($userField['MULTIPLE'] !== 'Y' && is_array($value)) ? array_shift($value) : $value;
	}

	public static function getFieldValue(array $userField, array $additionalParameters = [])
	{
		$valueFromForm = ($additionalParameters['bVarsFromForm'] ?? false);
		if (!$valueFromForm && !isset($additionalParameters['VALUE']))
		{
			if(
				isset($userField['ENTITY_VALUE_ID'], $userField['ENUM'])
				&& $userField['ENTITY_VALUE_ID'] <= 0
			)
			{
				$value = ($userField['MULTIPLE'] === 'Y' ? [] : null);
				foreach($userField['ENUM'] as $enum)
				{
					if($enum['DEF'] === 'Y')
					{
						if($userField['MULTIPLE'] === 'Y')
						{
							$value[] = $enum['ID'];
						}
						else
						{
							$value = $enum['ID'];
							break;
						}
					}
				}
			}
			else
			{
				$value = $userField['VALUE'] ?? null;
			}
		}
		elseif(isset($additionalParameters['VALUE']))
		{
			$value = $additionalParameters['VALUE'];
		}
		else
		{
			$value = Context::getCurrent()->getRequest()->get($userField['FIELD_NAME']);
		}

		return $value;
	}

	protected static function getElements(int $iblockId, array $additionalFilter = [])
	{
		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}
		if ($iblockId <= 0 || !self::$iblockIncluded)
		{
			return false;
		}

		$cacheTtl = 86400;

		$iblockRights = self::getIblockRightsMode($iblockId, $cacheTtl);
		if ($iblockRights === null)
		{
			return false;
		}

		if ($iblockRights === Iblock\IblockTable::RIGHTS_SIMPLE)
		{
			$filter = ['IBLOCK_ID' => $iblockId];
			if ($additionalFilter['ACTIVE'])
			{
				$filter['=ACTIVE'] = 'Y';
			}
			if (isset($additionalFilter['ID']))
			{
				$filter['@ID'] = $additionalFilter['ID'];
			}

			$result = [];
			$elements = \Bitrix\Iblock\ElementTable::getList([
				'select' => [
					'ID',
					'NAME',
				],
				'filter' => \CIBlockElement::getPublicElementsOrmFilter($filter),
				'order' => [
					'NAME' => 'ASC',
					'ID' => 'ASC',
				],
				'cache' => [
					'ttl' => $cacheTtl,
				],
			]);

			while($element = $elements->fetch())
			{
				$result[$element['ID']] = $element['NAME'];
			}
			unset($element, $elements);

			if (empty($result))
			{
				$result = false;
			}
		}
		else
		{
			$filter = [
				'IBLOCK_ID' => $iblockId,
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => \CIBlockRights::PUBLIC_READ,
			];
			if ($additionalFilter['ACTIVE'])
			{
				$filter['ACTIVE'] = 'Y';
			}
			if (isset($additionalFilter['ID']))
			{
				$filter['ID'] = $additionalFilter['ID'];
			}

			$result = [];
			$iterator = \CIBlockElement::GetList(
				[
					'NAME' => 'ASC',
					'ID' => 'ASC',
				],
				$filter,
				false,
				false,
				[
					'ID',
					'NAME',
				]
			);

			while ($element = $iterator->Fetch())
			{
				$result[$element['ID']] = $element['NAME'];
			}
			unset($element, $iterator);
		}

		return $result;
	}

	private static function getIblockRightsMode(int $iblockId, int $cacheTtl): ?string
	{
		$iblock = Iblock\IblockTable::getRow([
			'select' => [
				'ID',
				'RIGHTS_MODE',
			],
			'filter' => [
				'=ID' => $iblockId
			],
			'cache' => [
				'ttl' => $cacheTtl,
			],
		]);

		return ($iblock['RIGHTS_MODE'] ?? null);
	}
}
