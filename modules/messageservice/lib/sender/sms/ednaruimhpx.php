<?php

namespace Bitrix\MessageService\Sender\Sms;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\MessageService\DTO;
use Bitrix\MessageService\Message;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\Sender;

class EdnaruImHpx extends Sender\BaseConfigurable
{
	public const ID = 'ednaruimhpx';

	protected const MESSAGE_TYPE = "whatsapp";
	protected const DEFAULT_PRIORITY = "normal";
	protected const CONTENT_TEXT = "text";

	private const ENDPOINT_OPTION = 'connector_endpoint';
	private const LOGIN_OPTION = 'login';
	private const PASSWORD_OPTION = 'password';
	private const SUBJECT_OPTION = 'subject_id';

	public static function isSupported()
	{
		return defined('MESSAGESERIVICE_ALLOW_IMHPX') && MESSAGESERIVICE_ALLOW_IMHPX === true;
	}

	public function getId()
	{
		return static::ID;
	}

	public function getName()
	{
		return 'Edna.ru IMHPX';
	}

	public function getShortName()
	{
		return 'Edna.ru IMHPX';
	}

	public function isRegistered()
	{
		return defined('MESSAGESERIVICE_ALLOW_IMHPX') && MESSAGESERIVICE_ALLOW_IMHPX === true;
	}

	public function canUse()
	{
		return $this->isRegistered();
	}

	public function getFromList()
	{
		$id = $this->getOption(self::SUBJECT_OPTION);
		return [
			[
				'id' => $id,
				'name' => $id,
			],
		];
	}


	public function register(array $fields): Result
	{
		$this->setOption(static::ENDPOINT_OPTION, $fields['connector_endpoint']);
		$this->setOption(static::LOGIN_OPTION, $fields['login']);
		$this->setOption(static::PASSWORD_OPTION, $fields['password']);
		$this->setOption(static::SUBJECT_OPTION, $fields['subject_id']);

		return new Result();
	}

	public function getOwnerInfo()
	{
		return [
			self::ENDPOINT_OPTION => $this->getOption(self::ENDPOINT_OPTION),
			self::LOGIN_OPTION => $this->getOption(self::LOGIN_OPTION),
			self::PASSWORD_OPTION => $this->getOption(self::PASSWORD_OPTION),
			self::SUBJECT_OPTION => $this->getOption(self::SUBJECT_OPTION),
		];
	}

	public function getCallbackUrl(): string
	{
		return parent::getCallbackUrl();
	}

	public function getExternalManageUrl()
	{
		// TODO: Implement getExternalManageUrl() method.
	}

	public function sendMessage(array $messageFields): Sender\Result\SendMessage
	{
		$result = new Sender\Result\SendMessage();
		if (!$this->canUse())
		{
			return $result->addError(new Error('Service is unavailable'));
		}

		$body = $this->makeBodyOutgoingMessage($messageFields);

		$requestResult = $this->callExternalMethod($body);
		if (!$requestResult->isSuccess())
		{
			$result->addErrors($requestResult->getErrors());

			return $result;
		}

		$response = $requestResult->getHttpResponse();
		$this->processServiceResponse($response, $result);

		return $result;
	}

	public function getMessageStatus(array $messageFields)
	{
		// TODO: Implement getMessageStatus() method.
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

	protected function makeBodyOutgoingMessage(array $messageFields): string
	{
		$messageText = $messageFields['MESSAGE_BODY'];
		$messageText = htmlspecialcharsbx($messageText);

		$smsBlock = "<sms>
				<subject>Bitrix24</subject>
				<priority>{$this->getPriority()}</priority>
				<content>{$messageText}</content>
				<sendTimeoutSeconds>60</sendTimeoutSeconds>
				<validityPeriodMinutes>30</validityPeriodMinutes>
			</sms>";

		$address = static::normalizePhoneNumberForOutgoing($messageFields['MESSAGE_TO']);

		$ownerInfo = $this->getOwnerInfo();
		$login = $ownerInfo[static::LOGIN_OPTION];
		$password = $ownerInfo[static::PASSWORD_OPTION];
		$mailbox = $ownerInfo[static::SUBJECT_OPTION];

		$template = <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<consumeInstantMessageRequest>
	<header>
		<auth>
			<login>{$login}</login>
        	<password>{$password}</password>
		</auth>
	</header>
	<payload>
		<instantMessageList>
			<instantMessage clientId="{$messageFields['ID']}">
				<address>{$address}</address>
				<subject>{$mailbox}</subject>
				<priority>{$this->getPriority()}</priority>
				<instantMessageType>{$this->getMessageType()}</instantMessageType>
				<contentType>text</contentType>
				<content>
					<text>{$messageText}</text>
				</content>
				$smsBlock
			</instantMessage>
		</instantMessageList>
	</payload>
</consumeInstantMessageRequest>
XML;

		return $template;
	}

	protected function callExternalMethod(string $body): Sender\Result\HttpRequestResult
	{
		$httpClient = new HttpClient([
			"socketTimeout" => $this->socketTimeout,
			"streamTimeout" => $this->streamTimeout,
			'waitResponse' => true,
		]);
		$httpClient->setHeader('User-Agent', 'Bitrix24');
		$httpClient->setHeader('Content-type', 'text/xml');

		$result = new Sender\Result\HttpRequestResult();
		$result->setHttpRequest(new DTO\Request([
			'method' => HttpClient::HTTP_POST,
			'uri' => $this->getServiceEndpoint(),
			'headers' => method_exists($httpClient, 'getRequestHeaders') ? $httpClient->getRequestHeaders()->toArray() : [],
			'body' => $body
		]));

		$body = Encoding::convertEncoding($body, SITE_CHARSET, 'utf-8');
		if (!$httpClient->query(HttpClient::HTTP_POST, $this->getServiceEndpoint(), $body))
		{
			$result->setHttpResponse(new DTO\Response([
				'error' => Sender\Util::getHttpClientErrorString($httpClient)
			]));
			$httpError = $httpClient->getError();
			$errorCode = array_key_first($httpError);
			$result->addError(new Error($httpError[$errorCode], $errorCode));
			return $result;
		}

		$httpResponse = new DTO\Response([
			'statusCode' => $httpClient->getStatus(),
			'headers' => $httpClient->getHeaders()->toArray(),
			'body' => $httpClient->getResult(),
		]);
		$result->setHttpResponse($httpResponse);

		return $result;
	}

	/**
	 *
	 * Response body example:
	 * <?xml version="1.0" encoding="UTF-8" standalone="no"?>
	 * <consumeInstantMessageResponse>
	 *   <header/>
	 *   <payload>
	 *     <code>ok</code>
	 *     <instantMessageList>
	 *       <instantMessage clientId="9991144" providerId="87952036">
	 *         <code>ok</code>
	 *       </instantMessage>
	 *     </instantMessageList>
	 *   </payload>
	 * </consumeInstantMessageResponse>
	 *
	 * Where:
	 *  - clientId: our message id
	 *  - providerId: their message id
	 *  - code: status code ("ok" or one of the error codes, like "error-syntax", "error-auth", "error-permission", etc.)
	 *
	 * @param DTO\Response $response Response for our send request.
	 * @param Sender\Result\SendMessage $result Request to be filled.
	 */
	public function processServiceResponse(DTO\Response $response, Sender\Result\SendMessage $result): void
	{
		if ($response->statusCode !== 200)
		{
			$result->addError(new Error("Response status code is {$response->statusCode}", 'WRONG_SERVICE_RESPONSE_CODE'));
			return;
		}
		$parseResult = $this->parseXml($response->body);
		if (!$parseResult->isSuccess())
		{
			$result->addError(
				new Error(
					'XML parse error: ' . implode('; ', $parseResult->getErrorMessages()),
					'XML_PARSE_ERROR'
				)
			);
			return;
		}

		/** @var \SimpleXMLElement $instantMessageResponse */
		$instantMessageResponse = $parseResult->getData()['root'];

		// hack to convert SimpleXMLElement to array
		$instantMessageResponse = Json::decode(Json::encode($instantMessageResponse));

		// response structure
		if (
			!isset($instantMessageResponse['payload'])
			|| (!isset($instantMessageResponse['payload']['code'])
				&& !isset($instantMessageResponse['payload']['instantMessageList'])
			)
		)
		{
			$result->addError(new Error('Wrong xml response structure', 'SERVICE_RESPONSE_PARSE_ERROR'));
			return;
		}

		if ($instantMessageResponse['payload']['code'] !== 'ok')
		{
			$result->setStatus(\Bitrix\MessageService\MessageStatus::ERROR);
			$result->addError(new Error($instantMessageResponse['payload']['code'], $instantMessageResponse['payload']['code']));
			return;
		}

		foreach ($instantMessageResponse['payload']['instantMessageList'] as $instantMessage)
		{
			if ($instantMessage['code'] === 'ok' && isset($instantMessage['@attributes']['providerId']))
			{
				$result->setExternalId($instantMessage['@attributes']['providerId']);
				$result->setAccepted();
			}
			else
			{
				$result->setStatus(\Bitrix\MessageService\MessageStatus::ERROR);
				$result->addError(new Error('', $instantMessage['code']));
			}
			// we expect only one message response here
			return;
		}

		$result->addError(new Error('Could not find message status in response', 'SERVICE_RESPONSE_PARSE_ERROR'));
	}

	/**
	 * @param string $incomingRequestBody (see example)
	 * <?xml version="1.0" encoding="UTF-8" standalone="no"?>
	 *	<provideInstantMessageDlvStatusResponse>
	 *		<header/>
	 *		<payload>
	 *			<code>ok</code>
	 *			<instantMessageList>
	 *				<instantMessage providerId="6042">
	 *					<code>ok</code>
	 *					<instantMessageDlvStatus>
	 *						<dlvStatus>read</dlvStatus>
	 *						<dlvStatusAt>2021-03-24 10:53:55</dlvStatusAt>
	 *					</instantMessageDlvStatus>
	 *				</instantMessage>
	 *			</instantMessageList>
	 *		</payload>
	 *	</provideInstantMessageDlvStatusResponse>
	 *
	 * @return DTO\Response
	 */
	public function processIncomingRequest(string $incomingRequestBody): DTO\Response
	{
		$response = new DTO\Response([
			'statusCode' => 200
		]);
		$parseResult = $this->parseIncomingRequest($incomingRequestBody);
		if (!$parseResult->isSuccess())
		{
			$response->statusCode = 400;
			$response->body = 'Parse error';

			return $response;
		}

		/** @var DTO\StatusUpdate[] $statusUpdateList */
		$statusUpdateList = $parseResult->getData();
		foreach ($statusUpdateList as $statusUpdate)
		{
			$message = Message::loadByExternalId(static::ID, $statusUpdate->externalId);
			if ($message && $statusUpdate->providerStatus != '')
			{
				$message->updateStatusByExternalStatus($statusUpdate->providerStatus);
			}
		}

		return $response;
	}

	/**
	 * @param string $incomingRequestBody (see example)
	 * <?xml version="1.0" encoding="UTF-8" standalone="no"?>
	 *	<provideInstantMessageDlvStatusResponse>
	 *		<header/>
	 *		<payload>
	 *			<code>ok</code>
	 *			<instantMessageList>
	 *				<instantMessage providerId="6042">
	 *					<code>ok</code>
	 *					<instantMessageDlvStatus>
	 *						<dlvStatus>read</dlvStatus>
	 *						<dlvStatusAt>2021-03-24 10:53:55</dlvStatusAt>
	 *					</instantMessageDlvStatus>
	 *				</instantMessage>
	 *			</instantMessageList>
	 *		</payload>
	 *	</provideInstantMessageDlvStatusResponse>
	 *
	 * @return Result Result with []DTO\StatusUpdate
	 */
	public function parseIncomingRequest(string $incomingRequestBody): Result
	{
		$result = new Result();
		$parseResult = $this->parseXml($incomingRequestBody);
		if (!$parseResult->isSuccess())
		{
			return $result->addErrors($parseResult->getErrors());
		}

		/** @var \SimpleXMLElement $incomingRequest */
		$incomingRequest = $parseResult->getData()['root'];

		// incoming messages are not supported yet
		if ($incomingRequest->getName() != 'provideInstantMessageDlvStatusResponse')
		{
			return $result;
		}

		// hack to convert SimpleXMLElement to array
		$incomingRequest = Json::decode(Json::encode($incomingRequest));
		if (
			!isset($incomingRequest['payload'])
			|| (!isset($incomingRequest['payload']['code'])
				&& !isset($incomingRequest['payload']['instantMessageList'])
			)
		)
		{
			return $result->addError(new Error('Wrong XML structure'));
		}

		$statusUpdateList = [];

		// If response contains only one message delivery report - <instantMessage> element will contain the report
		// If response contains more than on message delivery report - <instantMessage> element will contain array of reports
		$instantMessageList = $incomingRequest['payload']['instantMessageList']['instantMessage'];
		if (!is_array($instantMessageList))
		{
			//empty list
			return $result->setData($statusUpdateList);
		}

		if (Collection::isAssociative($instantMessageList))
		{
			$instantMessageList = [$instantMessageList];
		}

		foreach ($instantMessageList as $instantMessage)
		{
			$statusUpdateList[] = new DTO\StatusUpdate([
				'externalId' => (int)$instantMessage['@attributes']['providerId'],
				'providerStatus' => $instantMessage['instantMessageDlvStatus']['dlvStatus'],
				'deliveryStatus' => static::resolveStatus($instantMessage['instantMessageDlvStatus']['dlvStatus']),
				'deliveryError' => $instantMessage['instantMessageDlvStatus']['dlvError']
			]);
		}

		return $result->setData($statusUpdateList);
	}

	/**
	 * @param string $xmlString XML document.
	 * @return Result Returns Result with the following keys (in the case of success)
	 *  - root {SimpleXMLElement} Root element of the XML document.
	 */
	protected function parseXml(string $xmlString): Result
	{
		$result = new Result();

		if ($xmlString === '')
		{
			return $result->addError(new Error('Empty XML'));
		}

		libxml_use_internal_errors(true);
		libxml_clear_errors();
		$parsedBody = simplexml_load_string($xmlString);
		$parseErrors = libxml_get_errors();

		if (!empty($parseErrors))
		{
			/** @var \LibXMLError $parseError */
			foreach ($parseErrors as $parseError)
			{
				$result->addError(new Error($parseError->message, $parseError->code));
			}
			return $result;
		}

		$result->setData([
			'root' => $parsedBody
		]);
		return $result;
	}

	protected function getPriority()
	{
		return $this::DEFAULT_PRIORITY;
	}

	protected function getMessageType()
	{
		return $this::MESSAGE_TYPE;
	}

	public function getServiceEndpoint(): string
	{
		return $this->getOption(static::ENDPOINT_OPTION);
	}

	public static function normalizePhoneNumberForOutgoing(string $phoneNumber): string
	{
		// remove +
		if (mb_strpos($phoneNumber, '+') === 0)
		{
			return mb_substr($phoneNumber, 1);
		}

		return $phoneNumber;
	}
}