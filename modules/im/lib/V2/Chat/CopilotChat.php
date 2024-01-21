<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Error;
use Bitrix\Im\V2\Integration\AI\Restriction;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\Service\Context;
use Bitrix\Im\V2\Message\Params;
use Bitrix\ImBot\Bot;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class CopilotChat extends GroupChat
{
	private const COUNTER_CHAT_CODE = 'copilot_chat_counter';

	protected function getDefaultType(): string
	{
		return self::IM_TYPE_COPILOT;
	}

	public function getDefaultManageUsersAdd(): string
	{
		return self::MANAGE_RIGHTS_NONE;
	}

	public function getDefaultManageUsersDelete(): string
	{
		return self::MANAGE_RIGHTS_NONE;
	}

	public function getDefaultManageSettings(): string
	{
		return self::MANAGE_RIGHTS_NONE;
	}

	public function setCanPost(string $canPost): Chat
	{
		return $this;
	}

	public function setManageSettings(string $manageSettings): Chat
	{
		return $this;
	}

	public function setManageUI(string $manageUI): Chat
	{
		return $this;
	}

	public function setManageUsersAdd(string $manageUsersAdd): Chat
	{
		return $this;
	}

	public function setManageUsersDelete(string $manageUsersDelete): Chat
	{
		return $this;
	}

	public function add(array $params, ?Context $context = null): Result
	{
		$result = new Result();

		if (!Loader::includeModule('imbot'))
		{
			return $result->addError(new ChatError(ChatError::IMBOT_NOT_INSTALLED));
		}

		if (!(new Restriction(Restriction::AI_COPILOT_CHAT))->isAvailable())
		{
			return $result->addError(new Error(Restriction::AI_TEXT_ERROR));
		}

		$copilotBotId = static::getBotIdOrRegister();

		if (!$copilotBotId)
		{
			return $result->addError(new Error(ChatError::COPILOT_NOT_INSTALLED));
		}

		$context ??= new Context();
		$params['USERS'] = [$context->getUserId(), $copilotBotId];

		return parent::add($params, $context);
	}

	protected function sendGreetingMessage(?int $authorId = null)
	{
		return;
	}

	protected function sendMessageUsersAdd(array $usersToAdd, bool $skipRecent = false): void
	{
		return;
	}

	protected function sendBanner(?int $authorId = null): void
	{
		\CIMMessage::Add([
			'MESSAGE_TYPE' => $this->getType(),
			'TO_CHAT_ID' => $this->getChatId(),
			'FROM_USER_ID' => Bot\CopilotChatBot::getBotId(),
			'MESSAGE' => Loc::getMessage('IM_CHAT_CREATE_COPILOT_WELCOME'),
			'SKIP_USER_CHECK' => 'Y',
			'PUSH' => 'N',
			'SKIP_COUNTER_INCREMENTS' => 'Y',
			'PARAMS' => [
				Params::COMPONENT_ID => Bot\CopilotChatBot::MESSAGE_COMPONENT_START,
				Params::NOTIFY => 'N',
			]
		]);
	}

	protected function sendDescriptionMessage(?int $authorId = null): void
	{
		return;
	}

	public function getDescription(): ?string
	{
		return null;
	}

	protected function prepareParams(array $params = []): Result
	{
		unset($params['TITLE']);

		return parent::prepareParams($params);
	}

	public static function getTitleTemplate(): ?string
	{
		return Loc::getMessage('IM_CHAT_COPILOT_CHAT_TITLE');
	}

	protected function generateTitle(): string
	{
		$copilotChatCounter = \CUserOptions::GetOption('im', self::COUNTER_CHAT_CODE, 1, $this->getContext()->getUserId());
		$title = Loc::getMessage('IM_CHAT_COPILOT_CHAT_TITLE', ['#NUMBER#' => $copilotChatCounter]);
		\CUserOptions::SetOption('im', self::COUNTER_CHAT_CODE, $copilotChatCounter + 1);

		return $title;
	}

	protected function addIndex(): Chat
	{
		return $this;
	}

	protected function updateIndex(): Chat
	{
		return $this;
	}


	protected function sendEventUsersAdd(array $usersToAdd): void
	{
		if (empty($usersToAdd))
		{
			return;
		}

		foreach ($usersToAdd as $userId)
		{
			$relation = $this->getRelations()->getByUserId($userId, $this->getId());
			if ($relation === null)
			{
				continue;
			}
			if ($relation->getUser()->isBot())
			{
				Im\Bot::changeChatMembers($this->getId(), $userId);
				Im\Bot::onJoinChat('chat'.$this->getId(), [
					'CHAT_TYPE' => $this->getType(),
					'MESSAGE_TYPE' => $this->getType(),
					'BOT_ID' => $userId,
					'USER_ID' => $this->getContext()->getUserId(),
					'CHAT_ID' => $this->getId(),
					'CHAT_AUTHOR_ID' => $this->getAuthorId(),
					'CHAT_ENTITY_TYPE' => $this->getEntityType(),
					'CHAT_ENTITY_ID' => $this->getEntityId(),
					'ACCESS_HISTORY' => (int)$relation->getStartCounter() === 0,
					'SILENT_JOIN' => 'Y', // suppress system message
				]);
			}
		}
	}

	private static function getBotIdOrRegister(): int
	{
		return Bot\CopilotChatBot::getBotId() ?: Bot\CopilotChatBot::register();
	}

	public static function isAvailable(): bool
	{
		return Loader::includeModule('imbot')
			&& (new Restriction(Restriction::AI_COPILOT_CHAT))->isAvailable()
			&& static::getBotIdOrRegister();
	}
}