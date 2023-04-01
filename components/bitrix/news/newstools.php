<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

use Bitrix\Main;
use Bitrix\Iblock;

class CNewsTools
{
	public static function OnSearchGetURL($arFields)
	{
		global $BX_NEWS_DETAIL_URL, $BX_NEWS_SECTION_URL;

		static $arIBlockCache = [];

		if ($arFields["MODULE_ID"] !== "iblock" || mb_substr($arFields["URL"], 0, 1) !== "=")
		{
			return $arFields["URL"];
		}

		if(!Main\Loader::includeModule('iblock'))
		{
			return '';
		}

		$IBLOCK_ID = (int)($arFields["PARAM2"] ?? 0);
		if ($IBLOCK_ID <= 0)
		{
			return '';
		}

		if (!isset($arIBlockCache[$IBLOCK_ID]))
		{
			$arIBlockCache[$IBLOCK_ID] = Iblock\IblockTable::getList([
				'select' => [
					'DETAIL_PAGE_URL',
					'SECTION_PAGE_URL',
					'IBLOCK_CODE' => 'CODE',
					'IBLOCK_EXTERNAL_ID' => 'XML_ID',
					'IBLOCK_TYPE_ID',
				],
				'filter' => [
					'=ID' => $IBLOCK_ID,
				],
			])->fetch();
		}

		if (!is_array($arIBlockCache[$IBLOCK_ID]))
		{
			return '';
		}

		$arr = [];
		$arFields["URL"] = ltrim($arFields["URL"], " =");
		parse_str($arFields["URL"], $arr);
		$arr = $arIBlockCache[$IBLOCK_ID] + $arr;
		$arr["LANG_DIR"] = $arFields["DIR"];

		if(mb_substr($arFields["ITEM_ID"], 0, 1) !== 'S')
		{
			return CIBlock::ReplaceDetailUrl($BX_NEWS_DETAIL_URL, $arr, true, "E");
		}
		else
		{
			return CIBlock::ReplaceDetailUrl($BX_NEWS_SECTION_URL, $arr, true, "S");
		}
	}
}
