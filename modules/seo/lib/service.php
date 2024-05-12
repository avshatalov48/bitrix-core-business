<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2013 Bitrix
 */
namespace Bitrix\Seo;

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Seo\Engine\Bitrix;
use Bitrix\Seo\Retargeting\AdsAudience;

Loc::loadMessages(__FILE__);

if(!defined("BITRIX_CLOUD_ADV_URL"))
{
	define("BITRIX_CLOUD_ADV_URL", 'https://cloud-adv.bitrix.info');
}

if(!defined("SEO_SERVICE_URL"))
{
	define('SEO_SERVICE_URL', BITRIX_CLOUD_ADV_URL);
}

class Service
{
	const SERVICE_URL = SEO_SERVICE_URL;
	const REGISTER = "/oauth/register/";
	const AUTHORIZE = "/register/";
	const REDIRECT_URI = "/bitrix/tools/seo_client.php";

	const SERVICE_AUTH_CACHE_TLL = 86400;
	const SERVICE_AUTH_CACHE_TLL_ERROR = 3600;
	const SERVICE_AUTH_CACHE_ID = 'seo|service_auth';
	const SERVICE_AUTH_CACHE_ID_ERROR = 'seo|service_auth_error';

	const CLIENT_LIST_CACHE_TLL = 86400;
	const CLIENT_LIST_CACHE_ID = 'seo|client_list|2';

	const  CLIENT_TYPE_SINGLE = 'S';
	const  CLIENT_TYPE_MULTIPLE = 'M';
	const  CLIENT_TYPE_COMPATIBLE = 'C';

	protected static $engine = null;
	protected static $auth = null;
	protected static $clientList = null;

	/**
	 * CAn connect to seoproxy?
	 * @return bool
	 */
	public static function isRegistered()
	{
		return static::getEngine() ? static::getEngine()->isRegistered() : false;
	}

	/**
	 * Get client info
	 * @use \Bitrix\Seo\Service::getClientList(...)
	 *
	 * @param string $engineCode Provider code.
	 * @return boolean|array
	 * @deprecated
	 */
	public static function getAuth(string $engineCode)
	{
		global $CACHE_MANAGER;
		if (static::$auth === null)
		{
			if ($CACHE_MANAGER->Read(static::SERVICE_AUTH_CACHE_TLL, static::SERVICE_AUTH_CACHE_ID))
			{
				static::$auth = $CACHE_MANAGER->Get(static::SERVICE_AUTH_CACHE_ID);
			}
			elseif (!$CACHE_MANAGER->Read(static::SERVICE_AUTH_CACHE_TLL_ERROR, static::SERVICE_AUTH_CACHE_ID_ERROR))
			{
				static::$auth = static::getEngine()->getInterface()->getClientInfo();
				if (!static::$auth)
				{
					static::$auth = false;
					$CACHE_MANAGER->Read(static::SERVICE_AUTH_CACHE_TLL_ERROR, static::SERVICE_AUTH_CACHE_ID_ERROR);
					$CACHE_MANAGER->Set(static::SERVICE_AUTH_CACHE_ID_ERROR, static::$auth);
				}
				else
				{
					$CACHE_MANAGER->Set(static::SERVICE_AUTH_CACHE_ID, static::$auth);
				}
			}
			else
			{
				static::$auth = false;
			}
		}

		if (static::$auth)
		{
			return static::$auth["engine"][$engineCode];
		}

		return false;
	}

	/**
	 * Get clients list
	 * @param string|bool $engineCode Provider code.
	 * @return array
	 * @throws SystemException
	 */
	public static function getClientList($engineCode = false)
	{
		if( static::$clientList == null)
		{
			$cache = Application::getInstance()->getManagedCache();
			if ($cache->read(static::CLIENT_LIST_CACHE_TLL, static::CLIENT_LIST_CACHE_ID))
			{
				static::$clientList = $cache->get(static::CLIENT_LIST_CACHE_ID);
				static::$clientList = is_array(static::$clientList) ? static::$clientList : [];
			}
			else
			{
				$clientDataProvider = static::getEngine()->getInterface();
				$result = $clientDataProvider->getClientList();
				if (!is_array($result)) // backward compatibility
				{
					$result = [];
					$data = $clientDataProvider->getClientInfo();
					if (is_array($data))
					{
						foreach ($data as $code => $client)
						{
							$data['proxy_client_type'] = static::CLIENT_TYPE_COMPATIBLE;
							$data['engine_code'] = $code;
							$data['proxy_client_id'] = null;
							$result[] = $data;
						}
					}
				}
				else
				{
					$result = $result['items'];
				}
				$cache->set(static::CLIENT_LIST_CACHE_ID, $result);
				static::$clientList = $result;
			}
		}
		if ($engineCode)
		{
			return array_filter(static::$clientList, function ($item) use ($engineCode) {
				return $item['engine_code'] == $engineCode;
			});
		}
		return static::$clientList;
	}

	/**
	 * @return void
	 * @use \Bitrix\Seo\Service::clearClientsCache()
	 * @deprecated
	 */
	public static function clearLocalAuth()
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->Clean(static::SERVICE_AUTH_CACHE_ID);

		static::$auth = null;
	}

	/**
	 * Clear clients list cache
	 * @param string|null $engine Engine code.
	 * @param int|null $clientId Proxy client id.
	 * @return void
	 * @throws SystemException
	 */
	public static function clearClientsCache($engine = null, $clientId = null)
	{
		$cache = Application::getInstance()->getManagedCache();
		$cache->Clean(static::CLIENT_LIST_CACHE_ID);
		$cache->Clean(static::SERVICE_AUTH_CACHE_ID);
		$cache->Clean(static::SERVICE_AUTH_CACHE_ID_ERROR);

		[$group, $type] = explode('.', $engine, 2);

		if ($group == \Bitrix\Seo\Retargeting\Service::GROUP)
		{
			$service = AdsAudience::getService();
			$service->setClientId($clientId);
			$account = $service->getAccount($type);
			if ($account)
				$account->clearCache();
		}

		static::$clientList = null;
		static::$auth = null;
	}

	/**
	 * @param string $engineCode Provider code.
	 * @param bool $localOnly Do not delete client in seoproxy service.
	 * @return void
	 * @use \Bitrix\Seo\Service::clearAuthForClient(...)
	 * @throws SystemException
	 * @deprecated
	 */
	public static function clearAuth($engineCode, $localOnly = false)
	{
		static::clearClientsCache($engineCode);

		if(!$localOnly)
		{
			static::getEngine()->getInterface()->clearClientAuth($engineCode);
		}
	}

	/**
	 * Remove auth for a client
	 * @param array $client Client data.
	 * @param bool $localOnly Only clear cache.
	 * @return void
	 * @throws SystemException
	 */
	public static function clearAuthForClient($client, $localOnly = false)
	{
		if(!$localOnly)
		{
			static::getEngine()->getInterface()->clearClientAuth($client['engine_code'], $client['proxy_client_id']);
		}
		static::clearClientsCache($client['engine_code'], $client['proxy_client_id']);
	}

	/**
	 * Set access settings
	 * @param array $accessParams Access params.
	 * @return void
	 * @throws SystemException
	 */
	protected static function setAccessSettings(array $accessParams)
	{
		if(static::isRegistered())
		{
			$id = static::getEngine()->getId();

			$result = SearchEngineTable::update($id, array(
				"CLIENT_ID" => $accessParams["client_id"],
				"CLIENT_SECRET" => $accessParams["client_secret"],
				"SETTINGS" => "",
			));
		}
		else
		{
			$result = SearchEngineTable::add(array(
				"CODE" => Bitrix::ENGINE_ID,
				"NAME" => "Bitrix",
				"ACTIVE" => SearchEngineTable::ACTIVE,
				"CLIENT_ID" => $accessParams["client_id"],
				"CLIENT_SECRET" => $accessParams["client_secret"],
				"REDIRECT_URI" => static::getRedirectUri(),
			));
		}

		if($result->isSuccess())
		{
			static::clearAuth(Bitrix::ENGINE_ID, true);
			static::$engine = null;
		}
	}

	/**
	 * @return \Bitrix\Seo\Engine\Bitrix
	 */
	public static function getEngine()
	{
		if(!static::$engine && Loader::includeModule("socialservices"))
		{
			static::$engine = new Bitrix();
		}

		return static::$engine;
	}

	/**
	 * @return void
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function register()
	{
		static::clearClientsCache();

		$httpClient = new HttpClient();

		$queryParams = [
			"key" => static::getLicense(),
			"scope" => static::getEngine()->getInterface()->getScopeEncode(),
			"redirect_uri" => static::getRedirectUri(),
		];

		$result = $httpClient->post(static::SERVICE_URL.static::REGISTER, $queryParams);
		$result = Json::decode($result);

		if($result["error"])
		{
			throw new SystemException($result["error"]);
		}

		static::setAccessSettings($result);
	}

	/**
	 * @return void
	 * @throws SystemException
	 */
	public static function unregister()
	{
		if(static::isRegistered())
		{
			$id = static::getEngine()->getId();
			SearchEngineTable::delete($id);
			static::clearClientsCache();
		}
	}

	/**
	 * @return string
	 */
	public static function getAuthorizeLink()
	{
		return static::SERVICE_URL.static::AUTHORIZE;
	}

	/**
	 * @param string $engine Provider code.
	 * @param bool $clientType Client type.
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getAuthorizeData($engine, $clientType = false): array
	{
		$checkKey = "";
		$session = Application::getInstance()
			->getSession()
		;

		if (Loader::includeModule("socialservices") && $session->isAccessible())
		{
			$checkKey = \CSocServAuthManager::GetUniqueKey();
		}

		$clientType = $clientType ?: Service::CLIENT_TYPE_COMPATIBLE;

		return [
			"action" => "authorize",
			"type" => $clientType,
			"engine" => $engine,
			"client_id" => static::getEngine()->getClientId(),
			"client_secret" => static::getEngine()->getClientSecret(),
			"key" => static::getLicense(),
			"check_key" => urlencode($checkKey),
			"redirect_uri" => static::getRedirectUri(),
		];
	}

	/**
	 * @return string
	 */
	protected static function getRedirectUri(): string
	{
		$request = Context::getCurrent()->getRequest();

		$host = $request->getHttpHost();
		$port = (int)$request->getServerPort();
		$host .= ($port && $port !== 80 && $port !== 443) ? ":{$port}" : '';

		$isHttps = $request->isHttps();

		return ($isHttps ? 'https' : 'http').'://'.$host.static::REDIRECT_URI;
	}

	/**
	 * @return string
	 */
	protected static function getLicense(): string
	{
		return md5(LICENSE_KEY);
	}

	/**
	 * If site change domain - need update engine
	 * @param array $domains
	 * @throws \Exception
	 */
	public static function changeRegisteredDomain(array $domains = []): void
	{
		if (!self::isRegistered())
		{
			return;
		}
		if(!$engine = static::getEngine())
		{
			return;
		}

		$newRedirectUri = static::getRedirectUri();
		if(!empty($domains))
		{
			$newRedirectUri = str_replace($domains['old_domain'], $domains['new_domain'], $newRedirectUri);
		}

		SearchEngineTable::update($engine->getId(), [
			'REDIRECT_URI' => $newRedirectUri
		]);
	}
}
