<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

use Bitrix\MessageService;

Loc::loadMessages(__FILE__);

class SmsLineBy extends Sender\BaseConfigurable
{
	public const ID = 'smslineby';

	const JSON_API_URL = 'https://api.smsline.by/v3/';

	public static function isSupported()
	{
		return ModuleManager::isModuleInstalled('b24network');
	}

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'SMS-line';
	}

	public function getShortName()
	{
		return 'smsline.by';
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
			$this->getOption('api_key') !== null
		);
	}

	public function register(array $fields)
	{
		$login = (string) $fields['login'];
		$key = (string) $fields['api_key'];

		$result = $this->sendGetRequest('balance/packets', $this->makeSignature('balancepackets', $key), $login);
		if ($result->isSuccess())
		{
			$this->setOption('login', $login);
			$this->setOption('api_key', $key);
		}

		return $result;
	}

	/**
	 * @return array [
	 * 	'login' => ''
	 * ]
	 */
	public function getOwnerInfo()
	{
		return array(
			'login' => $this->getOption('login'),
		);
	}

	public function getExternalManageUrl()
	{
		return 'https://mobilemarketing.by/login/';
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
			'target' => $messageFields['MESSAGE_FROM'],
			'msisdn' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'text' => $messageFields['MESSAGE_BODY'],
			'callback_url' => $this->getCallbackUrl()
		];

		$result = new SendMessage();
		$apiResult = $this->sendPostRequest('messages/single/sms', $message);
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$smsData = $resultData['message'];
			$smsId = $smsData['id_message'];

			if (!$smsId)
			{
				$result->addError(new Error($this->getErrorMessage(-1000)));
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

		$apiResult = $this->sendGetRequest('messages/'.$result->getExternalId());
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$smsData = $resultData['message'];

			if (!$smsData)
			{
				$result->addError(new Error($this->getErrorMessage(-1000)));
			}

			$result->setStatusText($smsData['state']['name']);
			$result->setStatusCode(self::resolveStatus($smsData['state']['state']));
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ($serviceStatus)
		{
			case 'seen':
				return MessageService\MessageStatus::READ;
				break;
			case 'delivered':
				return MessageService\MessageStatus::DELIVERED;
				break;
			case 'accepted':
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 'enrouted':
				return MessageService\MessageStatus::SENT;
				break;
			case 'undeliverable':
			case 'expired':
			case 'deleted':
				return MessageService\MessageStatus::UNDELIVERED;
				break;
			case 'reject':
			case 'notsent':
			case 'textblacklist':
			case 'noviber':
			case 'blocked':
			case 'unknown':
			case 'nostatus':
				return MessageService\MessageStatus::FAILED;
				break;
		}

		return $status;
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
		$result = $this->sendGetRequest('balance/packets');

		if ($result->isSuccess())
		{
			$from = [];
			$resultData = $result->getData();
			foreach ($resultData['packets'] as $packet)
			{
				if (isset($packet['targets']) && is_array($packet['targets']))
				{
					foreach ($packet['targets'] as $target)
					{
						$from[] = array(
							'id' => $target,
							'name' => $target
						);
					}
				}
			}

			$this->setOption('from_list', $from);
		}
	}

	private function makeSignature($text, $key = null)
	{
		if (!$key)
		{
			$key = $this->getOption('api_key');
		}

		return hash_hmac("sha256", $text, $key);
	}

	private function sendGetRequest($path, $signature = null, $login = null)
	{
		if (!$signature)
		{
			$signature = $this->makeSignature(str_replace('/', '', $path));
		}

		if (!$login)
		{
			$login = $this->getOption('login');
		}

		return $this->sendHttpRequest(HttpClient::HTTP_GET, $login, $signature, $path);
	}

	private function sendPostRequest($path, array $params)
	{
		$login = $this->getOption('login');
		$requestBody = Json::encode($params);
		$signature = $this->makeSignature(str_replace('/', '', $path).$requestBody);

		return $this->sendHttpRequest(HttpClient::HTTP_POST, $login, $signature, $path, $requestBody);
	}


	private function sendHttpRequest($method, $login, $signature, $path, $body = null): Sender\Result\HttpRequestResult
	{
		$httpClient = new HttpClient(array(
			"socketTimeout" => 10,
			"streamTimeout" => 30,
			"waitResponse" => true,
		));
		$httpClient->setCharset('UTF-8');
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-Type', 'application/json');
		$httpClient->setHeader('Authorization-User', $login);
		$httpClient->setHeader('Authorization', "Bearer $signature");

		$result = new Sender\Result\HttpRequestResult();
		$answer = ['error' => -1000];

		$url = self::JSON_API_URL.$path;

		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => $method,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $body,
		]));
		if ($httpClient->query($method, $url, $body))
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			}
			catch (\Bitrix\Main\ArgumentException $e)
			{
			}
		}

		if (isset($answer['error']))
		{
			$result->addError(new Error($this->getErrorMessage($answer['error']['code'], $answer['error']['message'])));
		}

		$result->setHttpResponse(new MessageService\DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		$result->setData($answer);

		return $result;
	}

	private function getErrorMessage($errorCode, $text = null)
	{
		$message = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSLINE_ERROR_'.$errorCode, null, 'ru');
		return $message ?: ($text ?: Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSLINE_ERROR_OTHER',null, 'ru'));
	}
}