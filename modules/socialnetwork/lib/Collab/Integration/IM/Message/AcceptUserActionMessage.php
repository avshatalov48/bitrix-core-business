<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM\Message;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Collab\Integration\Intranet\Invitation;

class AcceptUserActionMessage implements ActionMessageInterface
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

		$this->addUsersToChat($this->collabId, $this->senderId);

		$message = (string)Loc::getMessage(
			'SOCIALNETWORK_COLLAB_CHAT_USER_ACCEPT' . $this->getGenderSuffix($this->senderId),
			[
				'#SENDER_NAME#' => $this->getName($this->senderId, $this->senderId, $this->collabId),
			],
		);

		$this->sendAcceptUserAnalytics();

		return $this->sendMessage($message, $this->senderId, $this->collabId);
	}

	private function sendAcceptUserAnalytics(): void
	{
		$fields = Invitation::getFields($this->senderId);

		if ($fields === null)
		{
			return;
		}

		$event = new Event('intranet', 'onUserFirstInitialization', [
			'invitationFields' => $fields,
			'userId' => $this->senderId,
		]);

		$event->send();
	}
}
