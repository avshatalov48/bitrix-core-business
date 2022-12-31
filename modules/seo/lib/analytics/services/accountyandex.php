<?

namespace Bitrix\Seo\Analytics\Services;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Seo\Analytics\Internals\Expenses;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Type\Date;
use Bitrix\Seo\Retargeting\Response;
use Bitrix\Seo\Retargeting\Services\ResponseYandex;
use Bitrix\Seo\Retargeting\IRequestDirectly;

class AccountYandex extends \Bitrix\Seo\Analytics\Account implements IRequestDirectly
{
	const TYPE_CODE = 'yandex';
	const ERROR_CODE_REPORT_OFFLINE = 100201;

	protected $currency;

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
		if(empty($profile['NAME']))
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

		if($client->getStatus() != 200)
		{
			return $result->addError($this->getReportErrorByHttpStatus($client->getStatus()));
		}
		if($response)
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
	 * @param $accountId
	 * @param array $params
	 * @param array $publicPageIds
	 * @return Response
	 */
	public function updateAnalyticParams($accountId, array $params, array $publicPageIds = [])
	{
		return Response::create('yandex');
	}

	protected function getCurrency()
	{
		if($this->currency)
		{
			return $this->currency;
		}
		// currency is global for an account, so we get it from the first campaign.
		$cacheString = 'analytics_yandex_currency';
		$cachePath = '/seo/analytics/yandex/';
		$cacheTime = 3600;
		$cache = Cache::createInstance();
		$currency = null;
		if($cache->initCache($cacheTime, $cacheString, $cachePath))
		{
			$currency = $cache->getVars()['currency'];
		}
		if(!$currency)
		{
			$cache->clean($cacheString, $cachePath);
			$cache->startDataCache($cacheTime);

			$response = $this->getClient()->post(
				$this->getYandexServerAdress() . 'campaigns',
				Json::encode([
					'method' => 'get',
					'params' => [
						'SelectionCriteria' => new \stdClass(),
						'FieldNames' => ['Currency'],
						'Page' => [
							'Limit' => 1,
						],
					],
				])
			);
			if($response)
			{
				$response = Json::decode($response);
				if(!isset($response['error']) && isset($response['result']) && isset($response['result']['Campaigns']))
				{
					foreach($response['result']['Campaigns'] as $campaign)
					{
						$currency = $campaign['Currency'];
						break;
					}
				}
			}

			if($currency)
			{
				$cache->endDataCache(['currency' => $currency]);
			}
		}

		$this->currency = $currency;

		return $currency;
	}

	/**
	 * @param $status
	 * @return Error
	 */
	protected function getReportErrorByHttpStatus($status)
	{
		// https://tech.yandex.ru/direct/doc/examples-v5/php5-curl-stat1-docpage/
		$message = 'Unknown error';
		$code = 0;

		if($status == 400)
		{
			$message = 'Wrong parameters or too many reports';
		}
		elseif($status == 201 || $status == 202)
		{
			$message = 'Please try later';
			$code = static::ERROR_CODE_REPORT_OFFLINE;
		}
		elseif($status == 500)
		{
			$message = 'Some server error. Please try later';
		}
		elseif($status == 502)
		{
			$message = 'Server could not process your request in limited time. Please change your request';
		}

		return new Error($message, $code);
	}

	/**
	 * @param string $data
	 * @return array
	 */
	protected function parseReportData($data)
	{
		$result = [];
		if(!is_string($data) || empty($data))
		{
			return $result;
		}

		$titles = [];
		$strings = explode("\n", $data);
		foreach($strings as $number => $string)
		{
			if($number === 0)
			{
				$titles = explode("\t", $string);
			}
			elseif(!empty($string) && mb_strpos($string, 'Total') !== 0)
			{
				$row = array_combine($titles, explode("\t", $string));

				$result = $row;
			}
		}

		$conversions = (is_numeric($result['Conversions']) && $result['Conversions'])
			? $result['Conversions']
			: 0;
		$clicks = (is_numeric($result['Clicks']) && $result['Clicks'])
			? $result['Clicks']
			: 0;

		$result = [
			'impressions' => $result['Impressions'],
			'clicks' => $result['Clicks'],
			'actions' => $conversions + $clicks,
			'spend' => $result['Cost'],
			'cpc' => $result['AvgCpc'],
			'cpm' => $result['AvgCpm'],
			'currency' => $this->getCurrency(),
		];

		return $result;
	}

	/**
	 * @return string
	 */
	protected function getYandexServerAdress()
	{
		$isSandbox = false;
		//$isSandbox = true;

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