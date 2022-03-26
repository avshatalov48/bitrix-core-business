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
		if(self::$iblockIncluded === null)
		{
			self::$iblockIncluded = Loader::includeModule('iblock');
		}

		if(!self::$iblockIncluded)
		{
			return;
		}

		$result = [];
		$showNoValue = (
			$userField['MANDATORY'] !== 'Y'
			||
			$userField['SETTINGS']['SHOW_NO_VALUE'] !== 'N'
			||
			(
				isset($additionalParameters['SHOW_NO_VALUE'])
				&&
				$additionalParameters['SHOW_NO_VALUE'] === true
			)
		);

		if(
			$showNoValue
			&&
			(
				$userField['SETTINGS']['DISPLAY'] !== self::DISPLAY_CHECKBOX
				||
				$userField['MULTIPLE'] !== 'Y'
			)
		)
		{
			$result = [
				null => static::getEmptyCaption($userField)
			];
		}

		$sectionEnumList = CIBlockSectionEnum::getTreeList(
			(int)$userField['SETTINGS']['IBLOCK_ID'],
			$userField['SETTINGS']['ACTIVE_FILTER']
		);

		if(!is_object($sectionEnumList))
		{
			return;
		}

		while($section = $sectionEnumList->Fetch())
		{
			$result[$section['ID']] = $section['NAME'];
		}

		$userField['USER_TYPE']['FIELDS'] = $result;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
	 * @return array|string
	 */
	public static function getDefaultValue(array $userField, array $additionalParameters = [])
	{
		$value = ($userField['SETTINGS']['DEFAULT_VALUE'] ?? '');
		return ($userField['MULTIPLE'] === 'Y' ? [$value] : $value);
	}
}
