<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Entity;

use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;

class Promotion implements RestConvertible
{
	#[NotEmpty]
	private string $id;
	#[Validatable]
	private PromotionParams $params;

	public function __construct(string $id, array $params = [])
	{
		$this->id = $id;
		$this->params = PromotionParams::createFromArray($params);
	}

	public function setChatId(int $chatId): self
	{
		$this->params->setChatId($chatId);

		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->params->getChatId();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public static function getRestEntityName(): string
	{
		return 'promotion';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->id,
			'params' => $this->params->toRestFormat(),
		];
	}
}
