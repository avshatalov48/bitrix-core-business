<?php

namespace Bitrix\Seo\Analytics\Services\Helpers;

use Bitrix\Main\Type\Date;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Seo\Analytics\Internals\ExpensesCollection;

final class ExpensesAdapter
{
	public static function translateExpensesReportToDailyExpenses(array $data): ExpensesCollection
	{
		$campaigns = $data['CAMPAIGNS'] ?? [];
		if (is_array($campaigns))
		{
			$campaigns = array_column($campaigns, 'NAME', 'ID');
		}
		$rows = $data['ROWS'] ?? [];
		$currency = $data['CURRENCY'] ?? '';

		$resultCollection = new ExpensesCollection();
		foreach ($rows as $row)
		{
			$clicks = (int)($row['CLICKS'] ?? 0);
			$cost = (float)($row['COST'] ?? 0);
			$impressions = (int)($row['IMPRESSIONS'] ?? 0);

			$costPerMill =
				$impressions > 0
					? round(($cost / $impressions) * 1000, 2)
					: 0
			;

			$costPerClick =
				$clicks > 0
					? round($cost / $clicks, 2)
					: 0
			;

			$date = !empty($row['DATE']) ? new Date($row['DATE'], 'Y-m-d') : null;

			$formattedRow = [
				'impressions' => $row['IMPRESSIONS'],
				'campaignName' => $campaigns[$row['CID']] ?? '',
				'campaignId' => $row['CID'],
				'clicks' => $clicks,
				'actions' => $clicks,
				'spend' => $cost,
				'cpc' => $costPerClick,
				'date' => $date,
				'cpm' => $costPerMill,
				'currency' => $currency,
			];

			$resultCollection->addItem(new Expenses($formattedRow));
		}

		return $resultCollection;
	}
}
