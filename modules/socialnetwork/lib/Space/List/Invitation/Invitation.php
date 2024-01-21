<?php

namespace Bitrix\Socialnetwork\Space\List\Invitation;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Contract\Arrayable;
use \Bitrix\Main\Type\DateTime;

final class Invitation implements Arrayable
{
	private ?Sender $sender = null;
	private int $receiverId = 0;
	private int $spaceId = 0;
	private ?Datetime $inviteDate = null;

	public function setSender(Sender $sender): self
	{
		$this->sender = $sender;

		return $this;
	}

	public function setReceiverId(int $receiverId): self
	{
		$this->receiverId = $receiverId;

		return $this;
	}

	public function setInviteDate(?DateTime $inviteDate): self
	{
		$this->inviteDate = $inviteDate;

		return $this;
	}

	public function setSpaceId(int $spaceId): self
	{
		$this->spaceId = $spaceId;

		return $this;
	}

	public function getSpaceId(): int
	{
		return $this->spaceId;
	}

	private function getMessage(): string
	{
		return Loc::getMessage(
			'SOCIALNETWORK_SPACES_LIST_INVITATION_MESSAGE',
			['#NAME#' => $this->sender->getName()]
		);
	}

	public function toArray(): array
	{
		return [
			'sender' => $this->sender->toArray(),
			'receiverId' => $this->receiverId,
			'invitationDate' => $this->inviteDate,
			'invitationDateTimestamp' => $this->inviteDate?->getTimestamp(),
			'spaceId' => $this->spaceId,
			'message' => $this->getMessage(),
		];
	}
}