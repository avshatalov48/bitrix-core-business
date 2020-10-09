<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */

namespace Bitrix\Seo\Engine;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Engine;
use Bitrix\Seo\IEngine;
use Bitrix\Main\Text;
use Bitrix\Main\Text\Converter;

class Yandex extends Engine\YandexBase implements IEngine
{
	const ENGINE_ID = 'yandex';
	
	const SERVICE_URL = "https://webmaster.yandex.ru/api/v2";
	const API_BASE_URL = "https://api.webmaster.yandex.net/v3/user/";
	const API_HOSTS_URL = "hosts/";
	const API_SUMMARY_URL = "summary/";
	const API_SAMPLES_URL = "links/external/samples/";
	const API_POPULAR_URL = "search-queries/popular/";
	const API_VERIFICATION_URL = "verification/";
	const API_ORIGINAL_TEXTS_URL = "original-texts/";
	
	const HOSTS_SERVICE = "host-list";
	const HOST_VERIFY = "verify-host";
	const HOST_INFO = "host-information";
	const HOST_TOP_QUERIES = "top-queries";
	const HOST_ORIGINAL_TEXTS = "original-texts";
	const HOST_INDEXED = "indexed-urls";
	const HOST_EXCLUDED = "excluded-urls";
	
	const ORIGINAL_TEXT_MIN_LENGTH = 500;
	const ORIGINAL_TEXT_MAX_LENGTH = 32000;
	const ORIGINAL_TEXT_SHOW_COUNT = 50;
	
	const QUERY_USER = 'https://login.yandex.ru/info';
	
	const VERIFIED_STATE_VERIFIED = "VERIFIED";
	const VERIFIED_STATE_WAITING = "WAITING";
	const VERIFIED_STATE_FAILED = "VERIFICATION_FAILED";
	const VERIFIED_STATE_NEVER_VERIFIED = "NEVER_VERIFIED";
	const VERIFIED_STATE_IN_PROGRESS = "IN_PROGRESS";
	
	const INDEXING_STATE_OK = "OK";
	
	private static $verificationTypes = array('DNS', 'HTML_FILE', 'META_TAG', 'WHOIS', 'TXT_FILE');
	
	protected $engineId = 'yandex';
	protected $arServiceList = array();
	private $userId = NULL;
	private $hostIds = array();
	
	public function __construct()
	{
		parent::__construct();

//		save user ID from auth
		if (isset($this->engineSettings['AUTH_USER']['id']))
			$this->userId = $this->engineSettings['AUTH_USER']['id'];
	}
	
	/**
	 * Construct URL of service for REST request.
	 * Glue base URL and params: user ID, host ID, service name and additional url-params
	 * @param null $userId
	 * @param null $hostId
	 * @param null $service
	 * @param null $params
	 * @return string
	 */
	private function getServiceUrl($userId = NULL, $hostId = NULL, $service = NULL, $params = NULL)
	{
		$url = self::API_BASE_URL;
		
		if ($userId)
			$url .= $userId . '/';
		if ($hostId)
			$url .= 'hosts/' . $hostId . '/';
		if ($service)
			$url .= $service;
		if ($params)
		{
			if (is_array($params))
				$params = '?' . http_build_query($params);
			else
				$params = '?' . str_replace('?', '', $params);
			
			$url .= $params;
		}
		
		return $url;
	}
	
	// temporary hack
	public function getAuthSettings()
	{
		return $this->engineSettings['AUTH'];
	}
	
	/**
	 * For webmaster API v3 we can use host_id instead URL. Request them onec and then them save in local variable.
	 *
	 * @param $domain - URL of domain
	 * @return mixed
	 */
	private function getHostId($domain)
	{
//		get saved host ID
		if (isset($this->hostIds[$domain]) && !empty($this->hostIds[$domain]))
			return $this->hostIds[$domain];

//		else get host ID from API (host will be saved in local)
		$hosts = $this->getFeeds();
		
		return $hosts[$domain]['host_id'];
	}
	
	public function getFeeds()
	{
		$serviceUrl = $this->getServiceUrl($this->userId, NULL, self::API_HOSTS_URL);
		$queryResult = $this->query($serviceUrl, 'GET');
		
		if ($queryResult && $queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
		{
			$resultConverted = array();
			$result = Json::decode($queryResult->getResult());
			foreach ($result['hosts'] as $host)
			{
//				if set main mirror - we must use them
				if(array_key_exists("main_mirror", $host) && is_array($host["main_mirror"]) && !empty($host["main_mirror"]))
					$host = array_merge($host, $host["main_mirror"]);
					
//				ascii_host_url must be equal unicode_host_url for latin URLs.
//				if it cyrillic URL - we need ASCII host.
				$hostUrl = str_replace(array('http://', 'https://'), '', $host['ascii_host_url']);
				$hostUrl = rtrim($hostUrl, '/');
				$resultConverted[$hostUrl] = $host;
				
//				convert verified status in correct format
				if ($host['verified'])
					$resultConverted[$hostUrl]['verification'] = self::VERIFIED_STATE_VERIFIED;
//				save hostId in local var
				$this->hostIds[$hostUrl] = $host['host_id'];
			}
			
//			save found hosts to table
			$this->processHosts();
			
			return $resultConverted;
		}
		else
		{
			throw new Engine\YandexException($queryResult);
		}
	}
	
	/**
	 * Collect info about site by two different methods: info and statistic
	 * @param $domain
	 * @return array
	 */
	public function getSiteInfo($domain)
	{
		$result = array();
		
		$result += $this->getSiteInfoGeneral($domain);
		$result += $this->getSiteInfoStats($domain);
		
		return array($domain => $result);
	}
	
	private function getSiteInfoGeneral($domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId);
		$queryResult = $this->query($serviceUrl, 'GET');
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
			return Json::decode($queryResult->getResult());
		else
			throw new Engine\YandexException($queryResult);
	}
	
	private function getSiteInfoStats($domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_SUMMARY_URL);
		$queryResult = $this->query($serviceUrl, 'GET');
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
			return Json::decode($queryResult->getResult());
		else
			throw new Engine\YandexException($queryResult);
	}
	
//	todo: we can add info about external links like a popular queries
	
	/**
	 * Get info about popular queries for domain
	 * @param $domain - URL of domain
	 * @return array
	 * @throws YandexException
	 */
	public function getSiteInfoQueries($domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
//		get TOTAL_SHOWS
		$params = array(
			"order_by" => "TOTAL_SHOWS",
			"query_indicator" => "TOTAL_SHOWS",
		);
		
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_POPULAR_URL, $params);
//		dirt hack - our construcotr not understand multiply params
		$serviceUrl .= '&query_indicator=TOTAL_CLICKS';
		$serviceUrl .= '&query_indicator=AVG_SHOW_POSITION';
		$serviceUrl .= '&query_indicator=AVG_CLICK_POSITION';
		
		$queryResult = $this->query($serviceUrl, 'GET');
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
			$queriesShows = Json::decode($queryResult->getResult());
		else
			throw new Engine\YandexException($queryResult);
		
//		format out array
		$result = array();
		$totalShows = 0;
		$totalClicks = 0;
		foreach($queriesShows['queries'] as $key => $query)
		{
			$result[$key] = array(
				'TEXT' => $query['query_text'],
				'TOTAL_SHOWS' => $query['indicators']['TOTAL_SHOWS'],
				'TOTAL_CLICKS' => $query['indicators']['TOTAL_CLICKS'],
				'AVG_SHOW_POSITION' => is_null($query['indicators']['AVG_SHOW_POSITION']) ? '' :round($query['indicators']['AVG_SHOW_POSITION'], 1),
				'AVG_CLICK_POSITION' => is_null($query['indicators']['AVG_CLICK_POSITION']) ? '' :round($query['indicators']['AVG_CLICK_POSITION'], 1),
			);
			$totalShows += $query['indicators']['TOTAL_SHOWS'];
			$totalClicks += $query['indicators']['TOTAL_CLICKS'];
		}
		
		return array(
			'QUERIES' => $result,
			'DATE_FROM' => $queriesShows['date_from'],
			'DATE_TO' => $queriesShows['date_to'],
			'TOTAL_SHOWS' => $totalShows,
			'TOTAL_CLICKS' => $totalClicks,
		);
	}
	
	private function processHosts()
	{
		$existedDomains = \CSeoUtils::getDomainsList();
		
		foreach($existedDomains as $domain)
		{
			$domain['DOMAIN'] = ToLower($domain['DOMAIN']);

			if(isset($this->hostIds[$domain['DOMAIN']]))
			{
				if(!is_array($this->engineSettings['SITES']))
					$this->engineSettings['SITES'] = array();

				$this->engineSettings['SITES'][$domain['DOMAIN']] = $this->hostIds[$domain['DOMAIN']];
			}
		}
		
		$this->saveSettings();
	}
	
	public function getOriginalTexts($domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
		$counter = 0;
		$limit = self::ORIGINAL_TEXT_SHOW_COUNT;
		$result = array(
			'count' => 0,
			'quota_remainder' => 0,
			'can-add' =>false,
			'original_texts' => array(),
		);
		
//		recursive collect text ehilw not catch limit
		while($counter < $limit)
		{
//			default limit 10, may set other value
			$params = array('offset' => $counter);
			
			$stepResult = $this->getOriginalTextsRecursive($hostId, $params);
			$result['count'] = $stepResult['count'];
			$result['quota_remainder'] = $stepResult['quota_remainder'];
			$result['can-add'] = intval($result['quota_remainder']) > 0;
			$result['original_texts'] = array_merge($result['original_texts'], $stepResult['original_texts']);
			$counter += count($stepResult['original_texts']);
			
//			if catch last text - exit
			if($counter >= $result['count'])
				break;
		}
		
		return $result;
	}
	
	/**
	 * Need to collect all items throuth limits
	 *
	 * @param $hostId
	 * @param $params
	 * @return mixed
	 * @throws YandexException
	 */
	private function getOriginalTextsRecursive($hostId, $params)
	{
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_ORIGINAL_TEXTS_URL, $params);
		$queryResult = $this->query($serviceUrl, 'GET', $params);
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
			return Json::decode($queryResult->getResult());
		else
			throw new Engine\YandexException($queryResult);
	}
	
	
	/**
	 * Send original text to webmaster
	 *
	 * @param $text
	 * @param $domain
	 * @return string
	 * @throws YandexException
	 */
	public function addOriginalText($text, $domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);

//		create JSON data in correct format
		$data = array("content" => $text);
		$data = Json::encode($data);
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_ORIGINAL_TEXTS_URL);
		$queryResult = $this->query($serviceUrl, 'POST', $data);
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_CREATED && $queryResult->getResult() <> '')
			return $queryResult->getResult();
		else
			throw new Engine\YandexException($queryResult);
	}
	
	
	/**
	 * Add site to webmaster. After adding you need verify rights at this site.
	 *
	 * @param $domain
	 * @return array
	 * @throws YandexException
	 */
	public function addSite($domain)
	{
		$domain = ToLower($domain);
		$queryDomain = Context::getCurrent()->getRequest()->isHttps() ? 'https://' . $domain : $domain;

//		create JSON data in correct format
		$data = array("host_url" => $queryDomain);
		$data = Json::encode($data);
		$serviceUrl = $this->getServiceUrl($this->userId, NULL, self::API_HOSTS_URL);
		$queryResult = $this->query($serviceUrl, 'POST', $data);
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_CREATED && $queryResult->getResult() <> '')
			return array($domain => true);
		else
			throw new Engine\YandexException($queryResult);
	}
	
	
	/**
	 * Just checking verify status of site and get UIN for verification
	 * @param $domain
	 * @return UIN if site not verified and FALSE if site already verify.
	 * @throws YandexException
	 */
	public function getVerifySiteUin($domain)
	{
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_VERIFICATION_URL);
		$queryResult = $this->query($serviceUrl, 'GET');
		
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
		{
			$result = Json::decode($queryResult->getResult());
			if ($result['verification_state'] != self::VERIFIED_STATE_VERIFIED)
				return $result['verification_uin'];
			else
				return false;    //already verify
		}
		else
		{
			throw new Engine\YandexException($queryResult);
		}
	}

	public function verifySite($domain, $verType = 'HTML_FILE')
	{
		if (!in_array($verType, self::$verificationTypes))
			return array('error' => array('message' => 'incorrect verification type'));
		
		$domain = ToLower($domain);
		$hostId = $this->getHostId($domain);
		
		$serviceUrl = $this->getServiceUrl($this->userId, $hostId, self::API_VERIFICATION_URL, array('verification_type' => $verType));
		$queryResult = $this->query($serviceUrl, 'POST');
		if ($queryResult->getStatus() == self::HTTP_STATUS_OK && $queryResult->getResult() <> '')
		{
			$result = Json::decode($queryResult->getResult());
			
			return array($domain => array('verification' => $result['verification_state']));
		}
		else
		{
			throw new Engine\YandexException($queryResult);
		}
	}
	
	
	/**
	 * @deprecated by query
	 * @param $scope
	 * @param string $method
	 * @param null $data
	 * @param bool $skipRefreshAuth
	 * @return \CHTTP
	 */
	protected function queryOld($scope, $method = "GET", $data = NULL, $skipRefreshAuth = false)
	{
		if ($this->engineSettings['AUTH'])
		{
			$http = new \CHTTP();
			$http->setAdditionalHeaders(
				array(
					'Authorization' => 'OAuth ' . $this->engineSettings['AUTH']['access_token'],
				)
			);
			$http->setFollowRedirect(false);
			
			switch ($method)
			{
				case 'GET':
					$result = $http->get($scope);
					break;
				case 'POST':
					$result = $http->post($scope, $data);
					break;
				case 'PUT':
					$result = $http->httpQuery($method, $scope, $http->prepareData($data));
					break;
				case 'DELETE':
					
					break;
			}
			
			if ($http->status == 401 && !$skipRefreshAuth)
			{
				if ($this->checkAuthExpired())
				{
					$this->queryOld($scope, $method, $data, true);
				}
			}
			
			$http->result = Text\Encoding::convertEncoding($http->result, 'utf-8', LANG_CHARSET);
			
			return $http;
		}
	}
	
	/**
	 * Create HTTP client, set necessary headers and set request
	 *
	 * @param $scope - URL of service with additional params, if needed
	 * @param string $method - may be POST, GET or DELETE
	 * @param null $data
	 * @param bool $skipRefreshAuth
	 * @return HttpClient
	 */
	protected function query($scope, $method = "GET", $data = NULL, $skipRefreshAuth = false)
	{
		if ($this->engineSettings['AUTH'])
		{
			$http = new HttpClient();
			$http->setHeader('Authorization', 'OAuth ' . $this->engineSettings['AUTH']['access_token']);
			$http->setRedirect(false);
			switch ($method)
			{
				case 'GET':
					$http->get($scope);
					break;
				case 'POST':
					$http->setHeader('Content-type', 'application/json');
					$http->post($scope, $data);
					break;
				case 'DELETE':
					break;
			}

			if ($http->getStatus() == 401 && !$skipRefreshAuth)
			{
				if ($this->checkAuthExpired())
				{
					$this->query($scope, $method, $data, true);
				}
			}
			
			return $http;
		}
	}
}

?>