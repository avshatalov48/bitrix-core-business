<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Entity;

use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\Rule\Min;

class PromotionParams implements RestConvertible
{
	#[Min(0)]
	private ?int $chatId;

	public static function createFromArray(array $params): self
	{
		$promotionParams = new self();

		if (isset($params['chatId']))
		{
			$promotionParams->setChatId((int)$params['chatId']);
		}

		return $promotionParams;
	}

	public function setChatId(int $chatId): self
	{
		$this->chatId = $chatId;

		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->chatId ?? null;
	}

	public static function getRestEntityName(): string
	{
		return 'promotionParams';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'chatId' => $this->getChatId(),
		];
	}
}
