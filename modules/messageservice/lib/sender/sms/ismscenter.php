<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;

use Bitrix\MessageService\DTO;
use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

use Bitrix\MessageService;

class ISmsCenter extends Sender\BaseConfigurable
{
	public const ID = 'ismscenter';

	private const JSON_API_URL = 'http://isms.center/api/sms/';

	public static function isSupported()
	{
		return (
			ModuleManager::isModuleInstalled('b24network')
			|| Option::get('messageservice', 'ismscenter_enabled', 'N') === 'Y'
		);
	}

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'isms.center';
	}

	public function getShortName()
	{
		return 'isms.center';
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
		$from = (string) $fields['from_list'];

		$result = new Result();

		if ($login && $psw && $from)
		{
			$this->setOption('login', $login);
			$this->setOption('psw', $psw);

			$from = array_map(
				function($v) {
					$v = trim($v);

					return ['id' => $v, 'name' => $v];
				},
				explode(';', $from)
			);

			$this->setOption('from_list', $from);
		}
		else
		{
			$result->addError(new Error('Empty required fields.'));
		}

		return $result;
	}

	/**
	 * @return array [
	 *    'login' => ''
	 *    'fromList' => []
	 * ]
	 */
	public function getOwnerInfo()
	{
		return [
			'login' => $this->getOption('login'),
			'fromList' => $this->getFromList(),
		];
	}

	public function getExternalManageUrl()
	{
		return 'https://isms.center/ru';
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
			'from' => $messageFields['MESSAGE_FROM'],
			'to' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'text' => $messageFields['MESSAGE_BODY'],
			'notify_url' => $this->getCallbackUrl()
		];

		$result = new SendMessage();
		$apiResult = $this->sendApiRequest('send', $message);

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$smsData = $apiResult->getData();
			$smsId = $smsData['message_id'];

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

		$apiResult = $this->sendApiRequest('report', [
			'message_id' => $result->getExternalId(),
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
			case 'send':
				return MessageService\MessageStatus::QUEUED;
				break;
			case 'sending':
				return MessageService\MessageStatus::SENDING;
				break;
			case 'sent':
				return MessageService\MessageStatus::SENT;
				break;
			case 'delivered':
				return MessageService\MessageStatus::DELIVERED;
				break;
			case 'undelivered':
				return MessageService\MessageStatus::UNDELIVERED;
				break;
		}

		return parent::resolveStatus($serviceStatus);
	}

	private function sendApiRequest($path, array $params)
	{
		$login = $this->getOption('login');
		$psw = $this->getOption('psw');

		return $this->sendHttpRequest($path, $login, $psw, $params);
	}

	private function sendHttpRequest($path, $login, $psw, array $params): Sender\Result\HttpRequestResult
	{
		$httpClient = new HttpClient([
			'socketTimeout' => 10,
			'streamTimeout' => 30,
			'waitResponse' => true,
		]);
		$httpClient->setCharset('UTF-8');
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-Type', 'application/json');
		$httpClient->setAuthorization($login, $psw);

		$result = new Sender\Result\HttpRequestResult();
		$answer = [
			'error_code' => 500,
			'error_message' => 'Service error'
		];

		$method = HttpClient::HTTP_POST;
		$url = self::JSON_API_URL . $path;
		$body = Json::encode($params);

		if ($path === 'report')
		{
			$method = HttpClient::HTTP_GET;

			$url .= '?' . http_build_query($params);
			$body = null;
		}

		$result->setHttpRequest(new DTO\Request([
			'method' => $method,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params
		]));

		if ($httpClient->query($method, $url, $body))
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			} catch (\Bitrix\Main\ArgumentException $e)
			{
			}
		}

		if (isset($answer['error_code']))
		{
			$result->addError(new Error($answer['error_message'], $answer['error_code']));
		}

		$result->setHttpResponse(new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		$result->setData($answer);

		return $result;
	}
}
