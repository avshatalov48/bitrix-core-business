<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\AI\Transcription\Item\TranscribeFileItem;
use Bitrix\Im\V2\Pull\EventType;

class FileTranscriptionEvent extends BaseChatEvent
{
	protected TranscribeFileItem $transcribeFileItem;
	protected ?int $userId = null;

	public function __construct(
		Chat $chat,
		TranscribeFileItem $transcribeFileItem,
		?int $userId = null
	)
	{
		parent::__construct($chat);
		$this->transcribeFileItem = $transcribeFileItem;
		$this->userId = $userId;
	}

	protected function getBasePullParamsInternal(): array
	{
		return $this->transcribeFileItem->toRestFormat();
	}

	protected function getType(): EventType
	{
		return EventType::FileTranscription;
	}

	protected function getRecipients(): array
	{
		if (isset($this->userId))
		{
			return [$this->userId];
		}

		return $this->chat->getRelations()->filterActive()->getUserIds();
	}
}
