<?php

namespace Bitrix\Seo\Analytics\Services\Helpers\Yandex;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\Json;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Seo\Analytics\Services\AccountYandex;
use Bitrix\Seo\Retargeting\AdsHttpClient;

class Sender
{
	private AccountYandex $accountYandex;
	private ?array $profile = null;
	private ?string $currency = null;

	public function __construct(AccountYandex $accountYandex)
	{
		$this->accountYandex = $accountYandex;
	}

	private function getProfile(): Result
	{
		$result = new Result();

		if ($this->profile !== null)
		{
			$result->setData($this->profile);

			return $result;
		}

		$profile = $this->accountYandex->getProfile();
		if ($profile === null || empty($profile['NAME']))
		{
			$result->addError(new Error('Can not find user name for yandex account.'));

			return $result;
		}

		$this->profile = $profile;
		$result->setData($this->profile);

		return $result;
	}

	private function getClient(array $headers = []): Result
	{
		$result = new Result();

		$profile = $this->getProfile();
		if (!$profile->isSuccess())
		{
			return $result->addErrors($profile->getErrors());
		}

		$client = clone $this->accountYandex->getRequest()->getClient();
		$client->setHeader('Authorization', 'Bearer ' . $this->getAuthToken());
		$client->setHeader('Client-Login', $profile->getData()['NAME']);

		foreach ($headers as $name => $value)
		{
			$client->setHeader($name, $value);
		}

		return $result->setData(['client' => $client]);
	}

	private function getAuthToken()
	{
		return $this->accountYandex->getRequest()->getAuthAdapter()->getToken();
	}

	/**
	 * Gets daily expenses report.
	 *
	 * @param Date $dateFrom
	 * @param Date $dateTo
	 * @return ResponseResult<string>
	 */
	public function getDailyExpensesReport(Date $dateFrom, Date $dateTo): ResponseResult
	{
		$reportName = sprintf(
			'AdsReport_%s',
			md5($dateFrom->format('Y-m-d') . $dateTo->format('Y-m-d')),
		);

		$request = [
			'params' => [
				'SelectionCriteria' => [
					'DateFrom' => $dateFrom->format('Y-m-d'),
					'DateTo' => $dateTo->format('Y-m-d'),
				],
				'FieldNames' => [
					'Date',
					'AdId',
					'AdGroupId',
					'AdGroupName',
					'CampaignId',
					'CampaignName',
					'Impressions',
					'Clicks',
					'Cost',
					'Conversions',
					'AvgCpc',
				],
				'ReportName' => $reportName,
				'ReportType' => 'AD_PERFORMANCE_REPORT',
				'DateRangeType' => 'CUSTOM_DATE',
				'Format' => 'TSV',
				'IncludeVAT' => 'YES',
				'IncludeDiscount' => 'NO',
			],
		];

		$headers = [
			'returnMoneyInMicros' => 'false',
			'skipReportHeader' => 'true',
		];

		return $this->getEntityData('reports', $request, $headers);
	}

	/**
	 * Gets ads by campaign ids.
	 *
	 * @param array $campaignIds
	 * @return ResponseResult<array>
	 */
	public function getAds(array $campaignIds): ResponseResult
	{
		$request = [
			'method' => 'get',
			'params' => [
				'SelectionCriteria' => [
					'CampaignIds' => $campaignIds,
				],
				'FieldNames' => [
					'Id',
					'CampaignId',
					'Type',
				],
				'TextAdFieldNames' => [
					'Title',
					'Href',
				],
				'TextImageAdFieldNames' => [
					'Href',
				],
			],
		];

		return $this->getEntityData('ads', $request);
	}

	/**
	 * Gets currency for the account.
	 *
	 * @return string|null
	 */
	public function getCurrency(): ?string
	{
		if ($this->currency !== null)
		{
			return $this->currency;
		}

		// currency is global for an account, so we get it from the first campaign.
		$cacheString = 'analytics_yandex_currency';
		$cachePath = '/seo/analytics/yandex/';
		$cacheTime = 3600;
		$cache = Cache::createInstance();
		$currency = null;
		if ($cache->initCache($cacheTime, $cacheString, $cachePath))
		{
			$currency = $cache->getVars()['currency'];
		}

		if (!empty($currency))
		{
			$this->currency = (string)$currency;

			return $this->currency;
		}

		$cache->clean($cacheString, $cachePath);

		$request = [
			'method' => 'get',
			'params' => [
				'SelectionCriteria' => new \stdClass(),
				'FieldNames' => ['Currency'],
				'Page' => [
					'Limit' => 1,
				],
			],
		];

		$response = $this->getEntityData('campaigns', $request);
		if (!$response->isSuccess())
		{
			return null;
		}

		$response = $response->getResponse();

		if (isset($response['result']['Campaigns']))
		{
			$firstCampaign = current($response['result']['Campaigns']);
			$currency = $firstCampaign['Currency'] ?? null;
		}

		if (!$currency)
		{
			return null;
		}

		if ($cache->startDataCache($cacheTime))
		{
			$cache->endDataCache(['currency' => $currency]);
		}

		$this->currency = (string)$currency;

		return $this->currency;
	}

	/**
	 * Gets entity data.
	 *
	 * @param string $entityName
	 * @param array $request
	 * @param array $headers
	 * @return ResponseResult
	 */
	private function getEntityData(string $entityName, array $request, array $headers = []): ResponseResult
	{
		$result = new ResponseResult();

		$clientResult = $this->getClient($headers);
		if (!$clientResult->isSuccess())
		{
			return $result->addErrors($clientResult->getErrors());
		}

		/** @var AdsHttpClient $client */
		$client = $clientResult->getData()['client'];

		$sendResult = $this->send($client, $this->getYandexServerAddress($entityName), $request);
		if (!$sendResult->isSuccess())
		{
			return $result->addErrors($sendResult->getErrors());
		}

		$response = $sendResult->getResponse();

		if (!$response)
		{
			return $result->addError(new Error("Empty response for {$entityName}"));
		}

		if ($client->getHeaders()->getContentType() === 'application/json')
		{
			try
			{
				$response = Json::decode($response);
			}
			catch (\Exception $exception)
			{
				return $result->addError(new Error($exception->getMessage()));
			}
		}

		if (isset($response['error']) && is_array($response['error']))
		{
			return $result->addError(self::getErrorByResponse($response['error']));
		}

		$result->setResponse($response);

		return $result;
	}

	/**
	 * Sends request to the Yandex server.
	 *
	 * @param AdsHttpClient $client
	 * @param string $url
	 * @param array $request
	 * @return ResponseResult
	 */
	private function send(AdsHttpClient $client, string $url, array $request): ResponseResult
	{
		$result = new ResponseResult();

		while (true)
		{
			$response = $client->post($url, Json::encode($request));

			if ($client->getStatus() === 200)
			{
				$result->setResponse($response);

				break;
			}
			else
			{
				$errorByHttpStatus = self::getErrorByHttpStatus($client->getStatus());
				if ($errorByHttpStatus->getCode() === 201 || $errorByHttpStatus->getCode() === 202)
				{
					$retryIn = (int)($client->getHeaders()->get('retryIn') ?? 10);
					sleep($retryIn);
				}
				else
				{
					$result->addError($errorByHttpStatus);

					break;
				}
			}
		}

		return $result;
	}

	private function getYandexServerAddress(string $source): string
	{
		$url = 'https://api.direct.yandex.com/json/v5/';

		match ($source)
		{
			'campaigns' => $url .= 'campaigns',
			'reports' => $url .= 'reports',
			'ads' => $url .= 'ads',
			default => throw new SystemException("Unknown source {$source}"),
		};

		return $url;
	}

	public static function getErrorByHttpStatus(int $status): Error
	{
		// https://yandex.ru/dev/direct/doc/ru/php5-curl-stat2
		$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_UNKNOWN');

		if ($status === 201)
		{
			$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_201');
		}
		elseif ($status === 202)
		{
			$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_202');
		}
		elseif ($status === 400)
		{
			$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_400');
		}
		elseif ($status === 500)
		{
			$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_500');
		}
		elseif ($status === 502)
		{
			$message = Loc::getMessage('SEO_ANALYTICS_SERVICE_HELPERS_YANDEX_SENDER_HTTP_ERROR_502');
		}

		return new Error($message, $status);
	}

	private static function getErrorByResponse(array $error): Error
	{
		$message = 'Unknown error';
		$code = 0;

		if (isset($error['error_string']))
		{
			$message = $error['error_string'];

			if (isset($error['error_detail']))
			{
				$message .= '; ' . $error['error_detail'];
			}
		}

		if (isset($error['error_code']))
		{
			$code = (int)$error['error_code'];
		}

		return new Error($message, $code);
	}
}
