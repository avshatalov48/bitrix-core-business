<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class RegenerateLinkActionMessage implements ActionMessageInterface
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

		$message = (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_REGENERATE_LINK' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId, $this->senderId, $this->collabId),
			],
		);

		return $this->sendMessage($message, $this->senderId, $this->collabId);
	}
}