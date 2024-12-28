<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Integration\IM\ActionType;
use Bitrix\Socialnetwork\Item\UserToGroup;

class JoinUserActionMessage implements ActionMessageInterface
{
	use GetMessageSenderTrait;

	protected int $collabId;
	protected int $senderId;

	public function __construct(int $collabId, int $senderId)
	{
		$this->collabId = $collabId;
		$this->senderId = $senderId;
	}
	
	public function runAction(array $recipientIds = [], array $parameters = []): int
	{
		if (!Loader::includeModule('im'))
		{
			return 0;
		}

		$sender = $this->getMessageSender($this->collabId, $this->senderId);
		if ($sender === null)
		{
			return 0;
		}

		return $sender->sendActionMessage(ActionType::JoinUser);
	}
}