<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command\ValueObject;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\ValueObjectInterface;
use CSocNetGroupSubject;

class SubjectId implements ValueObjectInterface, CreateWithDefaultValueInterface, CreateObjectInterface
{
	#[PositiveNumber]
	private int $subjectId;

	public static function create(mixed $data): static
	{
		$value = new static();

		$value->subjectId = $data;

		return $value;
	}

	public static function createWithDefaultValue(): static
	{
		$value = new static();

		$subject = CSocNetGroupSubject::GetList(
			["SORT"=>"ASC", "NAME" => "ASC"],
			["SITE_ID" => SITE_ID],
			false,
			false,
			["ID", "NAME"],
		)->fetch();

		$value->subjectId = (int)($subject['ID'] ?? 0);

		return $value;
	}

	public function getValue(): int
	{
		return $this->subjectId;
	}
}