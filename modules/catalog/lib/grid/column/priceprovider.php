<?php

namespace Bitrix\Catalog\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Catalog;
use Bitrix\Catalog\Access;

class PriceProvider extends CatalogProvider
{
	private const PREFIX_PRICE_COLUMN_ID = 'PRICE_';
	private const PREFIX_CURRENCY_COLUMN_ID= 'CURRENCY_';

	public function prepareColumns(): array
	{
		$result = [];

		$editable = false;
		foreach (Catalog\GroupTable::getTypeList() as $priceType)
		{
			$priceTypeId = (int)$priceType['ID'];
			$columnId = static::getPriceTypeColumnId($priceTypeId);

			$result[$columnId] = [
				'type' => Grid\Column\Type::MONEY,
				'name' => $priceType['NAME_LANG'] ?? $priceType['NAME'],
				'necessary' => false,
				'editable' => $editable ?: new Grid\Column\Editable\MoneyConfig($columnId),
				'multiple' => false,
				'sort' => 'SCALED_PRICE_' . $priceTypeId,
				'align' => 'right',
				'select' => [
					self::getPriceTypeColumnId($priceTypeId),
					self::getCurrencyPriceTypeId($priceTypeId),
				],
			];
		}

		return $this->createColumns($result);
	}

	public static function parsePriceTypeId(string $columnId): ?int
	{
		$prefix = preg_quote(self::PREFIX_PRICE_COLUMN_ID);
		$re = "/^{$prefix}(\d+)$/";

		if (preg_match($re, $columnId, $m))
		{
			return (int)$m[1];
		}

		return null;
	}

	public static function getPriceTypeColumnId(int $priceTypeId): string
	{
		return self::PREFIX_PRICE_COLUMN_ID . $priceTypeId;
	}

	public static function getCurrencyPriceTypeId(int $priceTypeId)
	{
		return self::PREFIX_CURRENCY_COLUMN_ID . $priceTypeId;
	}

	protected function allowPriceEdit(): bool
	{
		return
			$this->allowProductEdit()
			&& $this->accessController->check(Access\ActionDictionary::ACTION_PRICE_EDIT)
		;
	}
}
