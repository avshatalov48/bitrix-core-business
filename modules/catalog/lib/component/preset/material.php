<?php

namespace Bitrix\Catalog\Component\Preset;

use Bitrix\Main\Config\Option;

class Material implements Preset
{
	public function enable()
	{
		Option::set('catalog', 'preset_store_material', 'Y');
	}

	public function disable()
	{
		Option::delete('catalog', ['name' => 'preset_store_material']);
	}

	public function isOn(): bool
	{
		return Option::get('catalog', 'preset_store_material', 'N') === 'Y';
	}
}