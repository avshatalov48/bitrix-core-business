<?php

namespace Bitrix\Iblock\UserField\Types;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserField\Types\EnumType;
use CDBResult;
use CUserTypeManager;
use CIBlockElementEnum;

Loc::loadMessages(__FILE__);

/**
 * Class ElementType
 * @package Bitrix\Iblock\UserField\Types
 */
class ElementType extends EnumType
{
	public const
		USER_TYPE_ID = 'iblock_element',
		RENDER_COMPONENT = 'bitrix:iblock.field.element';

	protected static
		$iblockIncluded = null;

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
	 * @return array
	 */
	public static function prepareSettings(array $userField): array
	{
		$height = (int)$userField['SETTINGS']['LIST_HEIGHT'];
		$disp = $userField['SETTINGS']['DISPLAY'];

		if($disp !== static::DISPLAY_CHECKBOX && $disp !== static::DISPLAY_LIST)
		{
			$disp = static::DISPLAY_LIST;
		}

		$iblockId = (int)$userField['SETTINGS']['IBLOCK_ID'];

		if($iblockId <= 0)
		{
			$iblockId = '';
		}

		$elementId = (int)$userField['SETTINGS']['DEFAULT_VALUE'];

		if($elementId <= 0)
		{
			$elementId = '';
		}

		$activeFilter = ($userField['SETTINGS']['ACTIVE_FILTER'] === 'Y' ? 'Y' : 'N');

		return [
			'DISPLAY' => $disp,
			'LIST_HEIGHT' => ($height < 1 ? 1 : $height),
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
			$ob = new \CIBlockElement();
			$rs = $ob->GetList(
				[],
				['=ID' => $val],
				false,
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
			$elementEnum = new CIBlockElementEnum();
			$elementEnumList = $elementEnum::getTreeList(
				(int)$userField['SETTINGS']['IBLOCK_ID'],
				$userField['SETTINGS']['ACTIVE_FILTER']
			);
		}
		return $elementEnumList;
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

		if(
			!self::$iblockIncluded
			||
			(int)$userField['SETTINGS']['IBLOCK_ID'] <= 0
		)
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
				$userField['SETTINGS']['DISPLAY'] !== 'CHECKBOX'
				||
				$userField['MULTIPLE'] !== 'Y'
			)
		)
		{
			$result = [
				null => static::getEmptyCaption($userField)
			];
		}

		$elementEnum = new CIBlockElementEnum();
		$elementEnumList = $elementEnum::getTreeList(
			(int)$userField['SETTINGS']['IBLOCK_ID'],
			$userField['SETTINGS']['ACTIVE_FILTER']
		);

		if(!is_object($elementEnumList))
		{
			return;
		}

		while($element = $elementEnumList->Fetch())
		{
			$result[$element['ID']] = $element['NAME'];
		}

		$userField['USER_TYPE']['FIELDS'] = $result;
	}
}