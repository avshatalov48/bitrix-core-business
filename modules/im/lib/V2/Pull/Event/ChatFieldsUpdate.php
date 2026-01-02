<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;

class ChatFieldsUpdate extends BaseChatEvent
{
	use ContextCustomer;
	use DialogIdFiller;

	protected array $updateFields = [];

	public function __construct(Chat $chat, array $updateFields)
	{
		parent::__construct($chat);
		$this->updateFields = $updateFields;
	}

	protected function getType(): EventType
	{
		return EventType::ChatFieldsUpdate;
	}

	protected function getBasePullParamsInternal(): array
	{
		$this->updateFields['chatId'] = $this->chat->getId();
		$this->updateFields['dialogId'] = $this->getBaseDialogId();

		return $this->updateFields;
	}

	public function shouldSendImmediately(): bool
	{
		return true;
	}
}
