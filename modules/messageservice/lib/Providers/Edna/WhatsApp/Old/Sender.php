<?php

namespace Bitrix\MessageService\Providers\Edna\WhatsApp\Old;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Providers\Constants\InternalOption;
use Bitrix\MessageService\Providers\Edna\WhatsApp\ExternalSender;
use Bitrix\MessageService\Providers\Edna\WhatsApp\StatusResolver;
use Bitrix\MessageService\Sender\Result\MessageStatus;
use Bitrix\MessageService\Sender\Result\SendMessage;

class Sender extends Providers\Edna\WhatsApp\Sender
{
	public function __construct(
		Providers\OptionManager $optionManager,
		Providers\SupportChecker $supportChecker,
		Providers\Edna\EdnaRu $utils,
		EmojiConverter $emoji
	)
	{
		parent::__construct($optionManager, $supportChecker, $utils, $emoji);

		$this->emoji = $emoji;
	}

	public function sendMessage(array $messageFields): SendMessage
	{
		if (!$this->supportChecker->canUse())
		{
			$result = new SendMessage();
			$result->addError(new Error('Service is unavailable'));

			return $result;
		}

		$requestParams = $this->getSendMessageParams($messageFields);
		$method = $this->getSendMessageMethod($messageFields);

		if ($method === 'imOutHSM')
		{
			$this->sendHSMtoChat($messageFields);
		}

		$result = new SendMessage();

		$externalSender =
			new ExternalSender(
				$this->optionManager->getOption(InternalOption::API_KEY),
				Constants::API_ENDPOINT
			)
		;

		$requestResult = $externalSender->callExternalMethod($method, $requestParams);
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

	public function getMessageStatus(array $messageFields): MessageStatus
	{
		$result = new MessageStatus();
		$result->setId($messageFields['ID']);
		$result->setExternalId($messageFields['ID']);

		if (!$this->supportChecker->canUse())
		{
			$result->addError(new Error(Loc::getMessage('MESSAGESERVICE_SENDER_SMS_EDNARU_USE_ERROR')));
			return $result;
		}

		$externalSender =
			new ExternalSender(
				$this->optionManager->getOption(InternalOption::API_KEY),
				Constants::API_ENDPOINT
			)
		;

		$apiResult = $externalSender->callExternalMethod("imOutMessage/{$messageFields['ID']}");
		if (!$apiResult->isSuccess())
		{
			$result->addErrors($apiResult->getErrors());
		}
		else
		{
			$apiData = $apiResult->getData();

			$result->setStatusText($apiData['dlvStatus']);
			$result->setStatusCode((new StatusResolver())->resolveStatus($apiData['dlvStatus']));
		}

		return $result;
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
		return $this->emoji->convertEmoji($text, Providers\Constants\InternalOption::EMOJI_ENCODE);
	}

	/**
	 * Returns request params for sending template or simple message.
	 * @param array $messageFields Message fields.
	 *
	 * @return array
	 */
	private function getSendMessageParams(array $messageFields): array
	{
		$messageFields['MESSAGE_BODY'] = $this->emoji->convertEmoji($messageFields['MESSAGE_BODY'], Providers\Constants\InternalOption::EMOJI_DECODE);
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

			$params = $this->emoji->convertEmojiInTemplate($params, InternalOption::EMOJI_DECODE);
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
	protected function getSendMessageMethod(array $messageFields): string
	{
		$method = 'imOutMessage';
		if ($this->isTemplateMessage($messageFields))
		{
			$method = 'imOutHSM';
		}

		return $method;
	}


}