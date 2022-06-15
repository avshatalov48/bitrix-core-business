<?php

namespace Bitrix\Catalog\Component\Preset;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

class Menu implements Preset
{
	public function enable()
	{
		Option::set('intranet', 'left_menu_crm_store_menu', 'Y');

		$this->clearCache();
	}

	public function disable()
	{
		Option::delete('intranet', ['name' => 'left_menu_crm_store_menu']);

		$this->clearCache();
	}

	public function isOn(): bool
	{
		return Option::get('intranet', 'left_menu_crm_store_menu', 'N') === 'Y';
	}

	protected function clearCache()
	{
		\CBitrixComponent::clearComponentCache('bitrix:menu');
		$GLOBALS['CACHE_MANAGER']->CleanDir('menu');
		$GLOBALS['CACHE_MANAGER']->ClearByTag('bitrix24_left_menu');

		if (Loader::includeModule('intranet'))
		{
			\Bitrix\Intranet\Composite\CacheProvider::deleteUserCache();
		}
	}
}