<?php

namespace Bitrix\Sale\Cashbox;

/**
 * Class MeasureCodeToTag2108Mapper
 *
 * @package Bitrix\Sale\Cashbox
 *
 * @see http://www.consultant.ru/document/cons_doc_LAW_362322/0060b1f1924347c03afbc57a8d4af63888f81c6c/
 * @see https://classifikators.ru/okei
 */
class MeasureCodeToTag2108Mapper
{
	/**
	 * @var array
	 */
	private static $map = [
		'796' => 0,
		'163' => 10,
		'166' => 11,
		'168' => 12,
		'4' => 20,
		'5' => 21,
		'6' => 22,
		'51' => 30,
		'53' => 31,
		'55' => 32,
		'111' => 40,
		'112' => 41,
		'113' => 42,
		'245' => 50,
		'233' => 51,
		'359' => 70,
		'356' => 71,
		'355' => 72,
		'354' => 73,
		'256' => 80,
		'257' => 81,
		'2553' => 82,
		'2554' => 83,
	];

	private const UNKNOWN_TYPE = 255;

	/**
	 * @param string|null $measureCode
	 * @return int
	 */
	public static function getTag2108Value(?string $measureCode): int
	{
		return self::$map[$measureCode] ?? self::UNKNOWN_TYPE;
	}
}
