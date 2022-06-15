<?php

namespace Bitrix\Catalog\Component\Preset;

class Factory
{
	/**
	 * @param $type
	 * @return Crm|Material|Menu|Store|null
	 */
	static public function create($type)
	{
		if ($type == Enum::TYPE_CRM)
		{
			return new Crm();
		}
		elseif ($type == Enum::TYPE_MATERIAL)
		{
			return new Material();
		}
		elseif ($type == Enum::TYPE_MENU)
		{
			return new Menu();
		}
		elseif ($type == Enum::TYPE_STORE)
		{
			return new Store();
		}
		return null;
	}
}