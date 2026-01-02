<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex;

use Bitrix\Main;
use Bitrix\Seo;

final class ReportBuilder
{
	private Dto\ReportCollection $reportCollection;
	private Dto\AdCollection $adCollection;

	private Sender $sender;

	private Main\Type\Date $dateTo;
	private Main\Type\Date $dateFrom;
	private ?string $currency = null;

	public function __construct(Sender $sender)
	{
		$this->sender = $sender;

		$this->initDefaultPeriod();
	}

	private function getSender(): Sender
	{
		return $this->sender;
	}

	/**
	 * Inits default period
	 *
	 * @return void
	 */
	private function initDefaultPeriod(): void
	{
		$this->dateTo = new Main\Type\Date();

		$this->dateFrom = new Main\Type\Date();
		$this->dateFrom->add('-1 week');
	}

	public function setPeriod(?Main\Type\Date $dateFrom, ?Main\Type\Date $dateTo): self
	{
		if ($dateFrom)
		{
			$this->dateFrom = $dateFrom;
		}

		if ($dateTo)
		{
			$this->dateTo = $dateTo;
		}

		return $this;
	}

	public function buildDailyExpensesReport(): Main\Result
	{
		$result = new Main\Result();

		$this->reportCollection = new Dto\ReportCollection();
		$this->adCollection = new Dto\AdCollection();

		$this->currency = $this->getSender()->getCurrency();

		$reportResult = $this->loadReports();
		if (!$reportResult->isSuccess())
		{
			return $result->addErrors($reportResult->getErrors());
		}

		$campaignIds = $this->reportCollection->getUniqCampaignIds();

		$adResult = $this->loadAds($campaignIds);
		if (!$adResult->isSuccess())
		{
			$result->addErrors($adResult->getErrors());

			return $result;
		}

		$expensesCollection = new Seo\Analytics\Internals\ExpensesCollection();
		/** @var Dto\Report $report */
		foreach ($this->reportCollection as $report)
		{
			$expensesCollection->addItem(new Seo\Analytics\Internals\Expenses($this->formatExpensesData($report)));
		}

		$result->setData(['expenses' => $expensesCollection]);

		return $result;
	}

	private function loadReports(): Main\Result
	{
		$result = new Main\Result();

		/** @var ResponseResult<string> $reportResult */
		$reportResult = $this->getSender()->getDailyExpensesReport($this->dateFrom, $this->dateTo);
		if ($reportResult->isSuccess())
		{
			foreach ($this->parseReportData((string)$reportResult->getResponse()) as $report)
			{
				$this->reportCollection->addItem(Dto\Report::makeFromArray($report));
			}
		}
		else
		{
			$result->addErrors($reportResult->getErrors());
		}

		return $result;
	}

	private function loadAds(array $campaignIds): Main\Result
	{
		$result = new Main\Result();

		$campaignIdsForRequest = [];

		$cache = Main\Data\Cache::createInstance();
		$cacheDir = '/biconnector/integration/crm/dailyexpenses/yandex/ads';
		$cacheTtl = 86400; // 60 * 60 * 24

		foreach ($campaignIds as $campaignId)
		{
			$cacheName = (string)$campaignId;

			if ($cache->initCache($cacheTtl, $cacheName, $cacheDir . '/' . $cacheName))
			{
				$cacheDataAds = $cache->getVars();
				foreach ($cacheDataAds as $cacheDataAd)
				{
					$this->adCollection->set((int)$cacheDataAd['Id'], Dto\Ad::makeFromArray($cacheDataAd));
				}
			}
			else
			{
				$campaignIdsForRequest[] = $campaignId;
			}
		}

		$adsForCache = [];

		foreach (array_chunk($campaignIdsForRequest, 10) as $chunkCampaignIds)
		{
			$adResult = $this->getSender()->getAds($chunkCampaignIds);
			if ($adResult->isSuccess())
			{
				$response = $adResult->getResponse();
				if (isset($response['result']['Ads']))
				{
					foreach ($response['result']['Ads'] as $ad)
					{
						$this->adCollection->set((int)$ad['Id'], Dto\Ad::makeFromArray($ad));
						$adsForCache[(int)$ad['CampaignId']][(int)$ad['Id']] = $ad;
					}
				}
			}
			else
			{
				$result->addErrors($adResult->getErrors());

				return $result;
			}
		}

		foreach ($campaignIdsForRequest as $campaignId)
		{
			$cacheName = (string)$campaignId;

			$cache->initCache($cacheTtl, $cacheName, $cacheDir . '/' . $cacheName);
			$cache->startDataCache();
			$cache->endDataCache($adsForCache[$campaignId] ?? []);
		}

		return $result;
	}

	private function parseReportData(string $data): \Generator
	{
		if (empty($data))
		{
			return;
		}

		$titles = [];
		$strings = explode("\n", $data);
		foreach ($strings as $number => $string)
		{
			if ($number === 0)
			{
				$titles = explode("\t", $string);
			}
			elseif (!empty($string) && !str_starts_with($string, 'Total'))
			{
				yield array_combine($titles, explode("\t", $string));
			}
		}
	}

	private function formatExpensesData(Dto\Report $report): array
	{
		/** @var Dto\Ad|null $ad */
		$ad = $this->adCollection->get($report->adId);
		if ($ad?->href)
		{
			DynamicParamFiller::fill($report, $ad->href);
		}

		$cpm = 0;
		if ($report->impressions > 0)
		{
			$cpm = round(($report->cost / $report->impressions) * 1000, 2);
		}

		return [
			'impressions' => $report->impressions,
			'campaignName' => $report->campaignName,
			'adId' => $report->adId,
			'adName' => $ad?->title,
			'groupId' => $report->adGroupId,
			'groupName' => $report->adGroupName,
			'campaignId' => $report->campaignId,
			'clicks' => $report->clicks,
			'actions' => $report->conversions + $report->clicks,
			'spend' => $report->cost,
			'cpc' => $report->avgCpc,
			'date' => $report->date,
			'cpm' => $cpm,
			'currency' => $this->currency,
			'utmSource' => $report->utmSource,
			'utmMedium' => $report->utmMedium,
			'utmCampaign' => $report->utmCampaign,
			'utmContent' => $report->utmContent,
		];
	}
}
