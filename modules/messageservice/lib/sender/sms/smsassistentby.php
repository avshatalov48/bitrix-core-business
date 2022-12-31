<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;

use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

use Bitrix\MessageService;

Loc::loadMessages(__FILE__);

class SmsAssistentBy extends Sender\BaseConfigurable
{
	use Sender\Traits\RussianProvider;

	public const ID = 'smsastby';

	const JSON_API_URL = 'https://userarea.sms-assistent.by/api/v1/json';
	const PLAIN_API_URL = 'https://userarea.sms-assistent.by/api/v1/%s/plain';

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_NAME');
	}

	public function getShortName()
	{
		return 'sms-assistent.by';
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');
		return is_array($from) ? $from : [];
	}

	public function isRegistered()
	{
		return (
			$this->getOption('user') !== null
			&&
			$this->getOption('password') !== null
		);
	}

	public function register(array $fields)
	{
		$params = array(
			'user' => $fields['account_user'],
			'password' => $fields['account_password'],
		);

		$result = $this->callPlainApi('credits', $params);
		if ($result->isSuccess())
		{
			$this->disableDemo();
			$this->setOption('user', $params['user']);
			$this->setOption('password', $params['password']);
		}

		return $result;
	}

	public function registerDemo(array $fields)
	{
		$params = [
			'info' => [
				'simple' => true,
				'user_uuid' => 'wsTKC5KdazmEjBNU8i5fqacddtOeqQBtRc45lcvO6WA=',
				"diler_uuid" => 'obUrbkWuyW0nYL9D2rR6s3EPMm1QHg9h/KWtO8jFIug=',
				'email' => $fields['email'],
				'tel' => '+'.\NormalizePhone($fields['tel']),
				'unp' => $fields['unp']
			]
		];

		$result = $this->callJsonApi('demo', $params);

		if ($result->isSuccess())
		{
			$this->enableDemo();
			$this->setOption('demo_email', $fields['email']);
			$this->setOption('demo_tel', '+'.\NormalizePhone($fields['tel']));
			$this->setOption('demo_unp', $fields['unp']);
		}

		return $result;
	}

	/**
	 * @return array [
	 * 	'user' => ''
	 * ]
	 */
	public function getOwnerInfo()
	{
		return array(
			'user' => $this->getOption('user'),
		);
	}

	/**
	 * @return array
	 */
	public function getDemoInfo()
	{
		return array(
			'email' => $this->getOption('demo_email'),
			'tel' => $this->getOption('demo_tel'),
			'unp' => $this->getOption('demo_unp'),
		);
	}

	public function getExternalManageUrl()
	{
		return 'https://userarea.sms-assistent.by/';
	}

	public function sendMessage(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_CAN_USE_ERROR')));
			return $result;
		}

		$message = [
			'recipient' => $messageFields['MESSAGE_TO'],
			'message' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
			'validity_period' => 24,
			'webhook_url' => $this->getCallbackUrl()
		];

		if (ModuleManager::isModuleInstalled('bitrix24'))
		{
			$message['Vendor'] = 'Bitrix24';
		}

		if ($messageFields['MESSAGE_FROM'])
		{
			$message['sender'] = $messageFields['MESSAGE_FROM'];
		}

		$result = new SendMessage();
		$apiResult = $this->callPlainApi('send_sms', $message);
		$result->setServiceRequest($apiResult->getHttpRequest());
		$result->setServiceResponse($apiResult->getHttpResponse());
		$resultData = $apiResult->getData();

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$smsId = $resultData['response'];

			if ($smsId <= 0)
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

	/* reserved method */
	private function sendMessageBulk(array $messageFields)
	{
		if (!$this->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_CAN_USE_ERROR')));
			return $result;
		}

		$message = [
			'recepient' => $messageFields['MESSAGE_TO'],
			'sms_text' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
		];

		if ($messageFields['MESSAGE_FROM'])
		{
			$message['sender'] = $messageFields['MESSAGE_FROM'];
		}

		$result = new SendMessage();
		$apiResult = $this->callJsonApi('sms_send', [
			'message' => [
				'default' => [
					'validity_period' => 24
				],
				'msg' => [
					$message
				]
			]
		]);
		$resultData = $apiResult->getData();

		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$smsData = current($resultData['message']['msg']);

			if (!$smsData)
			{
				$result->addError(new Error($this->getErrorMessage(-1000)));
			}
			else
			{
				if (isset($smsData['sms_id']))
				{
					$result->setExternalId($smsData['sms_id']);
				}

				if ($smsData['error_code'] > 0)
				{
					$result->addError(new Error($this->getErrorMessage($smsData['error_code'])));
				}
				else
				{
					$result->setAccepted();
				}
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
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_CAN_USE_ERROR')));
			return $result;
		}

		$params = array(
			'status' => [
				'msg' => [
					[
						'sms_id' => $result->getExternalId()
					]
				]
			]
		);

		$apiResult = $this->callJsonApi('statuses', $params);
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$smsData = current($resultData['status']['msg']);

			if (!$smsData)
			{
				$result->addError(new Error($this->getErrorMessage(-1000)));
			}

			$result->setStatusText($smsData['sms_status']);
			$result->setStatusCode(self::resolveStatus($smsData['sms_status']));
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ($serviceStatus)
		{
			case 'Queued':
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 'Sent':
				return MessageService\MessageStatus::SENT;
				break;
			case 'Delivered':
				return MessageService\MessageStatus::DELIVERED;
				break;
			case 'Expired':
			case 'Rejected':
			case 'Unknown':
			case 'Failed':
				return MessageService\MessageStatus::UNDELIVERED;
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

	private function callPlainApi($command, array $params = []): Sender\Result\HttpRequestResult
	{
		$url = sprintf(self::PLAIN_API_URL, $command);

		$httpClient = new HttpClient(array(
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setCharset('UTF-8');

		$isUtf = Application::getInstance()->isUtfMode();

		if (!isset($params['user']))
		{
			$params['user'] = $this->getOption('user');
		}

		if (!isset($params['password']))
		{
			$params['password'] = $this->getOption('password');
		}

		if (!$isUtf)
		{
			$params = \Bitrix\Main\Text\Encoding::convertEncoding($params, SITE_CHARSET, 'UTF-8');
		}

		$result = new Sender\Result\HttpRequestResult();
		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => HttpClient::HTTP_POST,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params,
		]));
		$answer = [];

		if ($httpClient->query(HttpClient::HTTP_POST, $url, $params) && $httpClient->getStatus() == '200')
		{
			$answer = $httpClient->getResult();
		}

		if (is_numeric($answer) && $answer < 0)
		{
			$result->addError(new Error($this->getErrorMessage($answer)));
		}
		$result->setHttpResponse(new MessageService\DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
			'error' => Sender\Util::getHttpClientErrorString($httpClient)
		]));

		$result->setData(['response' => $answer]);

		return $result;
	}

	private function callJsonApi($command, array $params = []): Sender\Result\HttpRequestResult
	{
		$httpClient = new HttpClient(array(
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setCharset('UTF-8');
		$httpClient->setHeader('Content-Type', 'application/json');

		if (!isset($params['login']) && $this->isRegistered())
		{
			$params['login'] = $this->getOption('user');
		}

		if (!isset($params['password']) && $this->isRegistered())
		{
			$params['password'] = $this->getOption('password');
		}

		$params['command'] = $command;

		$params = Json::encode($params);

		$result = new Sender\Result\HttpRequestResult();
		$result->setHttpRequest(new MessageService\DTO\Request([
			'method' => HttpClient::HTTP_POST,
			'uri' => self::JSON_API_URL,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $params,
		]));
		$answer = [];

		if ($httpClient->query(HttpClient::HTTP_POST, self::JSON_API_URL, $params) && $httpClient->getStatus() == '200')
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			}
			catch (\Bitrix\Main\ArgumentException $e)
			{
				$answer = ['error' => -1000];
			}
		}

		if (isset($answer['error']))
		{
			$result->addError(new Error($this->getErrorMessage($answer['error'], $answer)));
		}

		if (isset($answer['status']) && $answer['status'] === -1)
		{
			$msg = !empty($answer['message']) ? $answer['message'] : $this->getErrorMessage($answer['error'], 1000);
			$result->addError(new Error($msg));
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

	private function getErrorMessage($errorCode)
	{
		$errorCode = abs($errorCode);
		$message = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_ERROR_'.$errorCode);
		return $message ?: Loc::getMessage('MESSAGESERVICE_SENDER_SMS_SMSASTBY_ERROR_OTHER');
	}

	private function loadFromList()
	{
		$result = $this->callJsonApi('get_senders');

		if ($result->isSuccess())
		{
			$from = array();
			$resultData = $result->getData();
			foreach ($resultData['senders'] as $sender)
			{
				if (!empty($sender))
				{
					$from[] = array(
						'id' => $sender,
						'name' => $sender
					);
				}
			}

			$this->setOption('from_list', $from);
		}
	}
}