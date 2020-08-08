<?php


namespace Bitrix\Sale\Rest\Synchronization;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\OAuthService;
use Bitrix\Sale\Result;

class Client
{
	protected $clientId;
	protected $clientSecret;
	protected $serviceUrl;
	protected $refreshToken;

	const HTTP_SOCKET_TIMEOUT = 10;
	const HTTP_STREAM_TIMEOUT = 10;
	const SERVICE_PATH = "/rest/";
	const B24_APP_GRANT_TYPE = 'refresh_token';

	public function __construct($clientId, $clientSecret, $serviceUrl)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->serviceUrl = $serviceUrl;
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
		return $this->serviceUrl.self::SERVICE_PATH.$methodName;
	}

	public function call($methodName, $additionalParams=[])
	{
		$result = new Result();

		if($this->clientId && $this->clientSecret)
		{
			$httpClient = $this->getHttpClient();

			$additionalParams = $this->prepareRequest($additionalParams);

			LoggerDiag::addMessage('CLIENT_CALL_REQUEST', var_export([
				'getRequestUrl'=>$this->getRequestUrl($methodName),
				'additionalParams'=>$additionalParams,
			], true));

			$httpResult = $httpClient->post(
				$this->getRequestUrl($methodName),
				$additionalParams
			);

			LoggerDiag::addMessage('CLIENT_CALL_PROCESS_RESULT', var_export([
				'result'=>$httpResult,
				'status'=>$httpClient->getStatus()
			], true));

			$respons = $this->prepareResponse($httpResult);

			if($respons)
			{
				LoggerDiag::addMessage('CLIENT_CALL_PROCESS_RESULT_SUCCESS');

				if(isset($respons['error']))
				{
					$result->addError(new Error($respons['error_description'], mb_strtoupper($respons['error'])));
					LoggerDiag::addMessage('CLIENT_CALL_RESULT_ERROR');
				}
				else
				{
					$result->setData(['DATA'=>$respons]);
					LoggerDiag::addMessage('CLIENT_CALL_RESULT_SUCCESS', var_export($respons, true));
				}
			}
			else
			{
				$result->addError(new Error('Strange answer from Bitrix Service! '.$httpResult, 'STRANGE_ANSWER'));
				LoggerDiag::addMessage('CLIENT_CALL_PROCESS_RESULT_ERROR');
			}
		}
		else
		{
			$result->addError(new Error('No client credentials for refresh token'));
			LoggerDiag::addMessage('CLIENT_CALL_CLIENT_ID_EMPTY');
		}

		return $result;
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

	protected function prepareRequest($params)
	{
		if(!is_array($params))
		{
			$params = array();
		}
		else
		{
			$params = Encoding::convertEncoding($params, LANG_CHARSET, "utf-8");
		}

		return $params;
	}

	public function refreshToken($refreshToken)
	{
		$result = new Result();

		if($refreshToken=='')
		{
			$result->addError(new Error('Refresh token is empty'));
			LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_EMPTY');
		}

		if(!$this->clientId || !$this->clientSecret)
		{
			$result->addError(new Error('No client credentials for refresh token'));
			LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_CLIENT_ID_EMPTY');
		}

		if($result->isSuccess())
		{
			$request = OAuthService::SERVICE_URL.'/oauth/token/'.'?'.http_build_query(
					[
						'grant_type'=>self::B24_APP_GRANT_TYPE,
						'client_id'=>$this->clientId,
						'client_secret'=>$this->clientSecret,
						'refresh_token'=>$refreshToken
					]);

			LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_REQUEST', var_export($request,true));

			$httpClient = $this->getHttpClient();
			$httpResult = $httpClient->get($request);

			LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_PROCESS_RESULT', var_export([
				'result'=>$httpResult,
				'status'=>$httpClient->getStatus()
			], true));

			$respons = $this->prepareResponse($httpResult);
			if($respons)
			{
				LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_PROCESS_RESULT_SUCCESS');

				if(isset($respons['error']))
				{
					$result->addError(new Error($respons['error_description'], mb_strtoupper($respons['error'])));
					LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_RESULT_ERROR');
				}
				else
				{
					$result->setData(['DATA'=>$respons]);
					LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_RESULT_SUCCESS', var_export($respons, true));
				}
			}
			else
			{
				$result->addError(new Error('Strange answer from Bitrix Service! ', 'STRANGE_ANSWER_REFRESH_TOKEN'));
				LoggerDiag::addMessage('CLIENT_REFRESH_TOKEN_PROCESS_RESULT_ERROR');
			}
		}

		return $result;
	}

	public function checkAccessToken($accessToken)
	{
		$result = new Result();

		if(!Loader::includeModule('rest'))
			$result->addError(new Error('Module REST is not included'));

		if($result->isSuccess())
		{
			if(!\Bitrix\Rest\OAuthService::getEngine()->isRegistered())
			{
				try
				{
					\Bitrix\Rest\OAuthService::register();
				}
				catch(\Bitrix\Main\SystemException $e)
				{
					$result->addError(new Error('OAuthServiceError', '	OAUTH_SERVICE_ERROR'));
				}
			}

			if($result->isSuccess())
			{
				$client = \Bitrix\Rest\OAuthService::getEngine()->getClient();
				$respons = $client->call('app.info', ['auth' => $accessToken]);
				if(isset($respons['error']))
					$result->addError(new Error($respons['error_description'], mb_strtoupper($respons['error'])));
			}
		}
		return $result;
	}
}