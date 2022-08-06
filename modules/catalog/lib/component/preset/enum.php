<?php

namespace Bitrix\Catalog\Component\Preset;

class Enum
{
	const TYPE_CRM = 'crm';
	const TYPE_MENU = 'menu';
	const TYPE_STORE = 'store';
	const TYPE_MATERIAL = 'material';

	static public function getAllType(): array
	{
		return [
			self::TYPE_CRM,
			self::TYPE_MENU,
			self::TYPE_STORE,
			self::TYPE_MATERIAL
		];
	}

	static public function getUseAllType(): array
	{
		return [
			self::TYPE_CRM,
			self::TYPE_MENU,
			self::TYPE_STORE,
		];
	}
}