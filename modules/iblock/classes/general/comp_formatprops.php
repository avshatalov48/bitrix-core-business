<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\PropertyTable;

class CIBlockFormatProperties
{
	private static ?bool $b24Installed = null;

	private static array $userTypeCache = [];

	private static array $nameCache = [
		PropertyTable::TYPE_ELEMENT => [],
		PropertyTable::TYPE_SECTION => [],
	];

	private const USER_TYPE = 'UserType';

	public static function GetDisplayValue($arItem, $arProperty, $event1 = '')
	{
		if (self::$b24Installed === null)
		{
			self::$b24Installed = ModuleManager::isModuleInstalled('bitrix24');
		}

		$arProperty['RAW_PROPERTY_TYPE'] = $arProperty['PROPERTY_TYPE'];
		/** @var array $arUserTypeFormat */
		$arUserTypeFormat = false;
		if (!empty($arProperty['USER_TYPE']))
		{
			$userTypeId = $arProperty['USER_TYPE'];
			if (!isset(self::$userTypeCache[$userTypeId]))
			{
				self::$userTypeCache[$userTypeId] = false;
				$arUserType = CIBlockProperty::GetUserType($userTypeId);
				if (isset($arUserType['GetPublicViewHTML']))
				{
					self::$userTypeCache[$userTypeId] = $arUserType['GetPublicViewHTML'];
				}
				unset($arUserType);
			}
			$arUserTypeFormat = self::$userTypeCache[$userTypeId];
		}

		if ($arUserTypeFormat)
		{
			$arProperty['PROPERTY_TYPE'] = self::USER_TYPE;
			if ($arProperty['MULTIPLE'] === 'N' || !is_array($arProperty['~VALUE']))
			{
				$arValues = [$arProperty['~VALUE']];
			}
			else
			{
				$arValues = $arProperty['~VALUE'];
			}
		}
		else
		{
			if (is_array($arProperty['VALUE']))
			{
				$arValues = $arProperty['VALUE'];
			}
			else
			{
				$arValues = [$arProperty['VALUE']];
			}
		}
		$arDisplayValue = [];

		switch ($arProperty['PROPERTY_TYPE'])
		{
			case self::USER_TYPE:
				foreach ($arValues as $val)
				{
					$arDisplayValue[] = (string)call_user_func_array(
						$arUserTypeFormat,
						[
							$arProperty,
							['VALUE' => $val],
							[],
						]
					);
				}
				break;
			case PropertyTable::TYPE_ELEMENT:
				$arLinkElements = [];
				foreach ($arValues as $val)
				{
					$val = (int)$val;
					if ($val > 0)
					{
						if (!isset(self::$nameCache[PropertyTable::TYPE_ELEMENT][$val]))
						{
							//USED TO GET "LINKED" ELEMENTS
							$rsLink = CIBlockElement::GetList(
								[],
								[
									'ID' => $val,
									'ACTIVE' => 'Y',
									'ACTIVE_DATE' => 'Y',
									'CHECK_PERMISSIONS' => 'Y',
									'MIN_PERMISSION' => CIBlockRights::PUBLIC_READ,
								],
								false,
								false,
								[
									'ID',
									'IBLOCK_ID',
									'NAME',
									'DETAIL_PAGE_URL',
									'PREVIEW_PICTURE',
									'DETAIL_PICTURE',
									'SORT',
								]
							);
							self::$nameCache[PropertyTable::TYPE_ELEMENT][$val] = $rsLink->GetNext();
							unset($rsLink);
						}
						if (is_array(self::$nameCache[PropertyTable::TYPE_ELEMENT][$val]))
						{
							$row = self::$nameCache[PropertyTable::TYPE_ELEMENT][$val];
							if (self::$b24Installed)
							{
								$arDisplayValue[] = $row['NAME'];
							}
							else
							{
								$arDisplayValue[] = '<a href="' . $row['DETAIL_PAGE_URL'] . '">' . $row['NAME'] . '</a>';
							}
							$arLinkElements[$val] = $row;
							unset($row);
						}
					}
				}
				$arProperty['LINK_ELEMENT_VALUE'] = (!empty($arLinkElements) ? $arLinkElements : false);
				unset($arLinkElements);
				break;
			case PropertyTable::TYPE_SECTION:
				$arLinkSections = [];
				foreach ($arValues as $val)
				{
					$val = (int)$val;
					if ($val > 0)
					{
						if (!isset(self::$nameCache[PropertyTable::TYPE_SECTION][$val]))
						{
							//USED TO GET SECTIONS NAMES
							$rsSection = CIBlockSection::GetList(
								[],
								[
									'ID' => $val,
									'CHECK_PERMISSIONS' => 'Y',
									'MIN_PERMISSION' => CIBlockRights::PUBLIC_READ,
								],
								false,
								[
									'ID',
									'IBLOCK_ID',
									'NAME',
									'SECTION_PAGE_URL',
									'PICTURE',
									'DETAIL_PICTURE',
									'SORT',
								]
							);
							self::$nameCache[PropertyTable::TYPE_SECTION][$val] = $rsSection->GetNext();
							unset($rsSection);
						}
						if (is_array(self::$nameCache[PropertyTable::TYPE_SECTION][$val]))
						{
							$row = self::$nameCache[PropertyTable::TYPE_SECTION][$val];
							if (self::$b24Installed)
							{
								$arDisplayValue[] = $row['NAME'];
							}
							else
							{
								$arDisplayValue[] = '<a href="' . $row['SECTION_PAGE_URL'] . '">' . $row['NAME'] . '</a>';
							}
							$arLinkSections[$val] = self::$nameCache[PropertyTable::TYPE_SECTION][$val];
						}
					}
				}
				$arProperty['LINK_SECTION_VALUE'] = (!empty($arLinkSections) ? $arLinkSections : false);
				unset($arLinkSections);
				break;
			case PropertyTable::TYPE_LIST:
				$isCheckBox = Tools::isCheckboxProperty($arProperty);
				foreach ($arValues as $val)
				{
					$val = (string)$val;
					if ($isCheckBox)
					{
						if ($val === Tools::CHECKBOX_VALUE_YES)
						{
							$arDisplayValue[] = Loc::getMessage('IBLOCK_FORMATPROPS_PROPERTY_YES');
						}
						else
						{
							$arDisplayValue[] = Loc::getMessage('IBLOCK_FORMATPROPS_PROPERTY_NO');
						}
					}
					else
					{
						if ($val !== '')
						{
							$arDisplayValue[] = $val;
						}
					}
				}
				unset($isCheckBox);
				break;
			case PropertyTable::TYPE_FILE:
				$arFiles = [];
				foreach ($arValues as $val)
				{
					if ($arFile = CFile::GetFileArray($val))
					{
						$arFiles[] = $arFile;
						$arDisplayValue[] =
							'<a href="' . htmlspecialcharsbx($arFile['SRC']) . '">'
							. Loc::getMessage('IBLOCK_DOWNLOAD')
							. '</a>'
						;
					}
				}
				$fileCount = count($arFiles);
				if ($fileCount == 1)
				{
					$arProperty['FILE_VALUE'] = $arFiles[0];
				}
				elseif ($fileCount > 1)
				{
					$arProperty['FILE_VALUE'] = $arFiles;
				}
				else
				{
					$arProperty['FILE_VALUE'] = false;
				}
				unset($fileCount, $arFiles);
				break;
			default:
				foreach ($arValues as $val)
				{
					$trimmed = trim((string)$val);
					if (strpos($trimmed, 'http') === 0)
					{
						$arDisplayValue[] =  '<a href="' . htmlspecialcharsbx($trimmed) . '">' . $trimmed . '</a>';
					}
					elseif (strpos($trimmed, 'www') === 0)
					{
						$arDisplayValue[] =  '<a href="' . htmlspecialcharsbx('https://' . $trimmed) . '">' . $trimmed . '</a>';
					}
					else
					{
						$arDisplayValue[] = $val;
					}
				}
				break;
		}

		$displayCount = count($arDisplayValue);
		if ($displayCount == 1)
		{
			$arProperty['DISPLAY_VALUE'] = $arDisplayValue[0];
		}
		elseif ($displayCount > 1)
		{
			$arProperty['DISPLAY_VALUE'] = $arDisplayValue;
		}
		else
		{
			$arProperty['DISPLAY_VALUE'] = false;
		}

		$arProperty['PROPERTY_TYPE'] = $arProperty['RAW_PROPERTY_TYPE'];
		unset($arProperty['RAW_PROPERTY_TYPE']);

		return $arProperty;
	}

	/**
	 * @param string $format
	 * @param int $timestamp
	 * @return string
	 */
	public static function DateFormat($format, $timestamp)
	{
		global $DB;

		switch ($format)
		{
			case 'SHORT':
				return FormatDate($DB->DateFormatToPHP(FORMAT_DATE), $timestamp);
			case 'FULL':
				return FormatDate($DB->DateFormatToPHP(FORMAT_DATETIME), $timestamp);
			default:
				return FormatDate($format, $timestamp);
		}
	}

	public static function clearCache(): void
	{
		self::$userTypeCache = [];
		self::$nameCache = [
			PropertyTable::TYPE_ELEMENT => [],
			PropertyTable::TYPE_SECTION => [],
		];
	}
}
