<?php
namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\MessageService;
use Bitrix\MessageService\Internal\Entity\RestAppLangTable;
use Bitrix\MessageService\Internal\Entity\RestAppTable;
use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Sender\Result;
use Bitrix\Rest\Sqs;

Loc::loadMessages(__FILE__);

class Rest extends Sender\Base
{
	public const ID = 'rest';

	public static $langFields;

	public static function isSupported(): bool
	{
		return ModuleManager::isModuleInstalled('rest');
	}

	public function getId(): string
	{
		return static::ID;
	}

	public function getName(): ?string
	{
		return Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_NAME');
	}

	public function getShortName(): string
	{
		return 'REST';
	}

	public function canUse(): bool
	{
		if (Loader::includeModule('rest') && \Bitrix\Rest\OAuthService::getEngine()->isRegistered())
		{
			$firstRecord = RestAppTable::getList([
				'select' => ['ID'],
				'limit' => 1,
				'cache' => [
					'ttl' => 3600,
				],
			])->fetchObject();

			return $firstRecord !== null;
		}

		return false;
	}

	public function getFromList(): array
	{
		$list = [];
		if (!$this->canUse())
		{
			return $list;
		}

		$result = RestAppTable::getList();
		while ($row = $result->fetch())
		{
			$list[] = [
				'id' => $row['APP_ID'].'|'.$row['CODE'],
				'name' => sprintf('[%s] %s',
					$this->getLangField($row['ID'], 'APP_NAME'),
					$this->getLangField($row['ID'], 'NAME')
				),
				'description' => $this->getLangField($row['ID'], 'DESCRIPTION')
			];
		}
		return $list;
	}

	public function isCorrectFrom($from): bool
	{
		[$appId, $code] = explode('|', $from);
		$restSender = RestAppTable::getList(
			[
				'filter' => [
					'=APP_ID' => $appId,
					'=CODE' => $code
				],
				'select' => ['ID']
			]
		)->fetch();

		return $restSender !== false;
	}

	public function sendMessage(array $messageFields): Result\SendMessage
	{
		$sendResult = new Result\SendMessage();

		if (!$this->canUse())
		{
			$sendResult->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_ERROR_APP_NOT_FOUND')));
			return $sendResult;
		}

		[$appId, $code] = explode('|', $messageFields['MESSAGE_FROM']);
		$restSender = null;

		if ($appId && $code)
		{
			$restSender = RestAppTable::getList(
				['filter' =>
					[
						'=APP_ID' => $appId,
						'=CODE' => $code
					]
				]
			)->fetch();
		}

		if (!$restSender)
		{
			$sendResult->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_ERROR_APP_NOT_FOUND')));
			return $sendResult;
		}
		/*
		$session = \Bitrix\Rest\Event\Session::get();
		if(!$session)
		{
			$sendResult->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_ERROR_SESSION')));
			return $sendResult;
		}
		*/
		$dbRes = \Bitrix\Rest\AppTable::getList([
			'filter' => [
				'=CLIENT_ID' => $restSender['APP_ID'],
			]
		]);
		$application = $dbRes->fetch();

		if (!$application)
		{
			$sendResult->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_ERROR_APP_NOT_FOUND')));
			return $sendResult;
		}

		$appStatus = \Bitrix\Rest\AppTable::getAppStatusInfo($application, '');
		if($appStatus['PAYMENT_ALLOW'] === 'N')
		{
			$sendResult->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_REST_ERROR_PAYMENT_ALLOW')));
			return $sendResult;
		}

		$auth = $messageFields['AUTHOR_ID'] > 0 ? [
			'CODE' => $restSender['CODE'],
			\Bitrix\Rest\Event\Session::PARAM_SESSION => \Bitrix\Rest\Event\Session::get(),
			\Bitrix\Rest\OAuth\Auth::PARAM_LOCAL_USER => $messageFields['AUTHOR_ID'],
			"application_token" => \CRestUtil::getApplicationToken($application),
		] : [];

		$messageId = $messageFields['EXTERNAL_ID'] ?? 0;
		if (!$messageId)
		{
			$messageId = md5(($messageFields['ID'] ?? 0).'|'.uniqid());
			$this->setExternalMessageId((int)$messageFields['ID'], $messageId);
		}

		$restData = is_array($messageFields['MESSAGE_HEADERS']) ? $messageFields['MESSAGE_HEADERS'] : array();
		//compatible parameters
		$restData['properties'] = [
			'phone_number' => $messageFields['MESSAGE_TO'],
			'message_text' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
		];
		$restData['type'] = $restSender['TYPE'];
		$restData['code'] = $restSender['CODE'];
		$restData['message_id'] = $messageId;
		$restData['message_to'] = $messageFields['MESSAGE_TO'];
		$restData['message_body'] = $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']);
		$restData['ts'] = time();

		$queryItems = [
			Sqs::queryItem(
				$restSender['APP_ID'],
				$restSender['HANDLER'],
				$restData,
				$auth,
				[
					"sendAuth" => (bool)$auth,
					"sendRefreshToken" => false,
					"category" => Sqs::CATEGORY_DEFAULT,
				]
			),
		];

		\Bitrix\Rest\OAuthService::getEngine()->getClient()->sendEvent($queryItems);
		$sendResult->setExternalId($messageId);
		$sendResult->setStatus(MessageService\MessageStatus::SENT);

		if ($application['CODE'])
		{
			AddEventToStatFile(
				'messageservice',
				'sendRest' . $restSender['TYPE'],
				uniqid($application['CODE'], true),
				$application['CODE']
			);
		}

		if (is_callable(['\Bitrix\Rest\UsageStatTable', 'logMessage']))
		{
			\Bitrix\Rest\UsageStatTable::logMessage($application['CLIENT_ID'], $restSender['TYPE']);
			\Bitrix\Rest\UsageStatTable::finalize();
		}

		return $sendResult;
	}

	public static function resolveStatus($serviceStatus): int
	{
		$status = parent::resolveStatus($serviceStatus);

		switch ((string)$serviceStatus)
		{
			case 'queued':
				return MessageService\MessageStatus::QUEUED;
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

	private function getLangField($appId, $fieldName)
	{
		$fields = $this->getAppLangFields($appId);
		if (!$fields)
		{
			return '';
		}

		$fieldLangs = $fields[$fieldName];
		if (isset($fieldLangs[LANGUAGE_ID]))
		{
			return $fieldLangs[LANGUAGE_ID];
		}
		else
		{
			$defaultLang = Loc::getDefaultLang(LANGUAGE_ID);
			if (isset($fieldLangs[$defaultLang]))
			{
				return $fieldLangs[$defaultLang];
			}
		}
		return $fieldLangs['**'];
	}

	private function getAppLangFields($appId)
	{
		if (static::$langFields === null)
		{
			$orm = RestAppLangTable::getList();
			while ($row = $orm->fetch())
			{
				static::$langFields[$row['APP_ID']]['NAME'][$row['LANGUAGE_ID']] = $row['NAME'];
				static::$langFields[$row['APP_ID']]['APP_NAME'][$row['LANGUAGE_ID']] = $row['APP_NAME'];
				static::$langFields[$row['APP_ID']]['DESCRIPTION'][$row['LANGUAGE_ID']] = $row['DESCRIPTION'];
			}
		}

		return static::$langFields[$appId] ?? null;
	}

	private function setExternalMessageId(int $internalId, string $externalId): void
	{
		MessageService\Internal\Entity\MessageTable::update($internalId, [
			'EXTERNAL_ID' => $externalId
		]);
	}
}