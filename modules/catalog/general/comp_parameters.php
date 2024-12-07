<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;

class CCatalogIBlockParameters
{
	/**
	 * @return array
	 */
	public static function GetCatalogSortFields()
	{
		$result = [
			'CATALOG_AVAILABLE' => Loc::getMessage('IBLOCK_SORT_FIELDS_CATALOG_AVAILABLE_EXT'),
			//'CATALOG_WEIGHT' => Loc::getMessage('IBLOCK_SORT_FIELDS_CATALOG_WEIGHT')
		];

		foreach (\CCatalogGroup::getListArray() as $row)
		{
			$id = 'SCALED_PRICE_'.$row['ID'];
			$title = (string)$row['NAME_LANG'];
			$result[$id] = '['.$row['ID'].'] ['.$row['NAME'].']'.($title != '' ? ' '.$title : '').' '.
				Loc::getMessage('IBLOCK_SORT_FIELDS_PRICE_WITHOUT_DISCOUNT');
		}
		unset($title, $id, $row);

		return $result;
	}

	/**
	 * @deprected deprecated since catalog 16.5.2
	 * see \Bitrix\Catalog\Helpers\Admin\Tools::getPriceTypeList
	 *
	 * @param bool $useId
	 * @return array
	 */
	public static function getPriceTypesList($useId = false)
	{
		$useId = ($useId === true);
		return Catalog\Helpers\Admin\Tools::getPriceTypeList(!$useId);
	}
}
