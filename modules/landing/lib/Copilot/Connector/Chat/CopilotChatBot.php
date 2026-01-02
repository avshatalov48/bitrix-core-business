<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

use Bitrix\AI\Chatbot\Chatbot;
use Bitrix\AI\Chatbot\Message\Message;
use Bitrix\AI\Chatbot\Message\SystemMessage;
use Bitrix\Landing\Copilot\Generation\Type\GenerationErrors;
use Bitrix\Landing\Manager;
use Bitrix\Pull;

abstract class CopilotChatBot extends Chatbot implements ICopilotChatBot
{
	/**
	 * @inheritdoc
	 */
	public function sendErrorMessage(ChatBotMessageDto $message): void
	{
		$errorCode = $message->errorCode;
		if (!$errorCode)
		{
			return;
		}

		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		if ($errorCode === GenerationErrors::requestQuotaExceeded)
		{
			$answer = $this->getQuotaExceededMessage($message);
		}
		elseif ($errorCode === GenerationErrors::restrictedRequest)
		{
			$answer = $this->getRestrictedRequestMessage($message);
		}
		elseif (
			$errorCode === GenerationErrors::notExistResponse
			|| $errorCode === GenerationErrors::notFullyResponse
			|| $errorCode === GenerationErrors::notCorrectResponse
			|| $errorCode === GenerationErrors::errorInRequest
		)
		{
			$answer = $this->getVendorErrorMessage($message);
		}
		elseif (
			$errorCode === GenerationErrors::dataValidation
			|| $errorCode === GenerationErrors::notSendRequest
		)
		{
			$answer = $this->getB24ErrorMessage($message);
		}

		if (!isset($answer))
		{
			$answer = new SystemMessage('Unknown error');
		}

		$this->sendAnswer($message->chatId, $answer);
		$this->stopWriting($message->chatId, false);
	}

	/**
	 * @param ChatBotMessageDto $message
	 * @return Message|null
	 */
	abstract protected function getQuotaExceededMessage(ChatBotMessageDto $message): ?Message;

	/**
	 * @return Message
	 */
	abstract protected function getRestrictedRequestMessage(ChatBotMessageDto $message): Message;

	/**
	 * @return Message
	 */
	abstract protected function getVendorErrorMessage(ChatBotMessageDto $message): Message;

	/**
	 * @return Message
	 */
	abstract protected function getB24ErrorMessage(ChatBotMessageDto $message): Message;

	/**
	 * @inheritdoc
	 */
	public function onRequestQuotaOk(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$this->sendAnswer($chatId, $this->getStartGenerationMessage());
		$this->stopWriting($chatId, false);
	}

	/**
	 * @return Message
	 */
	abstract protected function getStartGenerationMessage(): Message;

	/**
	 * @return Message
	 */
	abstract protected function getFinishGenerationMessage(): Message;

	protected function sendFrontendEvent(ChatBotCommands $command, array $params = []): void
	{
		$userId = Manager::getUserId();
		if ($userId <= 0)
		{
			return;
		}

		$commandName = 'LandingCopilotChatbot:on' . ucfirst($command->value);
		Pull\Event::add($userId, [
			'module_id' => 'landing',
			'command' => $commandName,
			'params' => $params,
		]);
		Pull\Event::send();
	}
}
