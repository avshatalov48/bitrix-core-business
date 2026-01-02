<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;

/**
 * @deprecated
 * @see InputActionNotify
 */
class StartWriting extends BaseChatEvent
{
	use ContextCustomer;
	use DialogIdFiller;

	protected ?string $customUserName = null;
	protected int $expiry = 60;

	public function setCustomUserName(?string $customUserName): self
	{
		if ($customUserName)
		{
			$this->customUserName = $customUserName;
		}

		return $this;
	}

	protected function getType(): EventType
	{
		return EventType::StartWriting;
	}

	protected function getBasePullParamsInternal(): array
	{
		return [
			'dialogId' => $this->getBaseDialogId(),
			'userId' => $this->getContext()->getUserId(),
			'userName' => $this->customUserName ?? $this->getContext()->getUser()->getName(),
			'userFirstName' => $this->customUserName ?? $this->getContext()->getUser()->getFirstName(),
		];
	}

	protected function getSkippedUserIds(): array
	{
		return [$this->getContext()->getUserId()];
	}
}
