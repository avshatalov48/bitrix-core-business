<?php

namespace Bitrix\Im\V2\Chat\Copilot;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\AI\EngineManager;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\MessageCollection;
use Bitrix\Im\V2\Registry;
use Bitrix\Im\V2\Rest\PopupDataItem;

class CopilotPopupItem implements PopupDataItem
{
	private ?array $roleCodes = null;

	private ?array $engineCodes = null;

	private ?MessageCollection $messages = null;

	/**
	 * @var null|Chat\GroupChat[] $chats
	 */
	private ?array $chats = null;

	private array $messageIds;

	private array $chatIds;

	public function __construct(array $chatIds = [], array $messageIds = [])
	{
		$chatIds = array_filter($chatIds, 'is_int');
		$messageIds = array_filter($messageIds, 'is_int');

		$this->chatIds = array_combine($chatIds, $chatIds);
		$this->messageIds = array_combine($messageIds, $messageIds);
	}

	public static function getInstanceByChatIdsAndMessages(MessageCollection $messages, array $chatIds): self
	{
		$messages = clone $messages;
		$instance = new self(chatIds: $chatIds, messageIds: $messages->getIds());
		$instance->messages = $messages;

		return $instance;
	}

	public static function getInstanceByMessages(MessageCollection $messages): self
	{
		$messages = clone $messages;
		$instance = new self(messageIds: $messages->getIds());
		$instance->messages = $messages;

		return $instance;
	}

	public static function getInstanceByChatIds(array $chatIds): self
	{
		return new self(chatIds: $chatIds);
	}

	public function merge(PopupDataItem $item): self
	{
		if ($item instanceof static)
		{
			$this
				->mergeArray($this->chatIds, $item->chatIds)
				->mergeArray($this->messageIds, $item->messageIds)
				->mergeArray($this->chats, $item->chats)
				->mergeArray($this->engineCodes, $item->engineCodes)
				->mergeArray($this->roleCodes, $item->roleCodes)
				->mergeMessages($item->messages)
			;
		}

		return $this;
	}

	protected function mergeArray(?array &$target, ?array $source): self
	{
		if (isset($target, $source))
		{
			$target += $source;
		}
		elseif (isset($source))
		{
			$target = $source;
		}

		return $this;
	}

	protected function mergeMessages(?MessageCollection $messages): self
	{
		if (isset($this->messages, $messages))
		{
			$this->messages->fillParams();
			$messages->fillParams();

			$this->messages->mergeRegistry($messages);
		}
		elseif (isset($messages))
		{
			$this->messages = clone $messages;
		}

		return $this;
	}

	public static function getRestEntityName(): string
	{
		return 'copilot';
	}

	public function toRestFormat(array $option = []): ?array
	{
		$chats = $this->getChatsForRest();
		$messages = $this->getMessagesForRest();

		if (empty($chats) && empty($messages))
		{
			return null;
		}

		$engines = $this->getEnginesForRest();
		$roles = $this->getRolesForRest();

		return [
			'chats' => !empty($chats) ? $chats : null,
			'messages' => !empty($messages) ? $messages : null,
			'engines' => !empty($engines) ? $engines : null,
			'roles' => !empty($roles) ? $roles : null,
			'aiProvider' => EngineManager::getDefaultEngineName(),
		];
	}

	protected function getChatsForRest(): array
	{
		$result = [];

		foreach ($this->getChats() as $chat)
		{
			$engineCode = $chat->getEngineCode();

			$result[] = [
				'dialogId' => $chat->getDialogId(),
				'role' => $chat->getCopilotRole(),
				'engine' => $engineCode,
			];
		}

		return $result;
	}

	protected function getMessagesForRest(): array
	{
		$result = [];

		$rolesData = $this->getMessages()->getCopilotRoles();

		foreach ($rolesData as $messageId => $roleCode)
		{
			$result[] = [
				'id' => $messageId,
				'role' => $roleCode,
			];
		}

		return $result;
	}

	protected function getRolesForRest(): array
	{
		if (!isset($this->roleCodes))
		{
			$chatRoleCodes = [];

			foreach ($this->getChats() as $chat)
			{
				$roleCode = $chat->getCopilotRole();
				$chatRoleCodes[$roleCode] = $roleCode;
			}

			$messagesRoleCodes = array_values($this->getMessages()->getCopilotRoles());
			$messagesRoleCodes = array_combine($messagesRoleCodes, $messagesRoleCodes);

			$this->roleCodes = $chatRoleCodes + $messagesRoleCodes;
		}

		return (new RoleManager())->getRoles(array_values($this->roleCodes)) ?? [];
	}

	protected function getEnginesForRest(): array
	{
		if (!isset($this->engineCodes))
		{
			$engineCodes = [];

			foreach ($this->getChats() as $chat)
			{
				$engineCode = $chat->getEngineCode();

				if (isset($engineCode))
				{
					$engineCodes[$engineCode] = $engineCode;
				}
			}

			$this->engineCodes = $engineCodes;
		}

		$engines = (new EngineManager())->getEnginesByCodes(array_values($this->engineCodes));

		$result = [];
		foreach ($engines as $engine)
		{
			$engine = $engine->getIEngine();
			$result[] = [
				'code' => $engine->getCode(),
				'name' => $engine->getName(),
			];
		}

		return $result;
	}

	protected function getMessages(): MessageCollection
	{
		if (isset($this->messages))
		{
			return $this->messages;
		}

		$this->messages = new MessageCollection($this->messageIds);

		return $this->messages;
	}

	/**
	 * @return Chat\GroupChat[]
	 */
	protected function getChats(): array
	{
		if (isset($this->chats))
		{
			return array_values($this->chats);
		}

		$result = [];
		foreach ($this->chatIds as $chatId)
		{
			$chat = Chat::getInstance($chatId);
			if ($chat instanceof Chat\GroupChat && $chat->containsCopilot())
			{
				$result[$chat->getId()] = $chat;
			}
		}

		$this->chats = $result;

		return array_values($this->chats);
	}

	public static function convertArrayDataForChats(array $data): array
	{
		$result = [];

		foreach ($data as $id => $item)
		{
			$result[] = ['dialogId' => $id, 'role' => $item];
		}

		return $result;
	}

	public static function convertArrayDataForMessages(array $data): array
	{
		$result = [];

		foreach ($data as $id => $item)
		{
			$result[] = ['id' => $id, 'role' => $item];
		}

		return $result;
	}
}
