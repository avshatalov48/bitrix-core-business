<?php

namespace Bitrix\Catalog\Integration\Report\Handler\Chart;

use Bitrix\Catalog\Integration\Report\Handler\Chart\StoreInfoCombiner\StoreInfoCombiner;
use Bitrix\Catalog\Integration\Report\StoreStock\Entity\Store\StoreInfo;

use function Symfony\Component\String\s;

final class ChartStoreInfo
{
	private array $seriesList = [];
	private array $columns;

	private StoreInfoCombiner $combiner;

	public function __construct(StoreInfoCombiner $combiner)
	{
		$this->combiner = $combiner;
	}

	/**
	 * Add new series that contains stores <b>$stores</b> for future calculate
	 * @param string $seriesId
	 * @param StoreInfo ...$stores
	 * @return void
	 */
	public function accumulate(string $seriesId, StoreInfo ...$stores): void
	{
		if (!isset($this->seriesList[$seriesId]))
		{
			$this->seriesList[$seriesId] = $stores;
		}
		else
		{
			$this->seriesList[$seriesId] = $this->summarizeStores(...$this->seriesList[$seriesId], ...$stores);
		}
	}

	private function summarizeStores(StoreInfo ...$stores): array
	{
		$outputStores = [];

		/** @var StoreInfo $store */
		foreach ($stores as $store)
		{
			if (isset($outputStores[$store->getStoreId()]))
			{
				$outputStores[$store->getStoreId()] = $this->combiner->summarizeStores($outputStores[$store->getStoreId()], $store);
			}
			else
			{
				$outputStores[$store->getStoreId()] = $store;
			}
		}

		return array_values($outputStores);
	}

	/**
	 * Return combined calculation of all series sum for each store in one column
	 * @param int $maxLabelLength
	 * @return array
	 */
	public function getCombinedCalculatedColumn(int $maxLabelLength): array
	{
		$combinedTitle = '';

		$combinedColumn = array_reduce($this->getCalculatedColumns(), static function ($carry, $item) use (&$combinedTitle, $maxLabelLength) {
			foreach ($item as $series => $value)
			{
				if ($series !== 'TITLE')
				{
					$carry[$series] += $value;
				}
			}

			if ($combinedTitle === '' || (mb_strlen($combinedTitle) < $maxLabelLength + 2))
			{
				$combinedTitle .= ', ';
				$combinedTitle .= $item['TITLE'];
			}

			return $carry;
		}, array_fill_keys(array_keys($this->seriesList), 0.0));

		$combinedTitle = mb_substr($combinedTitle, 2);
		$combinedColumn['TITLE'] = $combinedTitle;

		return $combinedColumn;
	}

	/**
	 * Return calculated series sum for each store
	 * @return array
	 */
	public function getCalculatedColumns(): array
	{
		if (isset($this->columns))
		{
			return $this->columns;
		}

		$columns = [];

		foreach ($this->seriesList as $seriesId => $series)
		{
			/** @var StoreInfo $store */
			foreach ($series as $store)
			{
				$storeId = $store->getStoreId();
				if (!isset($columns[$storeId]))
				{
					$columns[$storeId] = array_fill_keys(array_keys($this->seriesList), 0.0);
					$columns[$storeId]['TITLE'] = $store->getStoreName();
				}

				$columns[$storeId][$seriesId] += $store->getCalculatedSumPrice();
			}
		}

		$this->columns = $columns;
		return $this->columns;
	}

	/**
	 * Return count of involved in calculation stores
	 * @return int
	 */
	public function getStoresCount(): int
	{
		return count($this->getCalculatedColumns());
	}
}
