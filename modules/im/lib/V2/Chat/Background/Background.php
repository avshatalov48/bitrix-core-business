<?php

namespace Bitrix\Im\V2\Chat\Background;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Chat\Param\Params;
use Bitrix\Im\V2\Pull\Event\ChatFieldsUpdate;

class Background
{
	protected int $chatId;
	protected Params $params;

	public function __construct(int $chatId)
	{
		$this->chatId = $chatId;
		$this->params = Params::getInstance($chatId);
	}

	public function get(): ?string
	{
		$background = $this->params->get(Params::BACKGROUND_ID)?->getValue();

		return $background ? (string)$background : null;
	}

	public function set(?string $value): self
	{
		if (
			!$this->chatId
			|| $this->params->get(Params::BACKGROUND_ID)?->getValue() === $value
		)
		{
			return $this;
		}

		if (!$value)
		{
			$this->params->deleteParam(Params::BACKGROUND_ID);
			$this->sendPush();

			return $this;
		}

		if (BackgroundId::tryFrom($value) === null)
		{
			return $this;
		}

		$this->params->addParamByName(Params::BACKGROUND_ID, $value);
		$this->sendPush();

		return $this;
	}

	protected function sendPush(): void
	{
		$chat = Chat::getInstance($this->chatId);
		$updateField = ['backgroundId' => $this->get()];
		(new ChatFieldsUpdate($chat, $updateField))->send();
	}

	public static function validateBackgroundId(mixed $value): ?string
	{
		if (!isset($value) || $value === '' || !is_string($value))
		{
			return null;
		}

		if (BackgroundId::tryFrom($value) === null)
		{
			return null;
		}

		return $value;
	}
}
