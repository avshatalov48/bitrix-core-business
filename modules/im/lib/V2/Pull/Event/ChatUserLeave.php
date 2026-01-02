<?php

declare(strict_types = 1);

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Chat\OpenLineChat;
use Bitrix\Im\V2\Pull\EventType;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Relation;
use Bitrix\Im\V2\RelationCollection;

class ChatUserLeave extends BaseChatEvent
{
	protected int $deletedUserId;
	protected RelationCollection $relations;

	public function __construct(Chat $chat, int $deletedUserId, RelationCollection $relations)
	{
		$this->deletedUserId = $deletedUserId;
		$this->relations = $relations;

		parent::__construct($chat);
	}

	protected function getBasePullParamsInternal(): array
	{
		return [
			'chatId' => $this->chat->getChatId(),
			'dialogId' => $this->getBaseDialogId(),
			'chatTitle' => Text::decodeEmoji($this->chat->getTitle() ?? ''),
			'userId' => $this->deletedUserId,
			'relations' => $this->chat->getRelationsByUserIds([$this->deletedUserId])->toRestFormat(),
			'message' => $this->getLeaveMessageText(),
			'userCount' => $this->chat->getUserCount(),
			'chatExtranet' => $this->chat->getExtranet() ?? false,
			'containsCollaber' => $this->chat->containsCollaber(),
		];
	}

	protected function getType(): EventType
	{
		return EventType::ChatUserLeave;
	}

	protected function getSkippedUserIds(): array
	{
		if ($this->chat instanceof OpenLineChat)
		{
			$filteredRelations = $this->relations->filter(
				fn (Relation $relation) => $relation->getUser()->getExternalAuthId() === 'imconnector',
			);

			return $filteredRelations->getUserIds();
		}

		return parent::getSkippedUserIds();
	}

	protected function getLeaveMessageText(): string
	{
		return
			$this->deletedUserId !== $this->chat->getContext()->getUserId()
				? $this->chat->getUserDeleteMessageText($this->deletedUserId)
				: ''
		;
	}

	protected function getRecipients(): array
	{
		return array_values($this->relations->getUserIds());
	}
}
