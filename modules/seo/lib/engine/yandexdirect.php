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
use Bitrix\Main\Application;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Seo\Adv\LogTable;
use Bitrix\Seo\Adv\YandexBannerTable;
use Bitrix\Seo\Adv\YandexCampaignTable;
use Bitrix\Seo\Engine;
use Bitrix\Seo\IEngine;
use Bitrix\Main\Text;
use Bitrix\Seo\Service;

// to use Yandex.Direct Sandbox
if (!defined('YANDEX_DIRECT_API_URL'))
{
	define('YANDEX_DIRECT_API_URL', 'https://api.direct.yandex.ru/v4/json/');
}

class YandexDirect extends Engine\YandexBase implements IEngine
{
	const ENGINE_ID = 'yandex_direct';
	
	const API_URL = YANDEX_DIRECT_API_URL;
	
	const METHOD_REGION_GET = 'GetRegions';
	const METHOD_CAMPAIGN_ADD = 'CreateOrUpdateCampaign';
	const METHOD_CAMPAIGN_UPDATE = 'CreateOrUpdateCampaign';
	const METHOD_CAMPAIGN_GET = 'GetCampaignsParams';
	const METHOD_CAMPAIGN_LIST = 'GetCampaignsList';
	const METHOD_CAMPAIGN_ARCHIVE = 'ArchiveCampaign';
	const METHOD_CAMPAIGN_UNARCHIVE = 'UnArchiveCampaign';
	const METHOD_CAMPAIGN_STOP = 'StopCampaign';
	const METHOD_CAMPAIGN_RESUME = 'ResumeCampaign';
	const METHOD_CAMPAIGN_DELETE = 'DeleteCampaign';
	const METHOD_BANNER_ADD = 'CreateOrUpdateBanners';
	const METHOD_BANNER_UPDATE = 'CreateOrUpdateBanners';
	const METHOD_BANNER_LIST = 'GetBanners';
	const METHOD_BANNER_MODERATE = 'ModerateBanners';
	const METHOD_BANNER_STOP = 'StopBanners';
	const METHOD_BANNER_RESUME = 'ResumeBanners';
	const METHOD_BANNER_ARCHIVE = 'ArchiveBanners';
	const METHOD_BANNER_UNARCHIVE = 'UnArchiveBanners';
	const METHOD_BANNER_DELETE = 'DeleteBanners';
	const METHOD_WORDSTAT_REPORT_CREATE = 'CreateNewWordstatReport';
	const METHOD_WORDSTAT_REPORT_DELETE = 'DeleteWordstatReport';
	const METHOD_WORDSTAT_REPORT_GET = 'GetWordstatReport';
	const METHOD_WORDSTAT_REPORT_LIST = 'GetWordstatReportList';
	const METHOD_FORECAST_REPORT_CREATE = 'CreateNewForecast';
	const METHOD_FORECAST_REPORT_DELETE = 'DeleteForecastReport';
	const METHOD_FORECAST_REPORT_GET = 'GetForecast';
	const METHOD_FORECAST_REPORT_LIST = 'GetForecastList';
	const METHOD_STAT_BANNER = 'GetBannersStat';
	
	const BOOL_YES = "Yes";
	const BOOL_NO = "No";
	
	const STATUS_NEW = "New";
	const STATUS_PENDING = "Pending";
	
	const PRIORITY_LOW = "Low";
	const PRIORITY_MEDIUM = "Medium";
	const PRIORITY_HIGH = "High";
	
	const TTL_WORDSTAT_REPORT = 3600; // session report lifetime
	const TTL_WORDSTAT_REPORT_EXT = 18000; // yandex report lifetime
	const TTL_FORECAST_REPORT = 3600; // session report lifetime
	const TTL_FORECAST_REPORT_EXT = 18000; // yandex report lifetime
	
	const MAX_WORDSTAT_REPORTS = 5;
	const MAX_FORECAST_REPORTS = 5;
	const MAX_CAMPAIGNS_BANNER_UPDATE = 10;
	
	const ERROR_NOT_FOUND = 27;
	const ERROR_NO_STATS = 2;
	
	const MAX_STAT_DAYS_DELTA = 7;
	const CAMPAIGN_LIMIT = 100;
	
	const ERROR_WRONG_CURRENCY = 245;
	
	const CACHE_DIR = '/seo/yandexdirect/';
	const CACHE_TTL = 86400;
	const CACHE_ID = "yandexdirect_client_cache";
	
	public $allowedCurrency = array('RUB', 'CHF', 'EUR', 'KZT', 'TRY', 'UAH', 'USD');
	
	protected $engineId = 'yandex_direct';
	protected $locale = NULL;
	
	public function __construct()
	{
		$this->locale = in_array(LANGUAGE_ID, array("ru", "en", "ua")) ? LANGUAGE_ID : 'en';
		
		parent::__construct();
	}
	
	public function getCurrentUser()
	{
		if (Service::isRegistered())
		{
			$currentAuth = Service::getAuth($this->getCode());
			
			return $currentAuth['user'];
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Sends request to create new campaign
	 *
	 * @param array $campaignParam Campaign params.
	 *
	 * @returns string XML_ID for newly created campaign
	 * @throws SystemException
	 * @throws YandexDirectException
	 * @see YandexCampaignTable::createParam
	 */
	public function addCampaign(array $campaignParam)
	{
		$result = $this->getProxy()->getInterface()->addCampaign(static::ENGINE_ID, $campaignParam);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Sends request to update an existing campaign
	 *
	 * @param array $campaignParam Campaign params.
	 *
	 * @returns string XML_ID for newly created campaign
	 * @throws SystemException
	 * @throws YandexDirectException
	 * @see YandexCampaignTable::createParam
	 */
	public function updateCampaign(array $campaignParam)
	{
		$result = $this->getProxy()->getInterface()->updateCampaign(static::ENGINE_ID, $campaignParam);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Get campaigns by IDs (not all)
	 *
	 * @param mixed $campaignsId XML_ID or array of XML_IDs
	 *
	 * @return array with campaign data
	 * @throws SystemException
	 * @throws YandexDirectException
	 */
	public function getCampaign($campaignsId)
	{
		if (empty($campaignsId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		if (!is_array($campaignsId))
		{
			$campaignsId = array($campaignsId);
		}
		
		$offset = 0;
		
		$result = array();
		
		while ($offset < count($campaignsId))
		{
			$currentCampaigns = array_slice($campaignsId, $offset, static::CAMPAIGN_LIMIT);
			
			$currentResult = $this->getProxy()->getInterface()->getCampaign(static::ENGINE_ID, $currentCampaigns);
			
			if (!empty($currentResult['error']))
			{
				throw new YandexDirectException($currentResult);
			}
			
			$result = array_merge($result, $currentResult);
			
			$offset += static::CAMPAIGN_LIMIT;
		}
		
		return $result;
	}
	
//	get ALL campaigns for current client
	public function getCampaignList()
	{
		$result = $this->getProxy()->getInterface()->getCampaignList(static::ENGINE_ID);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function archiveCampaign($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$result = $this->getProxy()->getInterface()->archiveCampaign(static::ENGINE_ID, $campaignId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function unArchiveCampaign($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$result = $this->getProxy()->getInterface()->unArchiveCampaign(static::ENGINE_ID, $campaignId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function resumeCampaign($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$result = $this->getProxy()->getInterface()->resumeCampaign(static::ENGINE_ID, $campaignId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function stopCampaign($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$result = $this->getProxy()->getInterface()->stopCampaign(static::ENGINE_ID, $campaignId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function deleteCampaign($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$result = $this->getProxy()->getInterface()->deleteCampaign(static::ENGINE_ID, $campaignId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Sends request to create new banner
	 *
	 * @param array $bannerParam Banner params.
	 *
	 * @returns string XML_ID for newly created banner
	 * @throws SystemException
	 * @throws YandexDirectException
	 * @see YandexBannerTable::createParam
	 */
	public function addBanner(array $bannerParam)
	{
		$result = $this->getProxy()->getInterface()->addBanner(static::ENGINE_ID, $bannerParam);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Sends request to update an existing banner
	 *
	 * @param array $bannerParam Banner params.
	 *
	 * @returns string XML_ID for newly created banner
	 * @throws SystemException
	 * @throws YandexDirectException
	 * @see YandexBannerTable::createParam
	 */
	public function updateBanner(array $bannerParam)
	{
		$result = $this->getProxy()->getInterface()->updateBanner(static::ENGINE_ID, $bannerParam);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getBanners($bannerId)
	{
		if (empty($bannerId))
		{
			throw new ArgumentNullException("bannerId");
		}
		
		if (!is_array($bannerId))
		{
			$bannerId = array($bannerId);
		}
		
		$result = $this->getProxy()->getInterface()->getBannerList(static::ENGINE_ID, array(
			'BannerIDS' => $bannerId,
		));
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getCampaignBanners($campaignId)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		if (!is_array($campaignId))
		{
			$campaignId = array($campaignId);
		}
		
		$result = $this->getProxy()->getInterface()->getBannerList(static::ENGINE_ID, array(
			'CampaignIDS' => $campaignId,
		));
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function moderateBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		$result = $this->getProxy()->getInterface()->moderateBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function stopBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		
		$result = $this->getProxy()->getInterface()->stopBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function resumeBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		$result = $this->getProxy()->getInterface()->resumeBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function archiveBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		$result = $this->getProxy()->getInterface()->archiveBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function unArchiveBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		$result = $this->getProxy()->getInterface()->unArchiveBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function deleteBanners($campaignId, array $bannerIDs)
	{
		if (empty($campaignId))
		{
			throw new ArgumentNullException("campaignId");
		}
		
		$queryData = array(
			'CampaignID' => $campaignId,
			'BannerIDS' => $bannerIDs,
		);
		
		$result = $this->getProxy()->getInterface()->deleteBanners(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Returns Yandex regions list
	 *
	 * @return array of regions
	 * @throws SystemException
	 * @see https://tech.yandex.ru/direct/doc/dg-v4/reference/GetRegions-docpage/
	 */
	public function getRegions()
	{
		$result = $this->getProxy()->getInterface()->getRegions(static::ENGINE_ID);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getClientsSettings()
	{
		$cacheManager = Application::getInstance()->getManagedCache();
		
		if ($cacheManager->read(self::CACHE_TTL, self::CACHE_ID))
		{
			$result = $cacheManager->get(self::CACHE_ID);
		}
		else
		{
			$result = $this->getProxy()->getInterface()->getClientsSettings(static::ENGINE_ID);
		}
		
		if(!is_array($result) || empty($result))
			$result = array('error' => 'No authentication.');
		
		if (!empty($result['error']))
			throw new YandexDirectException($result);
		else
			$cacheManager->set(self::CACHE_ID, $result);
		
		return $result;
	}
	
	public function createWordstatReport(array $phrase, $geo = NULL)
	{
		$queryData = array(
			'Phrases' => $phrase,
		);
		
		if (is_array($geo))
		{
			$queryData['GeoID'] = $geo;
		}
		
		$result = $this->getProxy()->getInterface()->createWordstatReport(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function deleteWordstatReport($reportId)
	{
		$result = $this->getProxy()->getInterface()->deleteWordstatReport(static::ENGINE_ID, $reportId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getWordstatReport($reportId)
	{
		$result = $this->getProxy()->getInterface()->getWordstatReport(static::ENGINE_ID, $reportId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getWordstatReportList()
	{
		$result = $this->getProxy()->getInterface()->getWordstatReportList(static::ENGINE_ID);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function createForecastReport(array $phrase, $geo = NULL)
	{
		$queryData = array(
			'Phrases' => $phrase,
		);
		
		if (is_array($geo))
		{
			$queryData['GeoID'] = $geo;
		}
		
		$result = $this->getProxy()->getInterface()->createForecastReport(static::ENGINE_ID, $queryData);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function deleteForecastReport($reportId)
	{
		$result = $this->getProxy()->getInterface()->deleteForecastReport(static::ENGINE_ID, $reportId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getForecastReport($reportId)
	{
		$result = $this->getProxy()->getInterface()->getForecastReport(static::ENGINE_ID, $reportId);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	public function getForecastReportList()
	{
		$result = $this->getProxy()->getInterface()->getForecastReportList(static::ENGINE_ID);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Sends request for banner stats.
	 *
	 * @param array $params Stats query params.
	 *
	 * @return array
	 * @throws YandexDirectException
	 * @throws \Bitrix\Main\SystemException
	 *
	 * @see https://tech.yandex.ru/direct/doc/dg-v4/live/GetBannersStat-docpage/
	 */
	public function getBannerStats(array $params)
	{
		$result = $this->getProxy()->getInterface()->getBannerStats(static::ENGINE_ID, $params);
		
		if (!empty($result['error']))
		{
			throw new YandexDirectException($result);
		}
		
		return $result;
	}
	
	/**
	 * Returns HttpClient object with query result
	 *
	 * @param string $scope
	 * @param string $method Method
	 * @param array $param array of query data
	 * @param bool $skipRefreshAuth Skip authorization refresh. Doesn't work with Yandex.
	 * @return HttpClient
	 * @throws SystemException
	 * @throws \Exception
	 */
	protected function query($scope, $method = "GET", $param = NULL, $skipRefreshAuth = false)
	{
		if ($param === NULL)
		{
			$param = array();
		}
		
		if ($this->engineSettings['AUTH'])
		{
			$http = new HttpClient();
			$http->setRedirect(false);
			$http->setHeader("Content-Type", "application/json; charset=utf-8");
			
			$postData = array(
				"method" => $method,
				"locale" => $this->locale,
				"token" => $this->engineSettings['AUTH']['access_token'],
			);
			
			if (!empty($param))
			{
				$postData["param"] = $param;
			}
			
			$postData = YandexJson::encode($postData, JSON_UNESCAPED_UNICODE);
			
			$ts = microtime(true);
			$http->post(static::API_URL, $postData);
			
			LogTable::add(array(
				'ENGINE_ID' => $this->getId(),
				'REQUEST_URI' => static::API_URL,
				'REQUEST_DATA' => Text\Encoding::convertEncoding($postData, 'UTF-8', SITE_CHARSET),
				'RESPONSE_TIME' => microtime(true) - $ts,
				'RESPONSE_STATUS' => $http->getStatus(),
				'RESPONSE_DATA' => Text\Encoding::convertEncoding($http->getResult(), 'UTF-8', SITE_CHARSET),
			));
			
			if ($http->getStatus() == 401 && !$skipRefreshAuth)
			{
				if ($this->checkAuthExpired())
				{
					$this->query($scope, $method, $param, true);
				}
			}
			
			return $http;
		}
		else
		{
			throw new SystemException("No Yandex auth data");
		}
	}
	
	public function finance_query($method, $masterToken, $operationNum, $param = array(), $skipRefreshAuth = false)
	{
		if ($this->engineSettings['AUTH'])
		{
			$http = new HttpClient();
			$http->setRedirect(false);
			$http->setHeader("Content-Type", "application/json; charset=utf-8");
			
			$auth = $this->getCurrentUser();
			
			$financeToken = hash(
				"sha256",
				$masterToken . $operationNum . $method . $auth['login']);
			
			$postData = array(
				"method" => $method,
				"finance_token" => $financeToken,
				"operation_num" => $operationNum,
				"locale" => $this->locale,
				"token" => $this->engineSettings['AUTH']['access_token'],
			);
			
			if (!empty($param))
			{
				$postData["param"] = $param;
			}
			
			$postData = YandexJson::encode($postData, JSON_UNESCAPED_UNICODE);
			
			$http->post(self::API_URL, $postData);
			
			if ($http->getStatus() == 401 && !$skipRefreshAuth)
			{
				if ($this->checkAuthExpired())
				{
					$this->query("", $method, $param, true);
				}
			}
			
			return $http;
		}
		else
		{
			throw new SystemException("No Yandex auth data");
		}
	}
	
	public function updateCampaignManual($campaignId = NULL)
	{
		$newCampaigns = array();
		
		$res = array(
			'added' => 0,
			'updated' => 0,
			'error' => 0,
		);
		
		$keys = array();
		
		if (!is_array($campaignId) && $campaignId > 0)
		{
			$campaignId = array($campaignId);
		}
		
		$campaignList = array();
		if (is_array($campaignId) && count($campaignId) > 0)
		{
//			get just current campaigns by ID
			$dbRes = YandexCampaignTable::getList(array(
				'filter' => array(
					'=ID' => $campaignId,
					'=ENGINE_ID' => $this->getId(),
				),
				'select' => array('XML_ID'),
			));
			
			while ($campaign = $dbRes->fetch())
			{
				$keys[] = $campaign['XML_ID'];
			}
			
			if (count($keys) > 0)
				$campaignList = $this->getCampaign($keys);
		}
		else
		{
//			get ALL campaigns, if IDs not set
			$campaignList = $this->getCampaignList();
		}
		
		$campaignListSorted = array();
		$campaignListToDelete = array();
		
		foreach ($campaignList as $campaignInfo)
		{
			$campaignListSorted[$campaignInfo['CampaignID']] = $campaignInfo;
		}
		
		$filter = array('=ENGINE_ID' => $this->getId());
//		get filtered items only if we update only selected campaigns. Else - get ALL from table
		if(is_array($campaignId) && count($campaignId) > 0 && count($campaignListSorted) > 0)
			$filter['=XML_ID'] = array_keys($campaignListSorted);
		$dbCampaigns = YandexCampaignTable::getList(array('filter' => $filter));
		
		YandexCampaignTable::setSkipRemoteUpdate(true);
//		UPDATE existing in table campaigns
		while ($campaign = $dbCampaigns->fetch())
		{
			if (isset($campaignListSorted[$campaign['XML_ID']]))
			{
				$result = YandexCampaignTable::update(
					$campaign['ID'], array(
						"SETTINGS" => $campaignListSorted[$campaign['XML_ID']],
					)
				);
				
				unset($campaignListSorted[$campaign['XML_ID']]);
				
				if ($result->isSuccess())
				{
					$res['updated']++;
				}
				else
				{
					$res['error']++;
				}
			}
//			collect campaigns, then not exist in YD, but exist in table
			else
			{
				$campaignListToDelete[$campaign['ID']] = $campaign['ID'];
			}
		}

//		REMOVE from table deleted campaigns
		if (count($campaignListToDelete) > 0)
		{
			foreach ($campaignListToDelete as $campaignId)
			{
				$resultDelete = YandexCampaignTable::delete($campaignId);
//					todo: skipRempoteUpdate in campaign table or in banner table? o_O
				YandexCampaignTable::setSkipRemoteUpdate(true);
			}
		}

//		ADD in table new campaignt from YD
		foreach ($campaignListSorted as $campaignId => $campaignInfo)
		{
			$result = YandexCampaignTable::add(array(
				"SETTINGS" => $campaignInfo,
			));
			
			if ($result->isSuccess())
			{
				$newCampaigns[] = $result->getId();
				$res['added']++;
			}
			else
			{
				$res['error']++;
			}
		}
		YandexCampaignTable::setSkipRemoteUpdate(false);
		
		if (count($newCampaigns) > 0)
		{
			set_time_limit(300);
			
			$res['new'] = $newCampaigns;
			
			$res['banner'] = array();
			$cnt = ceil(count($newCampaigns) / static::MAX_CAMPAIGNS_BANNER_UPDATE);
			for ($i = 0; $i < $cnt; $i++)
			{
				$res['banner'] = array_merge(
					$res['banner'],
					$this->updateBannersManual(
						array_slice(
							$newCampaigns,
							$i * static::MAX_CAMPAIGNS_BANNER_UPDATE,
							static::MAX_CAMPAIGNS_BANNER_UPDATE
						)
					)
				);
			}
		}
		
		return $res;
	}
	
	public function updateBannersManual($campaignId, $bannerId = NULL)
	{
		$res = array(
			'added' => 0,
			'updated' => 0,
			'error' => 0,
		);
		
		if (!is_array($bannerId) && $bannerId > 0)
		{
			$bannerId = array($bannerId);
		}
		
		$bannerList = array();
		if (is_array($bannerId) && count($bannerId) > 0)
		{
//			get banners by ID
			$dbRes = YandexBannerTable::getList(array(
				'filter' => array(
					'=ID' => $bannerId,
					'=ENGINE_ID' => $this->getId(),
				),
				'select' => array('XML_ID'),
			));
			
			while ($banner = $dbRes->fetch())
			{
				$keys[] = $banner['XML_ID'];
			}
			
			if(count($keys) > 0)
				$bannerList = $this->getBanners($keys);
		}
		else
		{
			$dbCampaigns = YandexCampaignTable::getList(array(
				'filter' => array(
					'=ID' => $campaignId,
					'=ENGINE_ID' => $this->getId(),
				),
				'select' => array('ID', 'XML_ID'),
			));
			
			while ($campaign = $dbCampaigns->fetch())
			{
				$campaignIndex[$campaign['XML_ID']] = $campaign['ID'];
			}
			
//			get ALL banners for current campaign
			if (count($campaignIndex) > 0)
				$bannerList = $this->getCampaignBanners(array_keys($campaignIndex));
		}
		
		$bannerListSorted = array();
		$bannerListToDelete = array();
		foreach ($bannerList as $bannerInfo)
		{
			$bannerListSorted[$bannerInfo['BannerID']] = $bannerInfo;
		}
		
		$filter = array('=ENGINE_ID' => $this->getId());
//			get filtered items only if we update only selected banners. Else - get ALL from table
		if(is_array($bannerId) && count($bannerId) > 0 && count($bannerListSorted) > 0)
			$filter['=XML_ID'] = array_keys($bannerListSorted);
		if($campaignId)
			$filter['=CAMPAIGN_ID'] = $campaignId;
		$dbBanners = YandexBannerTable::getList(array('filter' => $filter));
					
		YandexBannerTable::setSkipRemoteUpdate(true);
//			UPDATE existing in table banners
		while ($banner = $dbBanners->fetch())
		{
			if (isset($bannerListSorted[$banner['XML_ID']]))
			{
				$result = YandexBannerTable::update(
					$banner['ID'], array(
						"SETTINGS" => $bannerListSorted[$banner['XML_ID']],
					)
				);
				
				unset($bannerListSorted[$banner['XML_ID']]);
				
				if ($result->isSuccess())
				{
					$res['updated']++;
				}
				else
				{
					$res['error']++;
				}
			}
//				collect banners, then not exist in YD, but exist in table
			else
			{
				$bannerListToDelete[$banner['ID']] = $banner['ID'];
			}
		}
		
//			REMOVE from table deleted banners
		if (count($bannerListToDelete) > 0)
		{
			foreach ($bannerListToDelete as $bannerId)
			{
				$resultDelete = YandexBannerTable::delete($bannerId);
				YandexBannerTable::setSkipRemoteUpdate(true);
			}
		}

//			ADD in table new campaignt from YD
		foreach ($bannerListSorted as $bannerId => $bannerInfo)
		{
			$result = YandexBannerTable::add(array(
				"CAMPAIGN_ID" => $campaignIndex[$bannerInfo['CampaignID']],
				"SETTINGS" => $bannerInfo,
			));
			
			if ($result->isSuccess())
			{
				$res['added']++;
			}
			else
			{
				$res['error']++;
			}
		}
		YandexBannerTable::setSkipRemoteUpdate(false);
		
		return $res;
	}
	
	
	public static function updateAgent()
	{
		$engine = new self();
		if ($engine->getAuthSettings())
		{
			try
			{
				$dbRes = YandexCampaignTable::getList(array(
					'filter' => array(
						'<LAST_UPDATE' => DateTime::createFromTimestamp(time() - YandexCampaignTable::CACHE_LIFETIME),
						'=ENGINE_ID' => $engine->getId(),
					),
					'select' => array('CNT'),
					'runtime' => array(
						new ExpressionField('CNT', 'COUNT(*)'),
					),
				));
				
				$res = $dbRes->fetch();
				if ($res['CNT'] > 0)
				{
					$engine->updateCampaignManual();
				}
				
				$availableCampaigns = array();
				$campaignList = $engine->getCampaignList();
				foreach ($campaignList as $campaignInfo)
				{
					$availableCampaigns[] = $campaignInfo['CampaignID'];
				}
				
				if (count($availableCampaigns) > 0)
				{
					$dbRes = YandexBannerTable::getList(array(
						'group' => array('CAMPAIGN_ID'),
						'filter' => array(
							'<LAST_UPDATE' => DateTime::createFromTimestamp(time() - YandexBannerTable::CACHE_LIFETIME),
							'=ENGINE_ID' => $engine->getId(),
							'=CAMPAIGN.XML_ID' => $availableCampaigns,
						),
						'select' => array('CAMPAIGN_ID'),
					));
					
					$campaignId = array();
					while ($res = $dbRes->fetch())
					{
						$campaignId[] = $res['CAMPAIGN_ID'];
					}
					
					if (count($campaignId) > 0)
					{
						$engine->updateBannersManual($campaignId);
					}
				}
			}
			catch (YandexDirectException $e)
			{
			}
		}
		
		return __CLASS__ . "::updateAgent();";
	}
}
