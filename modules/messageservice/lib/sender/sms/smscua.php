<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;

use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

use Bitrix\MessageService;

class SmscUa extends Sender\BaseConfigurable
{
	public const ID = 'smscua';

	private const JSON_API_URL = 'https://smsc.ua/sys/';

	public static function isSupported()
	{
		return (
			ModuleManager::isModuleInstalled('b24network')
			|| Option::get('messageservice', 'smscua_enabled', 'N') === 'Y'
		);
	}

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'smsc.ua';
	}

	public function getShortName()
	{
		return 'smsc.ua';
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');

		return is_array($from) ? $from : [];
	}

	public function isRegistered()
	{
		return (
			$this->getOption('login') !== null
			&&
			$this->getOption('psw') !== null
		);
	}

	public function register(array $fields)
	{
		$login = (string)$fields['login'];
		$psw = (string)$fields['psw'];

		$result = $this->sendApiRequest(
			'balance',
			[
				'login' => $login,
				'psw' => $psw,
			]
		);

		if ($result->isSuccess())
		{
			$this->setOption('login', $login);
			$this->setOption('psw', $psw);
		}

		return $result;
	}

	/**
	 * @return array [
	 *    'login' => ''
	 * ]
	 */
	public function getOwnerInfo()
	{
		return [
			'login' => $this->getOption('login'),
		];
	}

	public function getExternalManageUrl()
	{
		return 'https://smsc.ua/login/';
	}

	public function sendMessage(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$message = [
			'sender' => $messageFields['MESSAGE_FROM'],
			'phones' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'mes' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
			'charset' => 'utf-8'
		];

		$result = new SendMessage();
		$apiResult = $this->sendApiRequest('send', $message);
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$smsData = $apiResult->getData();
			$smsId = $smsData['id'];

			if (!$smsId)
			{
				$result->addError(new Error('Service error.'));
			}
			else
			{
				$result->setExternalId($smsId);
				$result->setAccepted();
			}
		}

		return $result;
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['EXTERNAL_ID']);

		if (!$this->canUse())
		{
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$apiResult = $this->sendApiRequest('status', [
			'id' => $result->getExternalId(),
			'phone' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'charset' => 'utf-8',
		]);

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$smsData = $apiResult->getData();

			if (!$smsData)
			{
				$result->addError(new Error('Service error.'));
			}

			$result->setStatusText($smsData['status']);
			$result->setStatusCode(self::resolveStatus($smsData['status']));
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{

		switch ((string)$serviceStatus)
		{
			case '-3':
			case '-2':

				return MessageService\MessageStatus::ERROR;
				break;

			case '-1':
			case '0':

				return MessageService\MessageStatus::QUEUED;
				break;

			case '1':

				return MessageService\MessageStatus::DELIVERED;
				break;

			case '2':
			case '4':

				return MessageService\MessageStatus::READ;
				break;

			case '3':

				return MessageService\MessageStatus::UNDELIVERED;
				break;

			default:
				return MessageService\MessageStatus::FAILED;
				break;
		}

		return parent::resolveStatus($serviceStatus);
	}

	public function sync()
	{
		if ($this->isRegistered())
		{
			$this->loadFromList();
		}

		return $this;
	}

	private function loadFromList()
	{
		$result = $this->sendApiRequest('senders', ['get' => 1]);

		if ($result->isSuccess())
		{
			$from = [];
			$resultData = $result->getData();
			foreach ($resultData as $sender)
			{
				$from[] = [
					'id' => $sender['sender'],
					'name' => $sender['sender'],
				];
			}

			$this->setOption('from_list', $from);
		}
	}

	private function sendApiRequest($path, array $params)
	{
		if (!isset($params['login']))
		{
			$params['login'] = $this->getOption('login');
		}
		if (!isset($params['psw']))
		{
			$params['psw'] = $this->getOption('psw');
		}

		$method = $path === 'balance' ? HttpClient::HTTP_GET : HttpClient::HTTP_POST;

		return $this->sendHttpRequest($method, $path, $params);
	}

	private function sendHttpRequest($method, $path, array $params): Sender\Result\HttpRequestResult
	{
		$httpClient = new HttpClient([
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			"waitResponse" => true,
		]);
		$httpClient->setCharset('UTF-8');
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-Type', 'application/json');

		$result = new Sender\Result\HttpRequestResult();
		$answer = ['error' => 'Service error'];

		$url = self::JSON_API_URL.$path.'.php';

		$params['fmt'] = 3;

		//if ($method === HttpClient::HTTP_GET)
		{
			$url .= '?'.http_build_query($params);
			$params = null;
		}

		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => $method,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params,
		]));
		if ($httpClient->query($method, $url, $params))
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			} catch (\Bitrix\Main\ArgumentException $e)
			{
			}
		}
		$result->setHttpResponse(new MessageService\DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		if (isset($answer['error']))
		{
			$result->addError(new Error($answer['error'], $answer['error_code']));
		}

		$result->setData($answer);

		return $result;
	}
}
