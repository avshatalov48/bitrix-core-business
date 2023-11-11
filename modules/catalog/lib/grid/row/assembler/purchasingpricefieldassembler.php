<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use CCurrencyLang;

final class PurchasingPriceFieldAssembler extends FieldAssembler
{
	private const PRICE_COLUMN = 'PURCHASING_PRICE';
	private const CURRENCY_COLUMN = 'PURCHASING_CURRENCY';

	public function __construct()
	{
		parent::__construct([
			self::PRICE_COLUMN,
		]);
	}

	protected function prepareRow(array $row): array
	{
		$priceValue = $row['data'][self::PRICE_COLUMN] ?? null;
		$currencyValue = $row['data'][self::CURRENCY_COLUMN] ?? null;

		if (Loader::includeModule('currency'))
		{
			$row['columns'][self::PRICE_COLUMN] = CCurrencyLang::CurrencyFormat(
				$priceValue,
				$currencyValue
			);
		}
		else
		{
			$row['columns'][self::PRICE_COLUMN] = $priceValue;
		}

		$row['data']['~' . self::PRICE_COLUMN] = [
			'PRICE' => [
				'NAME' => 'PRICE',
				'VALUE' => $priceValue,
			],
			'CURRENCY' => [
				'NAME' => 'CURRENCY',
				'VALUE' => $currencyValue,
			],
		];

		return $row;
	}
}
