<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp;

use Bitrix\ImConnector\Library;
use Bitrix\ImOpenLines\Im;
use Bitrix\ImOpenLines\Session;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Constants\InternalOption;

class Sender extends Providers\Edna\Sender
{
	protected Providers\OptionManager $optionManager;
	protected Providers\SupportChecker $supportChecker;
	protected Providers\Edna\EdnaRu $utils;
	protected EmojiConverter $emoji;
	protected ConnectorLine $connectorLine;

	public function __construct(
		Providers\OptionManager $optionManager,
		Providers\SupportChecker $supportChecker,
		Providers\Edna\EdnaRu $utils,
		EmojiConverter $emoji
	)
	{
		parent::__construct($optionManager, $supportChecker, $utils);
		$this->emoji = $emoji;

		$this->connectorLine = new ConnectorLine($this->utils);
	}

	protected function getSendMessageMethod(array $messageFields): string
	{
		return Providers\Edna\Constants\Method::SEND_MESSAGE;
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
		return $this->emoji->convertEmoji($text, InternalOption::EMOJI_ENCODE);
	}

	protected function getSendMessageParams(array $messageFields): Result
	{
		$cascadeResult = new Result();
		if (is_numeric($messageFields['MESSAGE_FROM']))
		{
			$cascadeResult = $this->utils->getCascadeIdFromSubject(
				(int)$messageFields['MESSAGE_FROM'],
				static function(array $externalSubjectData, int $internalSubject)
				{
					return $externalSubjectData['id'] === $internalSubject;
				}
			);
		}
		elseif (is_string($messageFields['MESSAGE_FROM']))
		{
			$cascadeResult = $this->utils->getCascadeIdFromSubject(
				$messageFields['MESSAGE_FROM'],
				static function(array $externalSubjectData, string $internalSubject)
				{
					return $externalSubjectData['subject'] === $internalSubject;
				}
			);
		}
		else
		{
			return $cascadeResult->addError(new Error('Invalid subject id'));
		}

		if (!$cascadeResult->isSuccess())
		{
			return $cascadeResult;
		}

		$messageFields['MESSAGE_BODY'] = $this->emoji->convertEmoji($messageFields['MESSAGE_BODY'], InternalOption::EMOJI_DECODE);

		$params = [
			'requestId' => uniqid('', true),
			'cascadeId' => $cascadeResult->getData()['cascadeId'],
			'subscriberFilter' => [
				'address' => str_replace('+', '', $messageFields['MESSAGE_TO']),
				'type' => 'PHONE',
			],
		];

		$params['content'] = $this->getMessageContent($messageFields);
		$result = new Result();
		$result->setData($params);

		return $result;
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
	public function isTemplateMessage(array $messageFields): bool
	{
		if (isset($messageFields['MESSAGE_HEADERS']['template']['text']))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param array $messageFields
	 * @return array{whatsappContent: array}
	 */
	protected function getMessageContent(array $messageFields): array
	{
		$whatsAppContent =
			$this->isTemplateMessage($messageFields)
				? $this->getHSMContent($messageFields)
				: $this->getSimpleMessageContent($messageFields)
		;

		return [
			'whatsappContent' => $whatsAppContent
		];
	}

	/**
	 * @param array $messageFields
	 * @return array{contentType:string, text:string}
	 */
	private function getHSMContent(array $messageFields): array
	{
		$params['contentType'] = Providers\Edna\Constants\ContentType::TEXT;
		$params['text'] = $messageFields['MESSAGE_HEADERS']['template']['text'];

		foreach (['header', 'footer', 'keyboard'] as $templateField)
		{
			if (
				isset($messageFields['MESSAGE_HEADERS']['template'][$templateField])
				&& count($messageFields['MESSAGE_HEADERS']['template'][$templateField]) > 0
			)
			{
				$params[$templateField] = $messageFields['MESSAGE_HEADERS']['template'][$templateField];
			}
		}

		$params = $this->emoji->convertEmojiInTemplate($params, InternalOption::EMOJI_DECODE);

		return $params;
	}

	/**
	 * @param array $messageFields
	 * @return array{contentType:string, text:string}
	 */
	private function getSimpleMessageContent(array $messageFields): array
	{
		return [
			'contentType' => 'TEXT',
			'text' => $messageFields['MESSAGE_BODY']
		];
	}

	protected function sendHSMtoChat(array $messageFields): Result
	{
		if (!Loader::includeModule('imopenlines') || !Loader::includeModule('imconnector'))
		{
			return (new Result())->addError(new Error('Missing modules imopenlines and imconnector'));
		}

		$externalChatId = str_replace('+', '', $messageFields['MESSAGE_TO']);
		$userId = $this->getImconnectorUserId($externalChatId);
		if (!$userId)
		{
			return (new Result())->addError(new Error('Missing User Id'));
		}

		$from = $messageFields['MESSAGE_FROM'];
		$lineId = $this->connectorLine->getLineId();
		$userSessionCode = $this->getSessionUserCode($lineId, $externalChatId, $from, $userId);
		$chatId = $this->getOpenedSessionChatId($userSessionCode);
		if (!$chatId)
		{
			return (new Result())->addError(new Error('Missing Chat Id'));
		}

		$messageId = Im::addMessage([
			'TO_CHAT_ID' => $chatId,
			'MESSAGE' => $this->utils->prepareTemplateMessageText($messageFields),
			'SYSTEM' => 'Y',
			'SKIP_COMMAND' => 'Y',
			'NO_SESSION_OL' => 'Y',
			'PARAMS' => [
				'CLASS' => 'bx-messenger-content-item-ol-output'
			],
		]);

		$result = new Result();
		$resultData = $messageFields;
		$resultData['messageId'] = $messageId;
		$resultData['chatId'] = $chatId;
		$result->setData($resultData);

		if (!$messageId)
		{
			$result->addError(new Error('Error sending a message to the chat'));
		}
		return $result;
	}

	protected function getImconnectorUserId(string $externalChatId): ?string
	{
		$userXmlId = Library::ID_EDNA_WHATSAPP_CONNECTOR . '|' . $externalChatId;
		$user = \Bitrix\Main\UserTable::getRow([
			'select' => ['ID'],
			'filter' => ['=XML_ID' => $userXmlId],
		]);

		return $user ? $user['ID'] : null;
	}

	protected function getSessionUserCode(string $lineId, string $externalChatId, string $from, string $userId): string
	{
		return Library::ID_EDNA_WHATSAPP_CONNECTOR. '|'. $lineId. '|'. $externalChatId. '@'. $from. '|' . $userId;
	}

	protected function getOpenedSessionChatId(string $userSessionCode): ?string
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

	protected function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(InternalOption::API_KEY),
			Constants::API_ENDPOINT,
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}
}