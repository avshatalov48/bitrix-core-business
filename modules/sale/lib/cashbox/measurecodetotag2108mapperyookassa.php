<?php

namespace Bitrix\Sale\Cashbox;

/**
 * Class MeasureCodeToTag2108Mapper
 *
 * @package Bitrix\Sale\Cashbox
 *
 * @see http://www.consultant.ru/document/cons_doc_LAW_362322/0060b1f1924347c03afbc57a8d4af63888f81c6c/
 * @see https://classifikators.ru/okei
 *
 * @see https://yookassa.ru/developers/payment-acceptance/scenario-extensions/receipts/54fz/parameters-values#measure
 */
class MeasureCodeToTag2108MapperYooKassa extends MeasureCodeToTag2108Mapper
{
	/**
	 * @var array
	 */
	protected static array $map = [
		'796' => 'piece',
		'163' => 'gram',
		'166' => 'kilogram',
		'168' => 'ton',
		'4' => 'centimeter',
		'5' => 'decimeter',
		'6' => 'meter',
		'51' => 'square_centimeter',
		'53' => 'square_decimeter',
		'55' => 'square_meter',
		'111' => 'milliliter',
		'112' => 'liter',
		'113' => 'cubic_meter',
		'245' => 'kilowatt_hour',
		'233' => 'gigacalorie',
		'359' => 'day',
		'356' => 'hour',
		'355' => 'minute',
		'354' => 'second',
		'256' => 'kilobyte',
		'257' => 'megabyte',
		'2553' => 'gigabyte',
		'2554' => 'terabyte',
	];

	protected const UNKNOWN_TYPE = 'other';
}