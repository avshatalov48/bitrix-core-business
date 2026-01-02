<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\InputAction\Type;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\EventType;
use Bitrix\Main\ArgumentException;

class InputActionNotify extends BaseChatEvent
{
	use ContextCustomer;
	use DialogIdFiller;

	protected ?string $customUserName = null;
	protected Type $actionType;
	protected int $expiry = 60;
	protected ?string $statusMessageCode = null;
	protected ?int $duration = null;
	private const MIN_DURATION = 1;
	private const MAX_DURATION = 600;

	public function __construct(Chat $chat, Type $actionType)
	{
		parent::__construct($chat);
		$this->actionType = $actionType;
	}

	protected function getType(): EventType
	{
		return EventType::InputActionNotify;
	}

	public function setCustomUserName(?string $customUserName): self
	{
		if ($customUserName)
		{
			$this->customUserName = $customUserName;
		}

		return $this;
	}

	public function setStatusMessageCode(?string $statusMessageCode): self
	{
		$this->statusMessageCode = $statusMessageCode;

		return $this;
	}

	public function setDuration(?int $duration): self
	{
		if ($duration === null)
		{
			return $this;
		}

		if ($duration < self::MIN_DURATION)
		{
			$duration = self::MIN_DURATION;
		}
		elseif ($duration > self::MAX_DURATION)
		{
			$duration = self::MAX_DURATION;
		}

		$this->duration = $duration;

		return $this;
	}

	protected function getBasePullParamsInternal(): array
	{
		return [
			'dialogId' => $this->getBaseDialogId(),
			'userId' => $this->getContext()->getUserId(),
			'userName' => $this->customUserName ?? $this->getContext()->getUser()->getName(),
			'userFirstName' => $this->customUserName ?? $this->getContext()->getUser()->getFirstName(),
			'type' => $this->actionType->value,
			'statusMessageCode' => $this->statusMessageCode,
			'duration' => $this->getDurationInMilliseconds(),
		];
	}

	protected function getSkippedUserIds(): array
	{
		return [$this->getContext()->getUserId()];
	}

	public function getDurationInMilliseconds(): ?int
	{
		return $this->duration !== null ? $this->duration * 1000 : null;
	}
}
