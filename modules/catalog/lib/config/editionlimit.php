<?
namespace Bitrix\Catalog\Config;

use Bitrix\Catalog;

class EditionLimit
{
	public static function isExceededPriceTypeLimit()
	{
		if (Feature::isMultiPriceTypesEnabled())
			return false;

		//TODO: enable managed cache after blocked old api \CCatalogGroup
		return Catalog\GroupTable::getCount([]) > 1;
	}

	public static function isExceededStoreLimit()
	{
		if (Feature::isMultiStoresEnabled())
			return false;

		//TODO: enable managed cache after blocked old api \CCatalogStore
		return Catalog\StoreTable::getCount([]) > 1;
	}
}