<?php

namespace Bitrix\Iblock\UserField\Types;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CDBResult;
use CUserTypeManager;
use CIBlockSectionEnum;

Loc::loadMessages(__FILE__);

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

		if(is_array($userField['VALUE']))
		{
			$val = $userField['VALUE'];
		}
		else
		{
			$val = [$userField['VALUE']];
		}

		$val = array_filter($val, 'strlen');

		if(count($val) && Loader::includeModule('iblock'))
		{
			$ob = new \CIBlockSection();
			$rs = $ob->GetList(
				['left_margin' => 'asc'],
				['=ID' => $val],
				false,
				['NAME']
			);

			while($ar = $rs->Fetch())
			{
				$res .= $ar['NAME'] . '\r\n';
			}
		}

		return $res;
	}

	public static function renderField(array $userField, ?array $additionalParameters = []): string
	{
		static::getEnumList($userField);
		return parent::renderField($userField, $additionalParameters);
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

		$elementEnumList = false;

		if(self::$iblockIncluded && (int)$userField['SETTINGS']['IBLOCK_ID'])
		{
			$sectionEnum = new CIBlockSectionEnum();
			$section = $sectionEnum::getTreeList(
				(int)$userField['SETTINGS']['IBLOCK_ID'],
				$userField['SETTINGS']['ACTIVE_FILTER']
			);
		}
		return $section;
	}

	/**
	 * @param array $userField
	 * @param array $additionalParameters
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

		$sectionEnum = new CIBlockSectionEnum();
		$sectionEnumList = $sectionEnum::getTreeList(
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
}