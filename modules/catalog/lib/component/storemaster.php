<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Main\Config\Option;

/**
 * Class StoreMaster
 *
 * @package Bitrix\Catalog\Component
 */
final class StoreMaster
{
	private const IS_MASTER_USED_OPTION = 'use_store_master_used';

	/**
	 * @return bool
	 */
	public static function isUsed(): bool
	{
		return Option::get('catalog', self::IS_MASTER_USED_OPTION, 'N') === 'Y';
	}

	public static function setIsUsed(): void
	{
		Option::set('catalog', self::IS_MASTER_USED_OPTION, 'Y');
	}
}
