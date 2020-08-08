<?php
namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

if(!defined('REST_MARKETPLACE_URL'))
{
	define('REST_MARKETPLACE_URL', 'https://www.1c-bitrix.ru/buy_tmp/b24_app.php');
}

class Transport
{
	const SERVICE_URL = REST_MARKETPLACE_URL;

	const SOCKET_TIMEOUT = 10;
	const STREAM_TIMEOUT = 10;

	const METHOD_GET_LAST = 'get_last';
	const METHOD_GET_DEV = 'get_dev';
	const METHOD_GET_BEST = 'get_best';
	const METHOD_GET_SALE_OUT = 'get_sale_out';
	const METHOD_GET_BUY = 'get_buy';
	const METHOD_GET_UPDATES = 'get_updates';
	const METHOD_GET_CATEGORIES = 'get_categories';
	const METHOD_GET_CATEGORY = 'get_category';
	const METHOD_GET_TAG = 'get_tag';
	const METHOD_GET_APP = 'get_app';
	const METHOD_GET_APP_PUBLIC = 'get_app_public';
	const METHOD_GET_INSTALL = 'get_app_install';
	const METHOD_SET_INSTALL = 'is_installed';
	const METHOD_SEARCH_APP = 'search_app';
	const METHOD_FILTER_APP = 'search_app_adv';

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
	}

	public function call($method, $fields = array())
	{
		$query = $this->prepareQuery($method, $fields);

		$httpClient = new HttpClient(array(
			'socketTimeout' => static::SOCKET_TIMEOUT,
			'streamTimeout' => static::STREAM_TIMEOUT,
		));

		$response = $httpClient->post(self::SERVICE_URL, $query);

		return $this->prepareAnswer($response);
	}

	public function batch($actions)
	{
		$query = array();
		foreach($actions as $key => $batch)
		{
			$query[$key] = $this->prepareQuery($batch[0], $batch[1]);
		}

		$query = array('batch' => $query);

		$httpClient = new HttpClient();
		$response = $httpClient->post(self::SERVICE_URL, $query);

		return $this->prepareAnswer($response);
	}

	protected function prepareQuery($method, $fields)
	{
		if(!is_array($fields))
		{
			$fields = array();
		}

		$fields['action'] = $method;
		$fields['lang'] = LANGUAGE_ID;
		$fields['bsm'] = ModuleManager::isModuleInstalled('intranet') ? '0' : '1';

		if(Loader::includeModule('bitrix24'))
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

		return Encoding::convertEncoding($fields, LANG_CHARSET, 'utf-8');
	}

	protected function prepareAnswer($response)
	{
		$responseData = false;
		if($response && strlen($response) > 0)
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