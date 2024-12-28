<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Mapper\Default\Field;

use Bitrix\Socialnetwork\ValueObjectInterface;
use CSocNetGroupSubject;

class SubjectId implements ValueObjectInterface
{
	private static ?int $subjectId = null;

	public function getValue(): int
	{
		return $this->getDefaultSubjectId();
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