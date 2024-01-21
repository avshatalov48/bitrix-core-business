<?php

namespace Bitrix\Catalog\Integration\Report\Handler\StoreProfit;

use Bitrix\Catalog\Integration\Report\Filter\StoreSaleFilter;
use Bitrix\Catalog\Integration\Report\StoreStock\StoreStockSale;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Report\VisualConstructor\IReportMultipleGroupedData;

final class GraphHandler extends ProfitHandler implements IReportMultipleGroupedData
{
	protected const COLORS = [
		'SOLD' => '#64b1e2',
		'PROFIT' => '#fda505',
	];
	private const GROUP_MONTH = 'month';
	private const GROUP_DAY = 'day';
	private const GROUP_WEEK_DAY = 'weekday';

	public function getMultipleGroupedData()
	{
		return $this->getCalculatedData();
	}

	public function getMultipleGroupedDemoData()
	{
		return [];
	}

	public function prepare()
	{
		return $this->getGraphsData();
	}

	private function getDateGrouping(): string
	{
		$filter = $this->getFilterParameters();

		$periodDefinition = $filter[StoreSaleFilter::REPORT_INTERVAL_FIELD_NAME]['datasel'] ?? DateType::CURRENT_MONTH;

		switch ($periodDefinition)
		{
			case DateType::YEAR:
			case DateType::QUARTER:
			case DateType::CURRENT_QUARTER:
				return self::GROUP_MONTH;
			case DateType::LAST_WEEK:
			case DateType::CURRENT_WEEK:
			case DateType::NEXT_WEEK:
				return self::GROUP_WEEK_DAY;
		}

		return self::GROUP_DAY;
	}

	private function getGraphsData(): array
	{
		$filterParams = $this->getFormattedFilter();

		$basketItems = StoreStockSale::getProductsSoldPricesForDeductedPeriod($filterParams);

		if (empty($basketItems))
		{
			$basketItems = $this->getEmptyBasketItemsData();
		}

		$combinedData = [];
		$dateGrouping = $this->getDateGrouping();
		foreach ($basketItems as $item)
		{
			$item['BASKET_QUANTITY'] *= -1;
			$dateDeducted = $item['DATE_DEDUCTED'];
			if (!($dateDeducted instanceof DateTime))
			{
				continue;
			}
			$dateDeducted->setTime(0,0);
			if ($dateGrouping === self::GROUP_MONTH)
			{
				$year = $dateDeducted->format('Y');
				$month = $dateDeducted->format('m');
				$dateDeducted->setDate($year, $month, 1);
			}

			$priceFields = [
				'TOTAL_SOLD' => $item['BASKET_PRICE'] * $item['BASKET_QUANTITY'],
				'COST_PRICE' => $item['COST_PRICE'] * $item['BASKET_QUANTITY'],
			];
			if ($item['CURRENCY'])
			{
				$priceFields = $this->preparePriceFields($priceFields, $item['CURRENCY']);
			}

			$combinedData[$dateDeducted->toString()] ??= [
				'TOTAL_SOLD' => 0.0,
				'COST_PRICE' => 0.0,
				'PROFIT' => 0.0,
				'PROFITABILITY' => null,
				'DATE_DEDUCTED' => $dateDeducted,
			];

			$combinedData[$dateDeducted->toString()]['COST_PRICE'] += $priceFields['COST_PRICE'];
			$combinedData[$dateDeducted->toString()]['TOTAL_SOLD'] += $priceFields['TOTAL_SOLD'];
		}

		$totalProfit = 0;
		$totalSold = 0;
		foreach ($combinedData as $dateKey => $data)
		{
			$profit = $data['TOTAL_SOLD'] - $data['COST_PRICE'];
			$combinedData[$dateKey]['PROFIT'] = $profit;
			if ($data['COST_PRICE'] > 0)
			{
				$combinedData[$dateKey]['PROFITABILITY'] = $this->calculateProfitability($data['COST_PRICE'], $profit);
			}
			$totalProfit += $profit;
			$totalSold += $data['TOTAL_SOLD'];
		}

		$labels = [];
		$soldGraphItems = [];
		$profitGraphItems = [];

		foreach ($combinedData as $date => $value)
		{
			$groupByValue = $value['DATE_DEDUCTED']->getTimestamp();
			$label = $this->formatDateForLabel($value['DATE_DEDUCTED']);
			$item = [
				"groupBy" => $groupByValue,
				"label" => $label,
				"balloon" => [
					'title' => $label,
					'items' => [
						[
							'title' => Loc::getMessage('GRAPH_HANDLER_BALLOON_SUBTITLE_SOLD'),
							'htmlValue' =>  $this->formatAmountByCurrency((float)$value['TOTAL_SOLD']),
						],
						[
							'title' => Loc::getMessage('GRAPH_HANDLER_BALLOON_SUBTITLE_PROFIT'),
							'htmlValue' => $this->formatAmountByCurrency((float)$value['PROFIT']),
						],
						[
							'title' => Loc::getMessage('GRAPH_HANDLER_BALLOON_SUBTITLE_PROFITABILITY'),
							'value' => $value['PROFITABILITY'] !== null ? "{$value['PROFITABILITY']}%" : '-',
						],
					]
				],
			];

			$soldGraphItems[] = $item + ['value' => (float)$value['TOTAL_SOLD']];
			$profitGraphItems[] =  $item + ['value' => (float)$value['PROFIT']];
			$labels[$groupByValue] = $label;
		}

		return [
			[
				"items" => $soldGraphItems,
				"config" => $this->getConfigByCode('SOLD', $labels, $totalSold),
			],
			[
				"items" => $profitGraphItems,
				"config" => $this->getConfigByCode('PROFIT', $labels, $totalProfit)
			],
		];
	}
	private function getEmptyBasketItemsData(): array
	{
		return [
			[
				'BASKET_PRICE' => 0,
				'COST_PRICE' => 0,
				'DATE_DEDUCTED' => new DateTime(),
				'BASKET_QUANTITY' => 0,
			]
		];
	}
	private function getConfigByCode(string $code, array $labels, float $total): array
	{
		return [
			"groupsLabelMap" => $labels,
			"reportTitle" => Loc::getMessage('GRAPH_HANDLER_BALLOON_SUBTITLE_' . $code),
			"reportColor" => self::COLORS[$code],
			"amount" => $this->formatAmountByCurrency($total),
			"dateFormatForLabel" => $this->getDateFormatForLabel(),
			"dateGrouping" => $this->getDateGrouping()
		];
	}

	private function formatAmountByCurrency(float $amount): string
	{
		$totalAmountFormatted = \CCurrencyLang::CurrencyFormat($amount, CurrencyManager::getBaseCurrency());

		return str_replace("&nbsp;", " ", $totalAmountFormatted);
	}

	private function formatDateForLabel(Date $date)
	{
		return FormatDate($this->getDateFormatForLabel(), $date);
	}

	private function getDateFormatForLabel(): string
	{
		switch ($this->getDateGrouping())
		{
			case self::GROUP_DAY:
				return Context::getCurrent()->getCulture()->getDayMonthFormat();

			case self::GROUP_WEEK_DAY:
				return "l";

			case self::GROUP_MONTH:
				return "f";
		}

		return Context::getCurrent()->getCulture()->getLongDateFormat();
	}
}
