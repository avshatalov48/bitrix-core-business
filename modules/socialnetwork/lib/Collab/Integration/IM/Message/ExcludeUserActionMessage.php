<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class ExcludeUserActionMessage implements ActionMessageInterface
{
	use MessageTrait;

	protected int $collabId;
	protected int $senderId;

	public function __construct(int $collabId, int $senderId)
	{
		$this->collabId = $collabId;
		$this->senderId = $senderId;
	}

	public function send(array $recipientIds = [], array $parameters = []): int
	{
		if (!Loader::includeModule('im'))
		{
			return 0;
		}

		if (empty($recipientIds))
		{
			return 0;
		}

		$recipientNames = [];
		foreach ($recipientIds as $recipientId)
		{
			$recipientNames[] = $this->getName($this->senderId, $recipientId, $this->collabId);
		}

		$this->deleteUsersFromChat($this->collabId, ...$recipientIds);

		$userNames = implode(', ', $recipientNames);
		$senderName = $this->getName($this->senderId, $this->senderId, $this->collabId);

		$message = (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_EXCLUDE' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $senderName,
				'#RECIPIENT#' => $userNames,
			],
		);

		return $this->sendMessage($message, $this->senderId, $this->collabId);
	}
}