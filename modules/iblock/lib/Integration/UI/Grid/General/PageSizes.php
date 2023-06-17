<?php

namespace Bitrix\Iblock\Integration\UI\Grid\General;

/**
 * Typical page sizes in the grid.
 */
interface PageSizes
{
	public const SIZE_5_10_20 = [
		['NAME' => '5', 'VALUE' => '5'],
		['NAME' => '10', 'VALUE' => '10'],
		['NAME' => '20', 'VALUE' => '20'],
	];

	public const SIZE_10_20_50 = [
		['NAME' => '10', 'VALUE' => '10'],
		['NAME' => '20', 'VALUE' => '20'],
		['NAME' => '50', 'VALUE' => '50'],
	];

	public const SIZE_5_10_20_50_100 = [
		['NAME' => '5', 'VALUE' => '5'],
		['NAME' => '10', 'VALUE' => '10'],
		['NAME' => '20', 'VALUE' => '20'],
		['NAME' => '50', 'VALUE' => '50'],
		['NAME' => '100', 'VALUE' => '100'],
	];
}
