<?php

namespace Bitrix\Calendar\Sync\Office365\Converter;

class ColorConverter
{
	private const OFFICE_DEFAULT_COLOR = 'auto';
	private const BITRIX_DEFAULT_COLOR = '#838fa0';

	private const MAP_EXPORT_INT = [
		'#86b100' => '1',
		'#0092cc' => '0',
		'#00afc7' => '5',
		'#e89b06' => '7',
		'#00b38c' => '-1', //
		'#de2b24' => '2',
		'#838fa0' => '6',
		'#c3612c' => '4',
		'#e97090' => '-1',
	];

	private const MAP_EXPORT = [
		'#86b100' => 'lightGreen',
		'#0092cc' => 'lightBlue',
		'#00afc7' => 'lightTeal',
		'#c3612c' => 'lightBrown',
		'#e89b06' => 'lightOrange',
//		'#838fa0' => 'lightYellow',
		'#bd7ac9' => 'lightPink',
		'#838fa0' => 'lightGray',
		'#de2b24' => 'lightRed',
	];

	private const MAP_IMPORT = [
		'lightGreen' => '#86b100',
		'lightBlue' => '#0092cc',
		'lightTeal' => '#00afc7',
		'lightBrown' => '#c3612c',
		'lightOrange' => '#e89b06',
//		'lightYellow' => '#838fa0',
		'lightPink' => '#bd7ac9',
		'lightGray' => '#838fa0',
		'lightRed' => '#de2b24',
	];

	private const OFFICE_COLORS = [
		'auto' => -1,
		'lightBlue' => 0,
		'lightGreen' => 1,
		'lightOrange' => 2,
		'lightGray' => 3,
		'lightYellow' => 4,
		'lightTeal' => 5,
		'lightPink' => 6,
		'lightBrown' => 7,
		'lightRed' => 8,
		'maxColor' => 9,
	];

	private const OUR_COLORS = [
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
	 * @param string|null $color
	 *
	 * @return string
	 */
	public static function toOfficCode(?string $color = null): string
	{
		return self::MAP_EXPORT_INT[$color] ?? '-1';
	}

	/**
	 * @param string|null $color
	 *
	 * @return string
	 */
	public static function toOffice(?string $color = null): string
	{
		return self::MAP_EXPORT[$color] ?? self::OFFICE_DEFAULT_COLOR;
	}

	/**
	 * @param string $color
	 * @param string|null $hexColor
	 *
	 * @return string
	 */
	public static function fromOffice(string $color, ?string $hexColor = null): ?string
	{
		return $hexColor ?:
			self::MAP_IMPORT[$color]
			?? null
			;
	}
}
