<?php

namespace Bitrix\Calendar\Core\Property;

class ColorHelper
{
	public const OUR_COLORS = [
		'#86b100', // 1
		'#0092cc', // 2
		'#00afc7', // 3
		'#e89b06', // 4
		'#00b38c', // 5
		'#de2b24', // 6
		'#bd7ac9', // 7
		'#838fa0', // 8
		'#c3612c', // 9
		'#e97090', // 10
	];

	/**
	 * @return string
	 */
	public static function getOurColorRandom(): string
	{
		return self::OUR_COLORS[array_rand(self::OUR_COLORS)];
	}
}