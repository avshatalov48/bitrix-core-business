<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

class CCatalogIBlockParameters
{
	public static function GetCatalogSortFields()
	{
		return array(
			'CATALOG_AVAILABLE' => Loc::getMessage('IBLOCK_SORT_FIELDS_CATALOG_AVAILABLE_EXT'),
			//'CATALOG_WEIGHT' => Loc::getMessage('IBLOCK_SORT_FIELDS_CATALOG_WEIGHT')
		);
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