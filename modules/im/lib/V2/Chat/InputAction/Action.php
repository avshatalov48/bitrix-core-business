<?php

namespace Bitrix\Im\V2\Chat\InputAction;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Pull\Event\InputActionNotify;
use Bitrix\Im\V2\Pull\Event\StartWriting;
use Bitrix\Im\V2\Result;

class Action
{
	use ContextCustomer;

	protected Chat $chat;
	protected Type $type;
	protected ?string $customUserName = null;
	protected bool $byEvent = false;
	protected bool $linesSilentMode = false;
	protected ?string $statusMessageCode = null;
	protected ?int $duration = null;

	public function __construct(Chat $chat, Type $type)
	{
		$this->chat = $chat;
		$this->type = $type;
	}

	public function notify(): Result
	{
		$resultSend = $this->sendPull();
		if (!$resultSend->isSuccess())
		{
			return $resultSend;
		}

		$resultSendLegacy = $this->sendLegacyPull();
		if (!$resultSendLegacy->isSuccess())
		{
			return $resultSendLegacy;
		}

		$this->sendLegacyEvent();

		return new Result();
	}

	public function setCustomUserName(string $customUserName): self
	{
		$this->customUserName = $customUserName;

		return $this;
	}

	public function setByEvent(bool $byEvent): self
	{
		$this->byEvent = $byEvent;

		return $this;
	}

	public function setLinesSilentMode(bool $linesSilentMode): self
	{
		$this->linesSilentMode = $linesSilentMode;

		return $this;
	}

	public function setStatusMessageCode(?string $statusMessageCode): self
	{
		$this->statusMessageCode = $statusMessageCode;

		return $this;
	}

	public function setDuration(?int $duration): self
	{
		$this->duration = $duration;

		return $this;
	}

	private function sendPull(): Result
	{
		$pull =
			(new InputActionNotify($this->chat, $this->type))
				->setContext($this->context)
				->setCustomUserName($this->customUserName)
				->setStatusMessageCode($this->statusMessageCode)
				->setDuration($this->duration)
		;

		return $pull->send();
	}

	private function sendLegacyPull(): Result
	{
		if ($this->type !== Type::Writing)
		{
			return new Result(); // send legacy pull only for writing action
		}

		$pull = (new StartWriting($this->chat))
			->setContext($this->context)
			->setCustomUserName($this->customUserName)
		;

		return $pull->send();
	}

	private function sendLegacyEvent(): void
	{
		if ($this->type !== Type::Writing)
		{
			return; // send legacy event only for writing action
		}

		$eventParams = [[
			'DIALOG_ID' => $this->chat->getDialogId(),
			'CHAT' => $this->getChatArray(),
			'RELATION' => $this->getRelationsArray(),
			'USER_ID' => $this->getContext()->getUserId(),
			'USER_NAME' => $this->customUserName ?? $this->getContext()->getUser()->getName(),
			'BY_EVENT' => $this->byEvent,
			'LINES_SILENT_MODE' => $this->linesSilentMode,
		]];

		foreach(GetModuleEvents("im", "OnStartWriting", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, $eventParams);
		}
	}

	private function getRelationsArray(): array
	{
		$relationsArray = [];
		if ($this->chat instanceof Chat\PrivateChat)
		{
			return [];
		}

		foreach ($this->chat->getRelations() as $relation)
		{
			$relationArray = $relation->toArray();
			$relationArray['EXTERNAL_AUTH_ID'] = $relation->getUser()->getExternalAuthId();
			$relationsArray[] = static::convertBoolToChar($relationArray);
		}

		return $relationsArray;
	}

	private function getChatArray(): array
	{
		if ($this->chat instanceof Chat\PrivateChat)
		{
			return [];
		}

		return static::convertBoolToChar($this->chat->toArray());
	}

	/**
	 * The legacy method retrieved event objects directly from the database,
	 * so boolean values were represented as chars.
	 *
	 * @param array $array
	 * @return array
	 */
	private static function convertBoolToChar(array $array): array
	{
		foreach ($array as $field => $value)
		{
			if (is_bool($value))
			{
				$array[$field] = $value ? 'Y' : 'N';
			}
		}

		return $array;
	}
}
