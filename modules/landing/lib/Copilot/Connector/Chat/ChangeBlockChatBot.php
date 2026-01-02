<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

use Bitrix\AI\Chatbot\Dto\MessageDto;
use Bitrix\AI\Chatbot\Message\DefaultMessage;
use Bitrix\AI\Chatbot\Message\Message;
use Bitrix\AI\Chatbot\Message\SystemMessage;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class ChangeBlockChatBot extends CopilotChatBot
{
	protected const MODULE_ID = 'landing';
	protected const BOT_CODE = 'site_with_ai_change_block';

	public function onChatStart(int $chatId, array $parameters = []): void
	{
		$this->sendAnswer($chatId, new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_WELCOME')));
		$this->stopWriting($chatId, false);
	}

	/**
	 * @param MessageDto $message
	 * @param array|null $command
	 * @return void
	 *
	 * @throws SystemException|LoaderException
	 */
	public function onMessageAdd(MessageDto $message, ?array $command = null): void
	{
		$chatId = $message->chatId;

		if (is_array($command))
		{
			$commandName = is_string($command[0]) ? $command[0] : null;
			$commandParams = is_array($command[1]) ? $command[1] : [];

			if ($commandName === ChatBotCommands::restartGeneration->value)
			{
				$this->handleRestartGenerationCommand($message, $commandParams);
			}

			if ($commandName === ChatBotCommands::startOver->value)
			{
				$messageDto = new ChatBotMessageDto($chatId);
				$this->sendSelectBlockSuccessMessage($messageDto);
			}

			return;
		}

		$this->startWriting($chatId);
	}

	private function handleRestartGenerationCommand(MessageDto $message, array $params = []): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$generationId = $params['generationId'] ?? 0;
		if ($generationId <= 0)
		{
			return;
		}

		$generation = new Generation();
		if ($generation->initById($generationId))
		{
			$generation->clearErrors()->execute();

			$this->sendAnswer(
				$chatId,
				new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_GENERATION_RESTART')),
			);
		}

		$this->stopWriting($chatId, false);
	}

	public function sendWelcomeMessage(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$messages = $this->getMessages($chatId, 1);
		$prevMessage = array_shift($messages);
		$startMessage = Loc::getMessage('LANDING_CHATBOT_BLOCKS_WELCOME');
		if (
			!$prevMessage
			|| $prevMessage->content !== $startMessage
		)
		{
			$this->sendAnswer($chatId, new DefaultMessage($startMessage));
		}
	}

	public function sendNeedSelectBlockMessage(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$this->sendAnswer(
			$chatId,
			new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_NEED_SELECT_BLOCK_MESSAGE'))
		);
		$this->stopWriting($chatId, false);
	}

	public function sendSelectBlockSuccessMessage(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$this->sendAnswer(
			$chatId,
			new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_SELECT_BLOCK_SUCCESS'))
		);
		$this->stopWriting($chatId);
	}

	public function sendSelectBlockWrongMessage(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$this->sendAnswer($chatId,
			new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_SELECT_BLOCK_WRONG')));
		$this->stopWriting($chatId, false);
	}

	public function sendGenerationEndMessage(ChatBotMessageDto $message): void
	{
		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$this->sendAnswer($chatId, $this->getFinishGenerationMessage());
		$this->stopWriting($chatId);
	}

	/**
	 * @inheritdoc
	 */
	protected function getQuotaExceededMessage(ChatBotMessageDto $message): ?Message
	{
		$errorText = $message->params['errorText'] ?? '';
		$answer = new SystemMessage($errorText);

		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			ChatBotCommands::restartGeneration->value,
			[
				'generationId' => $message->generationId ?? null,
			],
		);

		return $answer;
	}

	/**
	 * @inheritdoc
	 */
	protected function getRestrictedRequestMessage(ChatBotMessageDto $message): Message
	{
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_ERROR_RESTRICTED_REQUEST'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_START_OVER_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_START_OVER_BUTTON'),
			ChatBotCommands::startOver->value,
			[
				'generationId' => $message->generationId ?? null,
			],
		);

		return $answer;
	}

	/**
	 * @inheritdoc
	 */
	protected function getVendorErrorMessage(ChatBotMessageDto $message): Message
	{
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_ERROR_VENDOR'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			ChatBotCommands::restartGeneration->value,
			[
				'generationId' => $message->generationId ?? null,
			],
		);

		return $answer;
	}

	/**
	 * @inheritdoc
	 */
	protected function getB24ErrorMessage(ChatBotMessageDto $message): Message
	{
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_ERROR_B24'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_BLOCKS_RETRY_GENERATION_BUTTON'),
			ChatBotCommands::restartGeneration->value,
			[
				'generationId' => $message->generationId ?? null,
			],
		);

		return $answer;
	}

	/**
	 * @inheritdoc
	 */
	protected function getStartGenerationMessage(): Message
	{
		return new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_GENERATION_START'));
	}

	/**
	 * @inheritdoc
	 */
	protected function getFinishGenerationMessage(): Message
	{
		return new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_BLOCKS_GENERATION_FINISH'));
	}
}
