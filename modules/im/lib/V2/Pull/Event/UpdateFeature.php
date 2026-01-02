<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Pull\BaseEvent;
use Bitrix\Im\V2\Pull\EventType;

class UpdateFeature extends BaseEvent
{
	protected string $name;
	protected bool $value;

	public function __construct(string $name, bool $value)
	{
		parent::__construct();
		$this->name = $name;
		$this->value = $value;
	}

	protected function getRecipients(): array
	{
		return [];
	}

	protected function getBasePullParamsInternal(): array
	{
		return [
			'name' => $this->name,
			'value' => $this->value,
		];
	}

	protected function getType(): EventType
	{
		return EventType::UpdateFeature;
	}

	public function getTarget(): ?Chat
	{
		return null;
	}

	public function isGlobal(): bool
	{
		return true;
	}
}