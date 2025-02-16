<?php

namespace Bitrix\Iblock\UserField\Types;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;
use Bitrix\Iblock;
use CDBResult;
use CUserTypeManager;
use CIBlockSectionEnum;

/**
 * Class SectionType
 * @package Bitrix\Iblock\UserField\Types
 */
class SectionType extends ElementType
{
	public const
		USER_TYPE_ID = 'iblock_section',
		RENDER_COMPONENT = 'bitrix:iblock.field.section';

	/**
	 * @return array
	 */
	public static function getDescription(): array
	{
		return [
			'DESCRIPTION' => Loc::getMessage('USER_TYPE_IBSEC_DESCRIPTION'),
			'BASE_TYPE' => CUserTypeManager::BASE_TYPE_INT,
		];
	}

	/**
	 * @param array $userField
	 * @return string
	 */
	public static function onSearchIndex(array $userField): string
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
			$iterator = Iblock\SectionTable::getList([
				'select' => [
					'NAME',
					'LEFT_MARGIN',
				],
				'filter' => [
					'@ID' => $val,
				],
				'order' => [
					'LEFT_MARGIN' => 'ASC',
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
	 * @return bool|CDBResult
	 */
	public static function getList(array $userField)
	{
		if(self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		$section = false;

		if(self::$iblockIncluded && (int)$userField['SETTINGS']['IBLOCK_ID'])
		{
			$section = CIBlockSectionEnum::getTreeList(
				(int)$userField['SETTINGS']['IBLOCK_ID'],
				$userField['SETTINGS']['ACTIVE_FILTER']
			);
		}
		return $section;
	}

	/**
	 * @param array &$userField
	 * @param array $additionalParameters
	 * @return void
	 */
	public static function getEnumList(array &$userField, array $additionalParameters = []): void
	{
		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		if (!self::$iblockIncluded)
		{
			return;
		}

		$userField['MANDATORY'] ??= 'N';
		$userField['SETTINGS']['IBLOCK_ID'] ??= 0;
		$userField['SETTINGS']['SHOW_NO_VALUE'] ??= 'Y';
		$userField['SETTINGS']['DISPLAY'] ??= '';
		$userField['SETTINGS']['ACTIVE_FILTER'] ??= 'N';

		$result = [];
		$showNoValue = (
			$userField['MANDATORY'] !== 'Y'
			|| $userField['SETTINGS']['SHOW_NO_VALUE'] !== 'N'
			|| (
				isset($additionalParameters['SHOW_NO_VALUE'])
				&& $additionalParameters['SHOW_NO_VALUE'] === true
			)
		);

		if(
			$showNoValue
			&& (
				$userField['SETTINGS']['DISPLAY'] !== self::DISPLAY_CHECKBOX
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

		$sections = self::getElements(
			(int)$userField['SETTINGS']['IBLOCK_ID'],
			$filter
		);

		if (!is_array($sections))
		{
			return;
		}

		if (!empty($currentValues))
		{
			$result = $sections;
		}
		else
		{
			$result = array_replace($result, $sections);
		}

		$userField['USER_TYPE']['FIELDS'] = $result;
	}

	protected static function getElements(int $iblockId, array $additionalFilter = [])
	{
		if (self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}
		if ($iblockId <= 0 || !self::$iblockIncluded)
		{
			return null;
		}

		$additionalFilter['ACTIVE'] ??= false;

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
		$iterator = \CIBlockSection::GetList(
			[
				'LEFT_MARGIN' => 'ASC',
			],
			$filter,
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

		return $result;
	}

	/**
	 * Returns values for old format group action.
	 *
	 * @param array $userField User field description.
	 * @param array|null $additionalParameters Optional parameters.
	 * @return array
	 */
	public static function getGroupActionData(array $userField, ?array $additionalParameters): array
	{
		$result = [];
		$enum = call_user_func([$userField['USER_TYPE']['CLASS_NAME'], 'getlist'], $userField);
		if(!$enum)
		{
			return $result;
		}

		while ($item = $enum->GetNext())
		{
			$result[] = ['NAME' => $item['VALUE'], 'VALUE' => $item['ID']];
		}
		unset(
			$item,
			$enum,
		);

		return $result;
	}

	/**
	 * Returns default value, if exists.
	 *
	 * @param array $userField User field description.
	 * @param array $additionalParameters Optional parameters.
	 * @return array|string
	 */
	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		$value = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '');

		return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
	}
}
