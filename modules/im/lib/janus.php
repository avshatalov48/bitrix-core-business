<?php

namespace Bitrix\Im;

use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Error;

class Janus
{
	const PLUGIN_VIDEOROOM = 'janus.plugin.videoroom';
	const USER_AGENT = 'Bitrix IM';

	protected static $serverAddress = 'https://testphone.bitrix.info:8089/janus';
	protected static $apiSecret = 'eaff35df4cf027ffb36300d9b9604d8f';

	//protected static $serverAddress = 'https://cp.perevozov.bx:8089/janus';
	//protected static $apiSecret = 'bitrix';

	protected $transaction = null;
	protected $sessionId = '';
	protected $pluginEndpoints = array();

	public function __construct()
	{
		$this->transaction = uniqid();
	}

	/**
	 * @param array $config
	 * @return string|false Returns room id or false in case of failure
	 */
	public function createRoom(array $config = array())
	{
		$request = array(
			'janus' => 'message',
			'body' => array(
				'request' => 'create',
				'description' => ($config['description'] ?: 'Nameless room'),
				'publishers' => ($config['publishers'] ?: 2)
			)
		);

		if(isset($config['bitrate']))
		{
			$request['body']['bitrate'] = $config['bitrate'];
		}
		$result = $this->query($request, self::PLUGIN_VIDEOROOM);
		if(!$result->isSuccess())
			return $result;
		
		$response = $result->getData();
		if($response['plugindata']['data']['videoroom'] === 'created')
			return $response['plugindata']['data']['room'];
		else
			return false;
	}

	/**
	 * @return Result
	 */
	public function login()
	{
		$request = array(
			'janus' => 'create',
		);

		$result = $this->query($request);
		if(!$result->isSuccess())
		{
			return $result;
		}
		$response = $result->getData();

		if($response['data']['id'])
			$this->sessionId = (string)$response['data']['id'];
		else
			$result->addError(new Error('Session id is not found in gateway response'));

		return $result;
	}

	/**
	 * @return bool
	 */
	protected function isLogged()
	{
		return ($this->sessionId != '');
	}

	/**
	 * @param string $pluginName
	 * @return Result
	 */
	public function attachToPlugin($pluginName)
	{
		$request = array(
			'janus' => 'attach',
			'plugin' => $pluginName,
		);
		$result = $this->query($request);

		if(!$result->isSuccess())
			return $result;

		$response = $result->getData();

		if($response['data']['id'])
			$this->pluginEndpoints[$pluginName] = (string)$response['data']['id'];
		else
			$result->addError(new Error('Session id is not found in gateway response'));

		return $result;
	}

	/**
	 * @param $pluginName
	 * @return bool
	 */
	protected function isAttachedToPlugin($pluginName)
	{
		return isset($this->pluginEndpoints[$pluginName]);
	}

	/**
	 * @param array $request
	 * @param string $pluginName
	 * @return Result
	 */
	protected function query(array $request, $pluginName = '')
	{
		$request['transaction'] = $this->transaction;
		$request['apisecret'] = self::$apiSecret;

		$endpoint = self::$serverAddress;
		if($this->sessionId)
		{
			$endpoint = $endpoint . '/' .$this->sessionId;
		}

		if($pluginName)
		{
			if(!$this->isAttachedToPlugin($pluginName))
			{
				$attachResult = $this->attachToPlugin($pluginName);
				if(!$attachResult->isSuccess())
				{
					return $attachResult;
				}
			}
			$endpoint = $endpoint . '/'  . $this->pluginEndpoints[$pluginName];
		}

		$result = new Result();
		$encodedRequest = Json::encode($request);

		$httpClient = new HttpClient(array(
			"socketTimeout" => 5,
			"streamTimeout" => 5,
			"disableSslVerification" => true
		));

		$httpClient->setHeader('User-Agent', self::USER_AGENT, true);
		$httpClient->query('POST', $endpoint, $encodedRequest);

		if($httpClient->getStatus() !== 200)
		{
			$result->addError(new Error('Error connecting to Janus Media Gateway'));
			return $result;
		}

		$response = $httpClient->getResult();

		$decodedResponse = json_decode($response, true, 512, JSON_BIGINT_AS_STRING);
		if(!is_array($decodedResponse))
		{
			$result->addError(new Error('Error decoding gateway response'));
			return $result;
		}

		$result->setData($decodedResponse);
		return $result;
	}

	/**
	 * @return string
	 */
	public static function getServerAddress()
	{
		return self::$serverAddress;
	}

	/**
	 * @return string
	 */
	public static function getApiSecret()
	{
		return self::$apiSecret;
	}
}