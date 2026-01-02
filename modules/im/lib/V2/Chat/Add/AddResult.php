<?php

namespace Bitrix\Im\V2\Chat\Add;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Result;

class AddResult extends Result
{
	public function getChat(): ?Chat
	{
		return $this->getResult()['CHAT'] ?? null;
	}

	public function setChat(Chat $chat): static
	{
		return
			$this
				->addToResult('CHAT', $chat)
				->addToResult('CHAT_ID', $chat->getChatId())
		;
	}

	public function getAlias(): ?string
	{
		return $this->getResult()['ALIAS'] ?? null;
	}

	public function setAlias(string $alias): static
	{
		return $this->addToResult('ALIAS', $alias);
	}

	public function getLink(): ?string
	{
		return $this->getResult()['LINK'] ?? null;
	}

	public function setLink(string $link): static
	{
		return $this->addToResult('LINK', $link);
	}

	public function getChatId(): ?int
	{
		return $this->getResult()['CHAT_ID'] ?? null;
	}
}