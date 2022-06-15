<?php

namespace Bitrix\Catalog\Component\Preset;

use Bitrix\Catalog\Component\UseStore;
use Bitrix\Main\Config\Option;

class Store implements Preset
{
	public function enable()
	{
		Option::set('catalog', 'preset_store_catalog_stores', 'Y');

		UseStore::installCatalogStores();
	}

	public function disable()
	{
		Option::delete('catalog', ['name' => 'preset_store_catalog_stores']);
	}

	public function isOn(): bool
	{
		return Option::get('catalog', 'preset_store_catalog_stores', 'N') === 'Y';
	}
}