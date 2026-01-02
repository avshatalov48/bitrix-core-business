<?php

declare(strict_types = 1);

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Entity\User\UserCollection;
use Bitrix\Im\V2\Pull\EventType;
use Bitrix\Im\V2\Chat;

class ChatUserAdd extends BaseChatEvent
{
	protected array $usersToAdd;

	public function __construct(Chat $chat, array $usersToAdd)
	{
		$this->usersToAdd = $usersToAdd;

		parent::__construct($chat);
	}

	protected function getBasePullParamsInternal(): array
	{
		return [
			'chatId' => $this->chat->getChatId(),
			'dialogId' => $this->getBaseDialogId(),
			'chatTitle' => Text::decodeEmoji($this->chat->getTitle() ?? ''),
			'chatOwner' => $this->chat->getAuthorId(),
			'chatExtranet' => $this->chat->getExtranet() ?? false,
			'containsCollaber' => $this->chat->containsCollaber(),
			'users' => (new UserCollection($this->usersToAdd))->toRestFormat(['IDS_AS_KEY' => true]),
			'newUsers' => array_values($this->usersToAdd),
			'relations' => $this->chat->getRelationsByUserIds($this->usersToAdd)->toRestFormat(),
			'userCount' => $this->chat->getUserCount(),
			'callToken' => $this->chat->getCallToken()->getToken(),
		];
	}

	protected function getType(): EventType
	{
		return EventType::ChatUserAdd;
	}
}
