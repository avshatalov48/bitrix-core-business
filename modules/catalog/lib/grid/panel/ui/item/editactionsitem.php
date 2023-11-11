<?php

namespace Bitrix\Catalog\Grid\Panel\UI\Item;

use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\v2\IoC\ServiceContainer;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

Loader::requireModule('iblock');

class EditActionsItem extends \Bitrix\Iblock\Grid\Panel\UI\Actions\Item\EditActionsItem
{
	protected function saveElement(int $id, array $fields): Result
	{
		$result = new Result();

		[$elementFields, $productFields, $priceFields] = $this->splitProductFields($fields);

		if (!empty($elementFields))
		{
			$result = parent::saveElement($id, $elementFields);
		}

		if ($result->isSuccess())
		{
			/**
			 * @var BaseProduct $product
			 */
			$product = ServiceContainer::getProductRepository($this->getIblockId())->getEntityById($id);
			if ($product)
			{
				$product->setFields($productFields);

				$sku = $product->getSkuCollection()->getFirst();
				if ($sku)
				{
					$sku->getPriceCollection()->setValues($priceFields);
				}

				$result = $product->save();
			}
			else
			{
				/**
				 * @var BaseSku $sku
				 */
				$sku = ServiceContainer::getSkuRepository($this->getIblockId())->getEntityById($id);
				if ($sku)
				{
					$sku->setFields($productFields);
					$sku->getPriceCollection()->setValues($priceFields);

					$result = $sku->save();
				}
			}
		}

		return $result;
	}

	/**
	 * @param array $fields
	 *
	 * @return array[] in format `[$elementFields, $productFields, $priceFields]`
	 */
	private function splitProductFields(array $fields): array
	{
		$elementFields = [];
		$productFields = [];
		$priceFields = [];

		$priceColumns = $this->getPriceColumns();
		$productColumns = $this->getProductColumns();

		foreach ($fields as $name => $value)
		{
			if (isset($productColumns[$name]))
			{
				if ($name === 'PURCHASING_PRICE')
				{
					if (is_array($value))
					{
						if (isset($value['PRICE']['VALUE']))
						{
							$productFields['PURCHASING_PRICE'] =
								$value['PRICE']['VALUE'] === ''
									? null
									: $value['PRICE']['VALUE']
							;
							$productFields['PURCHASING_CURRENCY'] = $value['CURRENCY']['VALUE'] ?? null;
						}
					}
					elseif ($value !== '')
					{
						$productFields[$name] = (float)$value;
					}
				}
				else
				{
					$productFields[$name] = $value;
				}
			}
			elseif (isset($priceColumns[$name]))
			{
				$priceTypeId = PriceProvider::parsePriceTypeId($name);
				if (isset($priceTypeId))
				{
					$priceFields[$priceTypeId] = [
						'PRICE' => $value['PRICE']['VALUE'] ?? null,
						'CURRENCY' => $value['CURRENCY']['VALUE'] ?? null,
					];
				}
			}
			else
			{
				$elementFields[$name] = $value;
			}
		}

		return [$elementFields, $productFields, $priceFields];
	}

	/**
	 * @return bool[] key is string (column id)
	 */
	private function getPriceColumns(): array
	{
		$result = [];

		$availableColumnsIds = [];
		foreach (GroupTable::getTypeList() as $type)
		{
			$id = PriceProvider::getPriceTypeColumnId($type['ID']);
			$availableColumnsIds[$id] = true;
		}

		foreach ($this->getColumns() as $column)
		{
			$id = $column->getId();
			if (isset($availableColumnsIds[$id]))
			{
				$result[$id] = true;
			}
		}

		return $result;
	}

	/**
	 * @return bool[] key is string (column id)
	 */
	private function getProductColumns(): array
	{
		$result = [];

		$availableColumnsIds = array_fill_keys([
			'VAT_ID',
			'VAT_INCLUDED',
			'MEASURE_RATIO',
			'MEASURE',
			'PURCHASING_PRICE',
			'PURCHASING_CURRENCY',
			'QUANTITY_TRACE',
			'QUANTITY',
			'QUANTITY_RESERVED',
			'WIDTH',
			'LENGTH',
			'WEIGHT',
			'HEIGHT',
			'CAN_BUY_ZERO',
			'BARCODE',
		], true);

		foreach ($this->getColumns() as $column)
		{
			$id = $column->getId();
			if (isset($availableColumnsIds[$id]))
			{
				$result[$id] = true;
			}
		}

		return $result;
	}
}
