<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Attribute\Field;

use CSocNetGroupSubject;

class SubjectIdField implements FieldInterface
{
	private static ?int $subjectId = null;

	public function __construct(
		private readonly string $toField,
	)
	{

	}

	public function getValue(mixed $value): mixed
	{
		return $value?? $this->getDefaultSubjectId();
	}

	public function getFieldName(): string
	{
		return $this->toField;
	}

	protected function getDefaultSubjectId(): int
	{
		if (static::$subjectId === null)
		{
			$subject = CSocNetGroupSubject::GetList(
				["SORT"=>"ASC", "NAME" => "ASC"],
				["SITE_ID" => SITE_ID],
				false,
				false,
				["ID", "NAME"],
			)->fetch();

			static::$subjectId = (int)($subject['ID'] ?? 0);
		}

		return static::$subjectId;
	}
}