<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use CCurrencyLang;

class PriceFieldAssembler extends FieldAssembler
{
	private bool $isCurrencyIncluded;

	public function __construct(array $columnIds)
	{
		parent::__construct($columnIds);

		$this->isCurrencyIncluded = Loader::includeModule('currency');
	}

	/**
	 * Currency column id.
	 *
	 * Works for `PriceProvider` columns.
	 * If you need another columns - override this method.
	 *
	 * @see \Bitrix\Catalog\Grid\Column\PriceProvider
	 *
	 * @param string $priceColumnId
	 *
	 * @return string|null
	 */
	protected function getCurrencyColumnId(string $priceColumnId): ?string
	{
		$priceTypeId = PriceProvider::parsePriceTypeId($priceColumnId);
		if (isset($priceTypeId))
		{
			return PriceProvider::getCurrencyPriceTypeId($priceTypeId);
		}

		return null;
	}

	final protected function prepareRow(array $row): array
	{
		if (empty($this->getColumnIds()))
		{
			return $row;
		}

		foreach ($this->getColumnIds() as $priceColumnId)
		{
			$currencyColumnId = $this->getCurrencyColumnId($priceColumnId);
			if (isset($currencyColumnId))
			{
				$priceValue = $row['data'][$priceColumnId] ?? null;
				$currencyValue = $row['data'][$currencyColumnId] ?? null;

				if ($this->isCurrencyIncluded)
				{
					$row['columns'][$priceColumnId] = CCurrencyLang::CurrencyFormat(
						$priceValue,
						$currencyValue
					);
				}
				else
				{
					$row['columns'][$priceColumnId] = $priceValue;
				}

				$row['data'][$priceColumnId] = [
					'PRICE' => [
						'NAME' => 'PRICE',
						'VALUE' => $priceValue,
					],
					'CURRENCY' => [
						'NAME' => 'CURRENCY',
						'VALUE' => $currencyValue,
					],
				];
			}
		}

		return $row;
	}
}
