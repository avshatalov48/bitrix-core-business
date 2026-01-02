<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Call\JwtCall;
use Bitrix\Im\V2\Rest\PopupDataItem;
use Bitrix\Main\Loader;

class CallToken implements PopupDataItem
{
	protected ?int $chatId = null;
	protected string $token = '';

	public function __construct(?int $chatId)
	{
		if ($chatId > 0)
		{
			$this->chatId = $chatId;
		}
	}

	public function update(): void
	{
		if ($this->chatId > 0 && Loader::includeModule('call'))
		{
			$this->token = JwtCall::updateCallToken((int)$this->chatId);
		}
	}

	public function getToken(): string
	{
		if (
			empty($this->token)
			&& $this->chatId > 0
			&& Loader::includeModule('call')
		)
		{
			$this->token = JwtCall::getCallToken($this->chatId);
		}

		return $this->token;
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	public static function getRestEntityName(): string
	{
		return 'callInfo';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'token' => $this->getToken(),
			'chatId' => $this->chatId,
		];
	}

	public function merge(PopupDataItem $item): PopupDataItem
	{
		return $this;
	}
}