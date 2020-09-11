<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

use Bitrix\MessageService;
use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

Loc::loadMessages(__FILE__);

class Twilio extends Sender\BaseConfigurable
{
	public function getId()
	{
		return 'twilio';
	}

	public function getName()
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_NAME');
	}

	public function getShortName()
	{
		return 'twilio.com';
	}

	public function isRegistered()
	{
		return ($this->getOption('account_sid') !== null);
	}

	public function isDemo()
	{
		return false;
	}

	public function canUse()
	{
		return $this->isRegistered();
	}

	public function getFromList()
	{
		$from = $this->getOption('from_list');
		return is_array($from) ? $from : array();
	}

	public function register(array $fields)
	{
		$sid = (string)$fields['account_sid'];
		$token = (string)$fields['account_token'];

		$result = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid, array(), $sid, $token
		);
		if ($result->isSuccess())
		{
			$data = $result->getData();

			if ($data['status'] !== 'active')
			{
				$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_ERROR_ACCOUNT_INACTIVE')));
			}
			else
			{
				$this->setOption('account_sid', $sid);
				$this->setOption('account_token', $token);
				$this->setOption('account_friendly_name', $data['friendly_name']);
			}
		}

		return $result;
	}

	public function getOwnerInfo()
	{
		return array(
			'sid' => $this->getOption('account_sid'),
			'friendly_name' => $this->getOption('account_friendly_name')
		);
	}

	public function getExternalManageUrl()
	{
		return 'https://www.twilio.com/console';
	}

	public function sendMessage(array $messageFields)
	{
		$sid = $this->getOption('account_sid');

		if (!$sid)
		{
			$result = new SendMessage();
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_CAN_USE_ERROR')));
			return $result;
		}

		$params = array(
			'To' => $messageFields['MESSAGE_TO'],
			'Body' => $messageFields['MESSAGE_BODY'],
			'From' => $messageFields['MESSAGE_FROM'],
			'StatusCallback' => $this->getCallbackUrl()
		);

		if (!$params['From'])
		{
			$params['From'] = $this->getDefaultFrom();
		}

		if (is_string($params['From']) && mb_strlen($params['From']) === 34) //unique id of the Messaging Service
		{
			$params['MessagingServiceSid'] = $params['From'];
			unset($params['From']);
		}

		$result = new SendMessage();
		$apiResult = $this->callExternalMethod(
			HttpClient::HTTP_POST,
			'Accounts/'.$sid.'/Messages/',
			$params
		);
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			if (isset($resultData['sid']))
			{
				$result->setExternalId($resultData['sid']);
			}
			$result->setAccepted();
		}

		return $result;
	}

	public function getMessageStatus(array $messageFields)
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['EXTERNAL_ID']);

		$sid = $this->getOption('account_sid');
		if (!$sid)
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_CAN_USE_ERROR')));
			return $result;
		}

		$apiResult = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid.'/Messages/'.$result->getExternalId()
		);
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$resultData = $apiResult->getData();
			$result->setStatusCode($resultData['status']);
			$result->setStatusText($resultData['status']);
			if (in_array($resultData['status'],
				array('accepted', 'queued', 'sending', 'sent', 'delivered', 'undelivered', 'failed')))
			{
				$result->setStatusText(
					Loc::getMessage('MESSAGESERVICE_SENDER_SMS_TWILIO_MESSAGE_STATUS_'.mb_strtoupper($resultData['status']))
				);
			}
		}

		return $result;
	}

	public static function resolveStatus($serviceStatus)
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ((string)$serviceStatus)
		{
			case 'accepted':
				return MessageService\MessageStatus::ACCEPTED;
				break;
			case 'queued':
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
			case 'failed':
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

	private function callExternalMethod($httpMethod, $apiMethod, array $params = array(), $sid = null, $token = null)
	{
		$url = 'https://api.twilio.com/2010-04-01/'.$apiMethod.'.json';

		$httpClient = new HttpClient(array(
			"socketTimeout" => 10,
			"streamTimeout" => 30,
			"waitResponse" => true,
		));
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setCharset('UTF-8');

		if (!$sid || !$token)
		{
			$sid = $this->getOption('account_sid');
			$token = $this->getOption('account_token');
		}

		$httpClient->setAuthorization($sid, $token);

		$isUtf = Application::getInstance()->isUtfMode();

		if (!$isUtf)
		{
			$params = \Bitrix\Main\Text\Encoding::convertEncoding($params, SITE_CHARSET, 'UTF-8');
		}

		$result = new Result();
		$answer = array();

		if ($httpClient->query($httpMethod, $url, $params))
		{
			try
			{
				$answer = Json::decode($httpClient->getResult());
			}
			catch (ArgumentException $e)
			{
				$result->addError(new Error('Service error'));
			}

			$httpStatus = $httpClient->getStatus();
			if ($httpStatus >= 400)
			{
				if (isset($answer['message']) && isset($answer['code']))
				{
					$result->addError(new Error($answer['message'], $answer['code']));
				}
				else
				{
					$result->addError(new Error('Service error (HTTP Status '.$httpStatus.')'));
				}
			}
		}

		if ($result->isSuccess())
		{
			$result->setData($answer);
		}

		return $result;
	}

	private function loadFromList()
	{
		$sid = $this->getOption('account_sid');
		$result = $this->callExternalMethod(
			HttpClient::HTTP_GET,
			'Accounts/'.$sid.'/IncomingPhoneNumbers'
		);

		if ($result->isSuccess())
		{
			$from = array();
			$resultData = $result->getData();
			if (isset($resultData['incoming_phone_numbers']) && is_array($resultData['incoming_phone_numbers']))
			{
				foreach ($resultData['incoming_phone_numbers'] as $phoneNumber)
				{
					if ($phoneNumber['capabilities']['sms'] === true)
					{
						$from[] = array(
							'id' => $phoneNumber['phone_number'],
							'name' => $phoneNumber['friendly_name']
						);
					}
				}
			}

			$this->setOption('from_list', $from);
		}
	}
}