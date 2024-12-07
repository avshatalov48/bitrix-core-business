<?php
namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

if (!defined('REST_MARKETPLACE_URL'))
{
	define('REST_MARKETPLACE_URL', '');
}

class Transport
{
	/** @deprecated use Transport()->getServiceUrl() */
	const SERVICE_URL = REST_MARKETPLACE_URL;
	protected const VERSION = 1;
	private const API_VERSION = 1;

	protected string $serviceDomain = '';
	private const DEFAULT_SERVICE_REGION = 'en';
	private const SERVICE_DOMAIN_LIST = [
		'en' => 'https://util.bitrixsoft.com/',
		'ru' => 'https://util.1c-bitrix.ru/',
		'kz' => 'https://util.1c-bitrix.kz/',
		'by' => 'https://util.1c-bitrix.by/',
		'ua' => 'https://util.bitrix.ua/',
	];
	public const SERVICE_TYPE_APP = 'APP';
	public const SERVICE_TYPE_COUPON = 'COUPON';
	private const SERVICE_URN_LIST = [
		self::SERVICE_TYPE_APP => 'b24/apps.php',
		self::SERVICE_TYPE_COUPON => 'b24/b24_coupon.php',
	];

	const SOCKET_TIMEOUT = 10;
	const STREAM_TIMEOUT = 10;

	const METHOD_GET_LAST = 'get_last';
	const METHOD_GET_DEV = 'get_dev';
	const METHOD_GET_BEST = 'get_best';
	const METHOD_GET_SALE_OUT = 'get_sale_out';
	const METHOD_GET_BUY = 'get_buy';
	const METHOD_GET_UPDATES = 'get_updates';
	const METHOD_GET_IMMUNE = 'get_immune';
	const METHOD_GET_CATEGORIES = 'get_categories';
	const METHOD_GET_CATEGORY = 'get_category';
	const METHOD_GET_TAG = 'get_tag';
	const METHOD_GET_APP = 'get_app';
	const METHOD_GET_APP_PUBLIC = 'get_app_public';
	const METHOD_GET_INSTALL = 'get_app_install';
	const METHOD_SET_INSTALL = 'is_installed';
	const METHOD_SEARCH_APP = 'search_app';
	const METHOD_FILTER_APP = 'search_app_adv';
	const METHOD_GET_SITE_LIST = 'sites_list';
	const METHOD_GET_SITE_ITEM = 'sites_item';

	protected static $instance = null;

	/**
	 * Resturns class instance.
	 *
	 * @return \Bitrix\Rest\Marketplace\Transport
	 */
	public static function instance()
	{
		if(static::$instance == null)
		{
			static::$instance = new self();
		}

		return static::$instance;
	}


	public function __construct()
	{
		if (Loader::includeModule('bitrix24'))
		{
			$region = \CBitrix24::getLicensePrefix();
		}
		else
		{
			$region = Option::get('main', '~PARAM_CLIENT_LANG', LANGUAGE_ID);
		}
		$this->serviceDomain = self::SERVICE_DOMAIN_LIST[$region] ?? self::SERVICE_DOMAIN_LIST[self::DEFAULT_SERVICE_REGION];
	}

	/**
	 * Returns service url.
	 *
	 * @param string $type
	 * @return string
	 */
	public function getServiceUrl(string $type = self::SERVICE_TYPE_APP): string
	{
		if ($type === self::SERVICE_TYPE_APP && !empty(self::SERVICE_URL))
		{
			return self::SERVICE_URL;
		}

		return self::SERVICE_URN_LIST[$type] ? $this->serviceDomain . self::SERVICE_URN_LIST[$type] : '';
	}

	public function call($method, $fields = array())
	{
		$query = $this->prepareQuery($method, $fields);

		$httpClient = new HttpClient(array(
			'socketTimeout' => static::SOCKET_TIMEOUT,
			'streamTimeout' => static::STREAM_TIMEOUT,
		));

		$response = $httpClient->post($this->getServiceUrl(), $query);

		return $this->prepareAnswer($response);
	}

	public function batch($actions)
	{
		$query = array();

		foreach($actions as $key => $batch)
		{
			if (!isset($batch[1]))
			{
				$batch[1] = [];
			}
			$query[$key] = $this->prepareQuery($batch[0], $batch[1]);
		}

		$query = array('batch' => $query);

		$httpClient = new HttpClient();
		$response = $httpClient->post($this->getServiceUrl(), $query);

		return $this->prepareAnswer($response);
	}

	protected function prepareQuery($method, $fields)
	{
		if(!is_array($fields))
		{
			$fields = array();
		}

		$fields['action'] = $method;
		$fields['apiVersion'] = self::API_VERSION;

		if (Client::isSubscriptionAccess())
		{
			$fields['queryVersion'] = static::VERSION;
		}
		$fields['lang'] = LANGUAGE_ID;
		$fields['bsm'] = ModuleManager::isModuleInstalled('intranet') ? '0' : '1';

		if(Loader::includeModule('bitrix24') && defined('BX24_HOST_NAME'))
		{
			$fields['tariff'] = \CBitrix24::getLicensePrefix();
			$fields['host_name'] = BX24_HOST_NAME;
		}
		else
		{
			$request = Context::getCurrent()->getRequest();
			$fields['host_name'] = $request->getHttpHost();
			@include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/license_key.php');
			$fields['license_key'] = ($LICENSE_KEY == 'DEMO') ? 'DEMO' : md5('BITRIX' . $LICENSE_KEY . 'LICENCE');
		}

		return $fields;
	}

	protected function prepareAnswer($response)
	{
		$responseData = false;
		if($response && $response <> '')
		{
			try
			{
				$responseData = Json::decode($response);
			}
			catch(ArgumentException $e)
			{
				$responseData = false;
			}
		}
		return is_array($responseData) ? $responseData : false;
	}
}
