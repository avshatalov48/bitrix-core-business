<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main\Localization\Loc;

/**
 * Class KkmRepository
 *
 * @package Bitrix\Sale\Cashbox
 */
class KkmRepository
{
	public const ATOL = 'atol';
	public const SHTRIHM = 'shtrih-m';
	public const EVOTOR = 'evotor';

	/**
	 * @return array
	 */
	public static function getAll(): array
	{
		return [
			self::ATOL => [
				'NAME' => Loc::getMessage('SALE_CASHBOX_KKM_ATOL')
			],
			self::SHTRIHM => [
				'NAME' => Loc::getMessage('SALE_CASHBOX_KKM_SHTRIHM')
			],
			self::EVOTOR => [
				'NAME' => Loc::getMessage('SALE_CASHBOX_KKM_EVOTOR')
			],
		];
	}

	/**
	 * @param string $code
	 * @return array|null
	 */
	public static function getByCode(string $code): ?array
	{
		$allKkm = self::getAll();

		return $allKkm[$code] ?? null;
	}
}
