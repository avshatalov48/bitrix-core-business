<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\Chat;

use Bitrix\AI\Chatbot\Dto\MessageDto;
use Bitrix\AI\Chatbot\Enum\MessageType;
use Bitrix\AI\Chatbot\Message\CreateSiteGreetingMessage;
use Bitrix\AI\Chatbot\Message\DefaultMessage;
use Bitrix\AI\Chatbot\Message\Message;
use Bitrix\AI\Chatbot\Message\SystemMessage;
use Bitrix\Landing\Copilot\Data\Wishes;
use Bitrix\Landing\Copilot\Generation;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing\Metrika;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class CreateSiteChatBot extends CopilotChatBot
{
	protected const MODULE_ID = 'landing';
	protected const BOT_CODE = 'site_with_ai';

	private const WELCOME_STAGE = 'welcome';
	private const CREATE_SITE_STAGE = 'createSite';
	private const RESTART_SITE_STAGE = 'restartSite';
	private const START_OVER_STAGE = 'startOver';

	public function onChatStart(int $chatId, array $parameters = []): void
	{
		$message = new CreateSiteGreetingMessage('');
		$message->addButton(
			'',
			Loc::getMessage('LANDING_CHATBOT_SITE_CREATE_SITE'),
			ChatBotCommands::startGeneration->value,
		);
		$this->sendAnswer($chatId, $message);
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
		$this->startWriting($message->chatId);

		$chatId = $message->chatId;
		if ($chatId <= 0)
		{
			return;
		}

		$commandParams = is_array($command[1]) ? $command[1] : [];
		$currentStage = $this->getCurrentScenarioStage($chatId, $message);

		switch ($currentStage)
		{
			case self::CREATE_SITE_STAGE:
			{
				$this->handleCreateSiteStage($chatId);

				break;
			}
			case self::RESTART_SITE_STAGE:
			{
				$this->handleRestartSiteStage($chatId, $commandParams);

				break;
			}
			case self::START_OVER_STAGE:
			{
				$this->handleStartOver($chatId, $commandParams);

				break;
			}
		}
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
	 * @param int $chatId
	 * @param MessageDto $userMessage
	 *
	 * @return string
	 */
	private function getCurrentScenarioStage(int $chatId, MessageDto $userMessage): string
	{
		if ($userMessage->type === MessageType::ButtonClicked)
		{
			$userMessageCommand = $userMessage->params['command'][0] ?? '';

			if ($userMessageCommand === ChatBotCommands::startGeneration->value)
			{
				return self::CREATE_SITE_STAGE;
			}

			if ($userMessageCommand === ChatBotCommands::restartGeneration->value)
			{
				return self::RESTART_SITE_STAGE;
			}

			if ($userMessageCommand === ChatBotCommands::startOver->value)
			{
				return self::START_OVER_STAGE;
			}
		}

		$messages = $this->getMessages($chatId, 0);
		$messagesCount = count($messages);

		if ($messagesCount === 2)
		{
			return self::CREATE_SITE_STAGE;
		}

		return self::WELCOME_STAGE;
	}

	/**
	 * @param int $chatId
	 * @param array $params
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function handleStartOver(int $chatId, array $params = []): void
	{
		$this->sendAnswer(
			$chatId,
			new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_SITE_START_OVER_SITE_MESSAGE'))
		);
		$this->stopWriting($chatId, false);

		$this->sendFrontendEvent(ChatBotCommands::startOver, $params);
	}

	/**
	 * @param int $chatId
	 *
	 * @return void
	 */
	private function handleCreateSiteStage(int $chatId): void
	{
		$wishes = $this->getWishesForSiteGenerating($chatId);
		$generator = new Generation();
		if (
			$generator
				->setScenario(new Generation\Scenario\CreateSite())
				->setChatId($chatId)
				->setWishes($wishes)
				->setMetrikaFields(new Metrika\FieldsDto(
					params: [[
						3,
						'context',
						$wishes->isDemoWishes() ? 'no' : 'user'
					]],
				))
				->execute()
		)
		{
			$this->sendFrontendEvent(ChatBotCommands::startGeneration, [
				'generationId' => $generator->getId(),
			]);
		}
	}

	/**
	 * @param int $chatId
	 *
	 * @return Wishes
	 */
	private function getWishesForSiteGenerating(int $chatId): Data\Wishes
	{
		$messages = $this->getMessages($chatId);
		$wishes = new Data\Wishes();

		// wishes
		if (isset($messages[1]) && count($messages) === 2)
		{
			$buttonId = $messages[1]->params['buttonId'] ?? null;
			if ($buttonId === null)
			{
				$wishes->addWish($messages[1]->content);
			}
		}

		if (empty($wishes->getWishes()))
		{
			$wishes->setDemoWishes();
		}

		return $wishes;
	}

	/**
	 * @param int $chatId
	 * @param array $params
	 * @return void
	 * @throws LoaderException
	 * @throws SystemException
	 */
	private function handleRestartSiteStage(int $chatId, array $params = []): void
	{
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
			$this->sendFrontendEvent(ChatBotCommands::restartGeneration, $params);

			$generation->clearErrors()->execute();
		}
		// todo: else - error?

		$this->stopWriting($chatId, false);

	}

	/**
	 * @inheritdoc
	 */
	protected function getQuotaExceededMessage(ChatBotMessageDto $message): ?Message
	{
		$errorText = $message->params['errorText'] ?? '';
		$answer = new SystemMessage($errorText);

		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_SITE_RETRY_CREATE_SITE_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_SITE_CREATE_SITE'),
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
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_SITE_ERROR_RESTRICTED_REQUEST'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_SITE_START_OVER_SITE_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_SITE_START_OVER_SITE_BUTTON'),
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
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_SITE_ERROR_VENDOR'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_SITE_RETRY_CREATE_SITE_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_SITE_RETRY_CREATE_SITE_BUTTON'),
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
		$answer = new SystemMessage(Loc::getMessage('LANDING_CHATBOT_SITE_ERROR_B24'));
		$answer->addButton(
			Loc::getMessage('LANDING_CHATBOT_SITE_RETRY_CREATE_SITE_BUTTON'),
			Loc::getMessage('LANDING_CHATBOT_SITE_RETRY_CREATE_SITE_BUTTON'),
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
		return new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_SITE_START_SITE_GENERATING'));
	}

	/**
	 * @inheritdoc
	 */
	protected function getFinishGenerationMessage(): Message
	{
		return new DefaultMessage(Loc::getMessage('LANDING_CHATBOT_SITE_FINISH_SITE_GENERATING'));
	}
}
