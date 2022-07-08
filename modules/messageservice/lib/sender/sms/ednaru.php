<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\ImConnector\Library;
use Bitrix\ImOpenLines\Im;
use Bitrix\ImOpenLines\Session;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\Sender;
use Bitrix\MessageService\Internal\Entity\MessageTable;

class Ednaru extends Sender\BaseConfigurable
{
	use Sender\Traits\RussianProvider;

	public const ID = 'ednaru';

	private const API_ENDPOINT = 'https://im.edna.ru/api/';
	private const SENDER_ID_OPTION = 'sender_id';
	private const API_KEY_OPTION = 'api_key';
	private const EMOJI_DECODE = 'decode';
	private const EMOJI_ENCODE = 'encode';

	public function isAvailable(): bool
	{
		return self::isSupported();
	}

	public function getId(): string
	{
		return static::ID;
	}

	public function getName(): string
	{
		return 'Edna.ru WhatsApp';
	}

	public function getShortName(): string
	{
		return 'Edna.ru WhatsApp';
	}

	public function isRegistered(): bool
	{
		return
			!is_null($this->getOption(self::API_KEY_OPTION))
			&& !is_null($this->getOption(self::SENDER_ID_OPTION));
	}

	public function register(array $fields): Result
	{
		$result = new Result();

		if (isset($fields['subject_id']))
		{
			$fields[self::SENDER_ID_OPTION] = $fields['subject_id'];
		}

		if (!isset($fields[self::API_KEY_OPTION], $fields[self::SENDER_ID_OPTION]))
		{
			$result->addError(
				new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_EMPTY_REQUIRED_FIELDS'))
			);

			return $result;
		}

		$this->setOption(self::API_KEY_OPTION, (string)$fields[self::API_KEY_OPTION]);

		$senderIds = [];
		foreach (explode(';', (string)$fields[self::SENDER_ID_OPTION]) as $senderId)
		{
			$senderId = trim($senderId);
			if ($senderId !== '')
			{
				$senderIds[] = $senderId;
			}
		}
		$this->setOption(self::SENDER_ID_OPTION, $senderIds);

		return $result;
	}

	public function getOwnerInfo(): array
	{
		return [
			self::API_KEY_OPTION => $this->getOption(self::API_KEY_OPTION),
			self::SENDER_ID_OPTION => $this->getOption(self::SENDER_ID_OPTION),
		];
	}

	public function getExternalManageUrl(): string
	{
		return 'https://im.edna.ru/';
	}

	public function getMessageStatus(array $messageFields): Sender\Result\MessageStatus
	{
		$result = new Sender\Result\MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['ID']);

		if (!$this->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_USE_ERROR')));
			return $result;
		}

		$apiResult = $this->callExternalMethod("imOutMessage/{$messageFields['ID']}");
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$apiData = $apiResult->getData();

			$result->setStatusText($apiData['dlvStatus']);
			$result->setStatusCode(static::resolveStatus($apiData['dlvStatus']));
		}

		return $result;
	}

	public function sendMessage(array $messageFields): Sender\Result\SendMessage
	{
		if (!$this->canUse())
		{
			$result = new Sender\Result\SendMessage();
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$requestParams = $this->getSendMessageParams($messageFields);
		$method = $this->getSendMessageMethod($messageFields);

		if ($method === 'imOutHSM')
		{
			$this->sendHSMtoChat($messageFields);
		}

		$result = new Sender\Result\SendMessage();
		$requestResult = $this->callExternalMethod($method, $requestParams);
		if (!$requestResult->isSuccess())
		{
			$result->addErrors($requestResult->getErrors());

			return $result;
		}

		$apiData = $requestResult->getData();
		$result->setExternalId($apiData['id']);
		$result->setAccepted();

		return $result;
	}

	public function testConnection(): Result
	{
		$result = new Result();
		if (!$this->canUse())
		{
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$requestParams = ['imType' => 'WHATSAPP'];

		return $this->callExternalMethod('im-subject/by-apikey', $requestParams);
	}

	protected function callExternalMethod(string $method, ?array $requestParams = null): Sender\Result\HttpRequestResult
	{
		$url = static::API_ENDPOINT . $method;
		$queryMethod = HttpClient::HTTP_GET;

		$httpClient = new HttpClient([
			'socketTimeout' => 10,
			'streamTimeout' => 30,
			'waitResponse' => true,
		]);
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-type', 'application/json');
		$httpClient->setHeader('X-API-KEY', $this->getOption(self::API_KEY_OPTION));
		$httpClient->setCharset('UTF-8');

		if (isset($requestParams))
		{
			$queryMethod = HttpClient::HTTP_POST;
			$requestParams = Json::encode($this->convertRequestParams($requestParams));
		}

		$result = new Sender\Result\HttpRequestResult();
		$result->setHttpRequest(new DTO\Request([
			'method' => $queryMethod,
			'uri' => $url,
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $requestParams
		]));
		if ($httpClient->query($queryMethod, $url, $requestParams))
		{
			$response = $this->parseExternalResponse($httpClient->getResult());
		}
		else
		{
			$result->setHttpResponse(new DTO\Response([
				'error' => Sender\Util::getHttpClientErrorString($httpClient)
			]));
			$error = $httpClient->getError();
			$response = ['code' => current($error)];
		}

		$result->setHttpResponse(new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
		]));

		if (!$this->checkResponse($response))
		{
			$errorMessage = $this->getErrorMessageByCode($response['code']);
			$result->addError(new Error($errorMessage));

			return $result;
		}
		$result->setData($response);

		return $result;
	}

	protected function parseExternalResponse(string $httpResult): array
	{
		try
		{
			return Json::decode($httpResult);
		}
		catch (ArgumentException $exception)
		{
			return ['code' => 'error-json-parsing'];
		}
	}

	public function getFromList(): array
	{
		$fromList = [];
		foreach ($this->getOption(self::SENDER_ID_OPTION, []) as $subject)
		{
			$fromList[] = [
				'id' => $subject,
				'name' => $subject,
			];
		}
		return $fromList;
	}

	public static function resolveStatus($serviceStatus): ?int
	{
		switch ($serviceStatus)
		{
			case 'read':
			case 'sent':
				return MessageStatus::SENT;
			case 'enqueued':
				return MessageStatus::QUEUED;
			case 'delayed':
				return MessageStatus::ACCEPTED;
			case 'delivered':
				return MessageStatus::DELIVERED;
			case 'undelivered':
				return MessageStatus::UNDELIVERED;
			case 'failed':
			case 'cancelled':
			case 'expired':
			case 'no-match-template':
				return MessageStatus::FAILED;
			default:
				return mb_strpos($serviceStatus, 'error') === 0 ? MessageStatus::ERROR : MessageStatus::UNKNOWN;
		}
	}

	public function getLineId(): ?int
	{
		if (!Loader::includeModule('imconnector'))
		{
			return null;
		}

		$statuses = \Bitrix\ImConnector\Status::getInstanceAllLine(Library::ID_EDNA_WHATSAPP_CONNECTOR);
		foreach ($statuses as $status)
		{
			if ($status->isConfigured())
			{
				return (int)$status->getLine();
			}
		}

		return null;
	}

	public function getCallbackUrl(): string
	{
		return parent::getCallbackUrl();
	}

	/**
	 * @inheritDoc
	 */
	public function isTemplatesBased(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getTemplatesList(array $context = null): array
	{
		$result = [];
		$templates = $this->getMessageTemplates()->getData()['result'];
		if (!is_array($templates))
		{
			return $result;
		}
		foreach ($templates as $template)
		{
			$result[] = [
				'ID' => Json::encode($template['content']),
				'TITLE' => $template['name'],
				'PREVIEW' => $template['content']['text'],
			];
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function prepareTemplate($templateData): array
	{
		try
		{
			$messageTemplateDecoded = Json::decode($templateData);
			$messageTemplateDecoded = $this->convertEmojiInTemplate($messageTemplateDecoded, self::EMOJI_ENCODE);
		}
		catch (\Bitrix\Main\ArgumentException $e)
		{
			throw new ArgumentException('Incorrect message template');
		}

		return $messageTemplateDecoded;
	}

	/**
	 * Returns a list of HSM templates for the account. If subject is defined, returns templates only for the subject.
	 * https://edna.docs.apiary.io/#reference/api/getoutmessagematchers
	 *
	 * @param string $subject Subject to filter templates.
	 *
	 * @return Result
	 */
	public function getMessageTemplates(string $subject = ''): Result
	{
		if (defined('WA_EDNA_RU_TEMPLATES_STUB') && WA_EDNA_RU_TEMPLATES_STUB === true)
		{
			return $this->getMessageTemplatesStub();
		}

		$params = ['imType' => 'whatsapp'];
		if ($subject !== '')
		{
			$params['subject'] = $subject;
		}

		$templatesRequestResult = $this->callExternalMethod('getOutMessageMatchers', $params);

		return $this->removeUnsupportedTemplates($templatesRequestResult);
	}

	/**
	 * Checks if message is HSM template by message fields.
	 * We consider that it is template by mandatory text field.
	 * https://edna.docs.apiary.io/#reference/api/imouthsm
	 *
	 * @param array $messageFields Message fields.
	 *
	 * @return bool
	 */
	private function isTemplateMessage(array $messageFields): bool
	{
		if (isset($messageFields['MESSAGE_HEADERS']['template']['text']))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns request params for sending template or simple message.
	 * @param array $messageFields Message fields.
	 *
	 * @return array
	 */
	private function getSendMessageParams(array $messageFields): array
	{
		$messageFields['MESSAGE_BODY'] = $this->convertEmoji($messageFields['MESSAGE_BODY'], self::EMOJI_DECODE);
		$params = [
			'id' => uniqid('', true),
			'subject' => $messageFields['MESSAGE_FROM'],
			'address' => str_replace('+', '', $messageFields['MESSAGE_TO']),
			'contentType' => 'text',
			'text' => $messageFields['MESSAGE_BODY'],
		];

		if ($this->isTemplateMessage($messageFields))
		{
			$params['imType'] = 'whatsapp';
			$params['text'] = $messageFields['MESSAGE_HEADERS']['template']['text'];

			$templateFields = ['header', 'footer', 'keyboard'];

			foreach ($templateFields as $templateField)
			{
				if (
					isset($messageFields['MESSAGE_HEADERS']['template'][$templateField])
					&& count($messageFields['MESSAGE_HEADERS']['template'][$templateField]) > 0
				)
				{
					$params[$templateField] = $messageFields['MESSAGE_HEADERS']['template'][$templateField];
				}
			}

			$params = $this->convertEmojiInTemplate($params, self::EMOJI_DECODE);
		}

		return $params;
	}

	/**
	 * Returns method for sending template or simple message.
	 *
	 * @param array $messageFields Message fields.
	 *
	 * @return string
	 */
	private function getSendMessageMethod(array $messageFields): string
	{
		$method = 'imOutMessage';
		if ($this->isTemplateMessage($messageFields))
		{
			$method = 'imOutHSM';
		}

		return $method;
	}

	private function sendHSMtoChat(array $messageFields): void
	{
		if (!Loader::includeModule('imopenlines') || !Loader::includeModule('imconnector'))
		{
			return;
		}

		$externalChatId = str_replace('+', '', $messageFields['MESSAGE_TO']);
		$userId = $this->getImconnectorUserId($externalChatId);
		if (!$userId)
		{
			return;
		}

		$from = $messageFields['MESSAGE_FROM'];
		$lineId = $this->getLineId();
		$userSessionCode = $this->getSessionUserCode($lineId, $externalChatId, $from, $userId);
		$chatId = $this->getOpenedSessionChatId($userSessionCode);
		if (!$chatId)
		{
			return;
		}

		Im::addMessage([
			'TO_CHAT_ID' => $chatId,
			'MESSAGE' => $this->prepareTemplateMessageText($messageFields),
			'SYSTEM' => 'Y',
			'SKIP_COMMAND' => 'Y',
			'NO_SESSION_OL' => 'Y',
			'PARAMS' => [
				'CLASS' => 'bx-messenger-content-item-ol-output'
			],
		]);
	}

	/**
	 * Returns stub with HSM template from docs:
	 * https://edna.docs.apiary.io/#reference/api/getoutmessagematchers
	 *
	 * @return Result
	 */
	private function getMessageTemplatesStub(): Result
	{
		$result = new Result();
		$result->setData([
			'result' => [
				[
					'id' => 206,
					'name' => 'test template',
					'imType' => 'whatsapp',
					'language' => 'AU',
					'content' => [
						'header' => [],
						'text' => 'whatsapp text',
						'footer' => [
							'text' => 'footer text'
						],
						'keyboard' => [
							'row' => [
								'buttons' => [
									[
										'text' => 'button1',
										'payload' => 'button1',
										'buttonType' => 'QUICK_REPLY'
									]
								]
							]
						]
					],
					'category' => 'ISSUE_UPDATE',
					'status' => 'PENDING',
					'createdAt' => '2020-11-12T11:31:39.000+0000',
					'updatedAt' => '2020-11-12T11:31:39.000+0000'
				],
				[
					'id' => 207,
					'name' => 'one more template',
					'imType' => 'whatsapp',
					'language' => 'AU',
					'content' => [
						'header' => [],
						'text' => 'one more template',
						'footer' => [
							'text' => 'footer text'
						],
						'keyboard' => [
							'row' => [
								'buttons' => [
									[
										'text' => 'button1',
										'payload' => 'button1',
										'buttonType' => 'QUICK_REPLY'
									]
								]
							]
						]
					],
					'category' => 'ISSUE_UPDATE',
					'status' => 'PENDING',
					'createdAt' => '2020-11-12T11:31:39.000+0000',
					'updatedAt' => '2020-11-12T11:31:39.000+0000'
				]
			],
			'code' => 'ok'
		]);

		return $result;
	}

	public function getManageUrl(): string
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true)
		{
			return parent::getManageUrl();
		}

		if (!Loader::includeModule('imopenlines') || !Loader::includeModule('imconnector'))
		{
			return '';
		}

		$contactCenterUrl = \Bitrix\ImOpenLines\Common::getContactCenterPublicFolder();

		return $contactCenterUrl . 'connector/?ID=' . Library::ID_EDNA_WHATSAPP_CONNECTOR;
	}

	private function convertRequestParams(array $requestParams): array
	{
		if (!Application::isUtfMode())
		{
			$requestParams = Encoding::convertEncoding($requestParams, SITE_CHARSET, 'UTF-8');
		}

		return $requestParams;
	}

	private function checkResponse(array $response): bool
	{
		if (
			(isset($response['code']) && $response['code'] === 'ok')
			|| !isset($response['code']) // Success response without "code" parameter https://edna.docs.apiary.io/#reference/api/by-apikey
		)
		{
			return true;
		}

		return false;
	}

	public function getSentTemplateMessage(string $from, string $to): string
	{
		$message = MessageTable::getList([
			'select' => ['ID', 'MESSAGE_HEADERS'],
			'filter' => [
				'=SENDER_ID' => $this->getId(),
				'=MESSAGE_FROM' => $from,
				'=MESSAGE_TO' => '+' . $to,
			],
			'limit' => 1,
			'order' => ['ID' => 'DESC'],
		])->fetch();

		if (!$message)
		{
			return '';
		}

		return $this->prepareTemplateMessageText($message);
	}

	private function prepareTemplateMessageText(array $message): string
	{
		$latestMessage = '';
		if (isset($message['MESSAGE_HEADERS']['template']['header']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['header']['text'] . '#BR#';
		}

		if (isset($message['MESSAGE_HEADERS']['template']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['text'] . '#BR#';
		}

		if (isset($message['MESSAGE_HEADERS']['template']['footer']['text']))
		{
			$latestMessage .= $message['MESSAGE_HEADERS']['template']['footer']['text'];
		}

		return $latestMessage;
	}

	private function getImconnectorUserId(string $externalChatId): ?string
	{
		$userXmlId = Library::ID_EDNA_WHATSAPP_CONNECTOR . '|' . $externalChatId;
		$user = \Bitrix\Main\UserTable::getRow([
			'select' => ['ID'],
			'filter' => ['=XML_ID' => $userXmlId],
		]);

		return $user ? $user['ID'] : null;
	}

	private function getOpenedSessionChatId(string $userSessionCode): ?string
	{
		$session = new Session();
		$sessionLoadResult = $session->getLast(['USER_CODE' => $userSessionCode]);
		if (!$sessionLoadResult->isSuccess())
		{
			return null;
		}
		$sessionData = $session->getData();
		$chatId = $sessionData['CHAT_ID'];
		$closed = $sessionData['CLOSED'] === 'Y';
		if ($closed)
		{
			return null;
		}

		return $chatId;
	}

	private function getSessionUserCode(string $lineId, string $externalChatId, string $from, string $userId): string
	{
		return Library::ID_EDNA_WHATSAPP_CONNECTOR. '|'. $lineId. '|'. $externalChatId. '@'. $from. '|' . $userId;
	}

	private function removeUnsupportedTemplates(Result $templates): Result
	{
		if (!$templates->isSuccess())
		{
			return $templates;
		}

		$templatesData = $templates->getData();
		if (!$templatesData['result'])
		{
			return $templates;
		}

		$filteredTemplates = [];
		foreach ($templatesData['result'] as $template)
		{
			if ($this->checkForPlaceholders($template))
			{
				continue;
			}

			$filteredTemplates[] = $template;
		}

		$templatesData['result'] = $filteredTemplates;
		$result = new Result();
		$result->setData($templatesData);

		return $result;
	}

	private function checkForPlaceholders($template): bool
	{
		if (
			$this->hasPlaceholder($template['content']['header']['text'] ?? '')
			|| $this->hasPlaceholder($template['content']['text'] ?? '')
			|| $this->hasPlaceholder($template['content']['footer']['text'] ?? '')
		)
		{
			return true;
		}

		return false;
	}

	private function hasPlaceholder(string $text): bool
	{
		$placeholder = '{{1}}';

		return strpos($text, $placeholder) !== false;
	}

	/**
	 * Mapping from the docs https://edna.docs.apiary.io/#reference/0
	 *
	 * @param string $errorCode
	 *
	 * @return string
	 */
	private function getErrorMessageByCode(string $errorCode): string
	{
		$errorCode = mb_strtoupper($errorCode);
		$errorCode = str_replace("-", "_", $errorCode);

		$errorMessage = Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_'.$errorCode);

		return $errorMessage ? : Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_UNKNOWN_ERROR');
	}

	/**
	 * Converts message body text. Encodes emoji in the text, if there are any emoji.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	public function prepareMessageBodyForSave(string $text): string
	{
		return $this->convertEmoji($text, self::EMOJI_ENCODE);
	}

	private function convertEmoji(string $text, string $type): string
	{
		if (!in_array($type, ['decode', 'encode'], true))
		{
			return $text;
		}

		return Emoji::$type($text);
	}

	private function convertEmojiInTemplate(array $messageTemplate, string $type): array
	{
		$template = $messageTemplate;
		if (isset($template['text']))
		{
			$template['text'] = $this->convertEmoji($template['text'], $type);
		}
		if (isset($template['header']['text']))
		{
			$template['header']['text'] = $this->convertEmoji($template['header']['text'], $type);
		}
		if (isset($template['footer']['text']))
		{
			$template['footer']['text'] = $this->convertEmoji($template['footer']['text'], $type);
		}
		if (
			isset($template['keyboard']['row']['buttons'])
			&& is_array($template['keyboard']['row']['buttons'])
		)
		{
			foreach ($template['keyboard']['row']['buttons'] as $index => $button)
			{
				if (isset($button['text']))
				{
					$template['keyboard']['row']['buttons'][$index]['text'] = $this->convertEmoji($button['text'], $type);
				}
			}
		}

		return $template;
	}
}