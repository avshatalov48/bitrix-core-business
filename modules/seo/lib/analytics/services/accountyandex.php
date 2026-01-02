<?php

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting\Services\ResponseYandex;
use Bitrix\Seo\Retargeting\IRequestDirectly;
use Bitrix\Seo\Analytics\Account;

class AccountYandex extends Account implements IRequestDirectly
{
	const TYPE_CODE = 'yandex';

	protected ?string $currency = null;

	/**
	 * Get list.
	 *
	 * @return Response
	 */
	public function getList()
	{
		// fake

		$response = Response::create(static::TYPE_CODE);
		$response->setData(array(array('ID' => 1)));

		return $response;
	}

	/**
	 * Return true if it has accounts.
	 *
	 * @return bool
	 */
	public function hasAccounts()
	{
		return false;
	}

	/**
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getProfile()
	{
		// default_avatar_id
		// 'https://avatars.yandex.net/get-yapic//islands-50/';

		$response = $this->getRequest()->getClient()->get(
			'https://login.yandex.ru/info?format=json&oauth_token=' .
			$this->getAuthToken()
		);

		if ($response)
		{
			try
			{
				$response = Json::decode($response);
			}
			catch (\Exception $exception)
			{
				return null;
			}

			if (is_array($response))
			{
				return array(
					'ID' => $response['id'],
					'NAME' => $response['login'],
					'LINK' => '',
					'PICTURE' => 'https://avatars.mds.yandex.net/get-yapic/0/0-0/islands-50',
				);
			}
		}


		return null;
	}

	/**
	 * @param $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 * @return Response
	 */
	public function getExpenses($accountId = null, Date $dateFrom = null, Date $dateTo = null)
	{
		// https://tech.yandex.ru/direct/doc/reports/example-docpage/
		$result = new ResponseYandex();
		$expenses = new Expenses();

		// preload currency cause we can lost it if request after report
		$this->getCurrency();
		$result->setData(['expenses' => $expenses]);

		$dateFrom = $dateFrom ?: new Date();
		$dateTo = $dateTo ?: new Date();

		$options = [
			'params' => [
				'SelectionCriteria' => [
					'DateFrom' => $dateFrom->format('Y-m-d'),
					'DateTo' => $dateTo->format('Y-m-d'),
				],
				'FieldNames' => [
					'Impressions', 'Clicks', 'Conversions', 'Cost',
					'AvgCpc',
					//'AvgCpm'
				],
				'ReportType' => 'ACCOUNT_PERFORMANCE_REPORT',
				'DateRangeType' => 'CUSTOM_DATE',
				'ReportName' => 'Account Report',
				'Format' => 'TSV',
				'IncludeVAT' => 'YES',
				'IncludeDiscount' => 'YES',
			],
		];

		$profile = $this->getProfile();
		if (empty($profile['NAME']))
		{
			return $result->addError(new Error("Can not find user name."));
		}

		$client = $this->getClient();
		$client->setHeader('Client-Login', $profile['NAME']);
		$client->setHeader('returnMoneyInMicros', 'false');
		$client->setHeader('skipReportHeader', 'true');
		//$client->setHeader('processingMode', 'online');
		$response = $client->post(
			$this->getYandexServerAdress() . 'reports',
			Json::encode($options)
		);

		if ($client->getStatus() != 200)
		{
			return $result->addError(Helpers\Yandex\Sender::getErrorByHttpStatus($client->getStatus()));
		}
		if ($response)
		{
			$expenses->add($this->parseReportData($response));
		}
		else
		{
			return $result->addError(new Error('Empty report data'));
		}

		return $result;
	}

	/**
	 * Return true if it has daily expenses report
	 *
	 * @return bool
	 */
	public function hasDailyExpensesReport(): bool
	{
		return true;
	}

	/**
	 * @param string|null $accountId
	 * @param Date|null $dateFrom
	 * @param Date|null $dateTo
	 *
	 * @return Result
	 */
	public function getDailyExpensesReport(?string $accountId, ?Date $dateFrom, ?Date $dateTo): Result
	{
		$result = new Result();

		$reportBuilder = new Helpers\Yandex\ReportBuilder(new Helpers\Yandex\Sender($this));
		$reportBuilder->setPeriod($dateFrom, $dateTo);

		$buildResult = $reportBuilder->buildDailyExpensesReport();
		if (!$buildResult->isSuccess())
		{
			$errorsMsg = [];
			foreach ($buildResult->getErrors() as $key => $value)
			{
				if (is_string($key))
				{
					$errorsMsg[] = "[{$key}] {$value}";
				}
				else
				{
					$errorsMsg[] = $value;
				}
			}

			$errorsMessage = implode(',', $errorsMsg);
			$errorMessage = $this->buildErrorMessage("Error occurred while load daily expenses: {$errorsMessage}");

			return $result->addError(new Error($errorMessage));
		}

		$result->setData(['expenses' => $buildResult->getData()['expenses']]);

		return $result;
	}

	/**
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Response
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		return Response::create('yandex');
	}

	/**
	 * @return string|null
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function getCurrency(): ?string
	{
		if ($this->currency)
		{
			return $this->currency;
		}

		$sender = new Helpers\Yandex\Sender($this);
		$this->currency = $sender->getCurrency();

		return $this->currency;
	}

	/**
	 * @param string $data
	 * @return array
	 */
	protected function parseReportData($data)
	{
		if (!is_string($data) || empty($data))
		{
			return [];
		}

		$titles = [];
		$strings = explode("\n", $data);
		foreach ($strings as $number => $string)
		{
			if ($number === 0)
			{
				$titles = explode("\t", $string);
			}
			elseif (!empty($string) && mb_strpos($string, 'Total') !== 0)
			{
				$row = array_combine($titles, explode("\t", $string));
			}
		}

		if (empty($row))
		{
			return [];
		}

		return $this->formatReportData($row);
	}

	private function formatReportData(array $row): array
	{
		$conversions =
			(is_numeric($row['Conversions']) && $row['Conversions'])
				? $row['Conversions']
				: 0
		;

		$clicks =
			(is_numeric($row['Clicks']) && $row['Clicks'])
				? $row['Clicks']
				: 0
		;

		$impressions =
			(is_numeric($row['Impressions']) && $row['Impressions'])
				? $row['Impressions']
				: 0
		;

		$cpm = 0;
		if ($impressions > 0)
		{
			$cpm = round(($row['Cost'] / $impressions) * 1000, 2);
		}

		$date = !empty($row['Date']) ? new Date($row['Date'], 'Y-m-d') : null;

		return [
			'impressions' => $impressions,
			'campaignName' => $row['CampaignName'],
			'campaignId' => $row['CampaignId'],
			'clicks' => $clicks,
			'actions' => $conversions + $clicks,
			'spend' => $row['Cost'],
			'cpc' => $row['AvgCpc'],
			'date' => $date,
			'cpm' => $cpm,
			'currency' => $this->getCurrency(),
		];
	}

	/**
	 * @return string
	 */
	protected function getYandexServerAdress()
	{
		$isSandbox = false;

		return 'https://api' . ($isSandbox ? '-sandbox' : '') . '.direct.yandex.com/json/v5/';
	}

	/**
	 * @return string
	 */
	protected function getAuthToken()
	{
		$token = $this->getRequest()->getAuthAdapter()->getToken();

		return $token;
	}

	/**
	 * @return \Bitrix\Seo\Retargeting\AdsHttpClient
	 */
	protected function getClient()
	{
		$client = clone $this->getRequest()->getClient();
		$client->setHeader('Authorization', 'Bearer ' . $this->getAuthToken());

		return $client;
	}
}