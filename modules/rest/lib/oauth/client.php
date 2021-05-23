<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage rest
 * @copyright 2001-2016 Bitrix
 */


namespace Bitrix\Rest\OAuth;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\OAuthService;

if(!defined("BITRIX_OAUTH_URL"))
{
	$defaultValue = \Bitrix\Main\Config\Option::get('rest', 'oauth_server', 'https://oauth.bitrix.info');
	define("BITRIX_OAUTH_URL", $defaultValue);
}

if(!defined('BITRIXREST_URL'))
{
	define('BITRIXREST_URL', BITRIX_OAUTH_URL);
}

class Client
{
	const SERVICE_URL = BITRIXREST_URL;
	const SERVICE_PATH = "/rest/";

	const METHOD_METHODS = 'methods';
	const METHOD_BATCH = 'batch';

	const METHOD_APPLICATION_ADD = 'application.add';
	const METHOD_APPLICATION_UPDATE = 'application.update';
	const METHOD_APPLICATION_DELETE = 'application.delete';
	const METHOD_APPLICATION_INSTALL = 'application.install';
	const METHOD_APPLICATION_INSTALL_SUBSCRIPTION = 'application.install.subscription';
	const METHOD_APPLICATION_UNINSTALL = 'application.uninstall';
	const METHOD_APPLICATION_STAT = 'application.stat';
	const METHOD_APPLICATION_LIST = 'application.list';
	const METHOD_APPLICATION_USAGE = 'application.usage.add';

	const METHOD_APPLICATION_VERSION_UPDATE = 'application.version.update';
	const METHOD_APPLICATION_VERSION_DELETE = 'application.version.delete';

	const METHOD_REST_AUTHORIZE = 'rest.authorize';
	const METHOD_REST_CHECK = 'rest.check';
	const METHOD_REST_CODE = 'rest.code';
	const METHOD_REST_EVENT_CALL = 'rest.event.call';

	const HTTP_SOCKET_TIMEOUT = 10;
	const HTTP_STREAM_TIMEOUT = 10;

	protected $clientId;
	protected $clientSecret;
	protected $licenseKey;

	public function __construct($clientId, $clientSecret, $licenseKey)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->licenseKey = $licenseKey;
	}

	protected function prepareRequestData($additionalParams)
	{
		if(!is_array($additionalParams))
		{
			$additionalParams = array();
		}
		else
		{
			$additionalParams = Encoding::convertEncoding($additionalParams, LANG_CHARSET, "utf-8");
		}

		return $additionalParams;
	}

	protected function prepareRequest($additionalParams, $licenseCheck = false)
	{
		$additionalParams = $this->prepareRequestData($additionalParams);

		$additionalParams['client_id'] = $this->clientId;
		$additionalParams['client_secret'] = $this->clientSecret;
		$additionalParams['client_redirect_uri'] = OAuthService::getRedirectUri();
		$additionalParams['member_id'] = \CRestUtil::getMemberId();

		if($licenseCheck)
		{
			$additionalParams = \CRestUtil::signLicenseRequest($additionalParams, $this->licenseKey);
		}

		return $additionalParams;
	}

	protected function prepareResponse($result)
	{
		try
		{
			return Json::decode($result);
		}
		catch(ArgumentException $e)
		{
			return false;
		}
	}

	protected function getHttpClient()
	{
		return new HttpClient(array(
			'socketTimeout' => static::HTTP_SOCKET_TIMEOUT,
			'streamTimeout' => static::HTTP_STREAM_TIMEOUT,
		));
	}

	protected function getRequestUrl($methodName)
	{
		return static::SERVICE_URL.static::SERVICE_PATH.$methodName;
	}

	/**
	 * Low-level function for REST method call. Returns method response.
	 *
	 * @param string $methodName Method name.
	 * @param array|null $additionalParams Method params.
	 * @param bool|false $licenseCheck Send license key in request (will be sent automatically on verification_needed error).
	 *
	 * @return bool|mixed
	 *
	 * @throws SystemException
	 */
	public function call($methodName, $additionalParams = null, $licenseCheck = false)
	{
		if($this->clientId && $this->clientSecret)
		{
			$additionalParams = $this->prepareRequest($additionalParams, $licenseCheck);

			$httpClient = $this->getHttpClient();
			$httpResult = $httpClient->post(
				$this->getRequestUrl($methodName),
				$additionalParams
			);

			$response = $this->prepareResponse($httpResult);

			if($response)
			{
				if(!$licenseCheck && is_array($response) && isset($response['error']) && $response['error'] === 'verification_needed')
				{
					return $this->call($methodName, $additionalParams, true);
				}
			}
			else
			{
				addMessage2Log('Strange answer from Bitrix Service! '.static::SERVICE_URL.static::SERVICE_PATH.$methodName.": ".$httpClient->getStatus().' '.$httpResult);
			}

			return $response;
		}
		else
		{
			throw new SystemException("No client credentials");
		}
	}

	public function batch($actions)
	{
		$batch = array();

		if(is_array($actions))
		{
			foreach($actions as $queryKey => $cmdData)
			{
				list($cmd, $cmdParams) = array_values($cmdData);
				$batch['cmd'][$queryKey] = $cmd.(is_array($cmdParams) ? '?'.http_build_query($this->prepareRequestData($cmdParams)) : '');
			}
		}

		return $this->call(static::METHOD_BATCH, $batch);
	}

	public function addApplication(array $applicationSettings)
	{
		return $this->call(static::METHOD_APPLICATION_ADD, array(
			"TITLE" => $applicationSettings["TITLE"],
			"REDIRECT_URI" => $applicationSettings["REDIRECT_URI"],
			"SCOPE" => $applicationSettings["SCOPE"],
		));
	}

	public function updateApplication(array $applicationSettings)
	{
		return $this->call(static::METHOD_APPLICATION_UPDATE, array(
			"CLIENT_ID" => $applicationSettings["CLIENT_ID"],
			"TITLE" => $applicationSettings["TITLE"],
			"REDIRECT_URI" => $applicationSettings["REDIRECT_URI"],
			"SCOPE" => $applicationSettings["SCOPE"],
		));
	}

	public function deleteApplication(array $applicationSettings)
	{
		return $this->call(static::METHOD_APPLICATION_DELETE, array(
			"CLIENT_ID" => $applicationSettings["CLIENT_ID"],
		));
	}

	public function installApplication(array $applicationSettings)
	{
		$queryFields = array(
			"CLIENT_ID" => $applicationSettings["CLIENT_ID"],
			"VERSION" => $applicationSettings["VERSION"],
		);

		if(isset($applicationSettings["CHECK_HASH"]) && isset($applicationSettings["INSTALL_HASH"]))
		{
			$queryFields['CHECK_HASH'] = $applicationSettings["CHECK_HASH"];
			$queryFields['INSTALL_HASH'] = $applicationSettings["INSTALL_HASH"];
		}

		if ($applicationSettings['BY_SUBSCRIPTION'] === 'Y')
		{
			$method = static::METHOD_APPLICATION_INSTALL_SUBSCRIPTION;
		}
		else
		{
			$method = static::METHOD_APPLICATION_INSTALL;
		}

		return $this->call($method, $queryFields);
	}

	public function unInstallApplication(array $applicationSettings)
	{
		return $this->call(static::METHOD_APPLICATION_UNINSTALL, array(
			"CLIENT_ID" => $applicationSettings["CLIENT_ID"],
		));
	}

	public function getAuth($clientId, $scope, array $additionalParams = array())
	{
		return $this->call(static::METHOD_REST_AUTHORIZE, array(
			"CLIENT_ID" => $clientId,
			"SCOPE" => $scope,
			"PARAMS" => $additionalParams,
		));
	}

	public function checkAuth($accessToken)
	{
		return $this->call(static::METHOD_REST_CHECK, array(
			"TOKEN" => $accessToken,
		));
	}

	public function getCode($clientId, $state, $additionalParams)
	{
		return $this->call(static::METHOD_REST_CODE, array(
			"CLIENT_ID" => $clientId,
			"STATE" => $state,
			"PARAMS" => $additionalParams,
		));
	}

	public function getApplicationList()
	{
		return $this->call(static::METHOD_APPLICATION_LIST);
	}

	public function sendApplicationUsage(array $usage)
	{
		return $this->call(static::METHOD_APPLICATION_USAGE, array(
			"USAGE" => $usage,
		));
	}

	public function sendEvent(array $eventItems)
	{
		return $this->call(static::METHOD_REST_EVENT_CALL, array(
			"QUERY" => $eventItems,
		));
	}
}