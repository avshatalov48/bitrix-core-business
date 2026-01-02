<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Collab\Entity;

use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Validation\Rule\PositiveNumber;

class FirstAddedMemberData implements Arrayable
{
	public function __construct(
		#[PositiveNumber]
		public readonly int $collabId,
		public readonly ?DateTime $addedDate,
		public readonly bool $isFirstMemberAdded = true,
	)
	{

	}

	public static function createFromArray(array $data): self
	{
		return new self(
			$data['collabId'],
			$data['date'] ?? null,
			$data['isFirstMemberAdded'] ?? true,
		);
	}

	public function toArray()
	{
		return [
			'collabId' => $this->collabId,
			'date' => $this->addedDate,
			'isFirstMemberAdded' => $this->isFirstMemberAdded,
		];
	}
}
