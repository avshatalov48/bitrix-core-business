<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Text\Emoji;
use Bitrix\MessageService\Providers;
use Bitrix\MessageService\Sender\Result\MessageStatus;

class Sender extends Providers\Edna\Sender
{

	public function getMessageStatus(array $messageFields): MessageStatus
	{
		return new MessageStatus();
	}

	public function prepareMessageBodyForSave(string $text): string
	{
		return Emoji::encode($text);
	}

	protected function initializeDefaultExternalSender(): Providers\ExternalSender
	{
		return new ExternalSender(
			$this->optionManager->getOption(Providers\Constants\InternalOption::API_KEY),
			RegionHelper::getApiEndPoint(),
			$this->optionManager->getSocketTimeout(),
			$this->optionManager->getStreamTimeout()
		);
	}

	protected function getSendMessageParams(array $messageFields): Result
	{
		$cascadeResult = $this->getSenderFromSubject($messageFields['MESSAGE_FROM']);
		if (!$cascadeResult->isSuccess())
		{
			return $cascadeResult;
		}

		$params = [
			'requestId' => uniqid('', true),
			'cascadeId' => $cascadeResult->getData()['cascadeId'],
			'subscriberFilter' => [
				'address' => str_replace('+', '', $messageFields['MESSAGE_TO']),
				'type' => 'PHONE',
			],
		];
		$params['content'] = $this->getMessageContent($messageFields);

		return (new Result())->setData($params);
	}

	protected function getSendMessageMethod(array $messageFields): string
	{
		return Providers\Edna\Constants\Method::SEND_MESSAGE;
	}

	protected function isTemplateMessage(array $messageFields): bool
	{
		return false;
	}

	protected function sendHSMtoChat(array $messageFields): Result
	{
		return new Result();
	}

	/**
	 * @param array $messageFields
	 * @return array{contentType:string, text:string}
	 */
	private function getMessageContent(array $messageFields): array
	{
		return [
			'smsContent' => [
				'contentType' => Providers\Edna\Constants\ContentType::TEXT,
				'text' => $this->prepareMessageBodyForSend($messageFields['MESSAGE_BODY']),
			],
		];
	}

	private function getSenderFromSubject($subject): Result
	{
		$cascadeResult = new Result();
		if (is_numeric($subject))
		{
			$cascadeResult = $this->utils->getCascadeIdFromSubject(
				(int)$subject,
				static function(array $externalSubjectData, int $internalSubject)
				{
					return $externalSubjectData['id'] === $internalSubject;
				}
			);
		}
		elseif (is_string($subject))
		{
			$cascadeResult = $this->utils->getCascadeIdFromSubject(
				$subject,
				static function(array $externalSubjectData, string $internalSubject)
				{
					return $externalSubjectData['subject'] === $internalSubject;
				}
			);
		}
		else
		{
			$cascadeResult->addError(new Error('Invalid subject id'));
		}

		return $cascadeResult;
	}
}