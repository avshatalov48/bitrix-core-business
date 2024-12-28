<?php

namespace Bitrix\Im\V2\Recent\Initializer;

enum StageType: string
{
	case Target = 'TARGET';
	case Other = 'OTHER';

	public function getNext(): ?self
	{
		return match ($this)
		{
			self::Target => self::Other,
			self::Other => null,
		};
	}

	public static function getFirst(): self
	{
		return self::Target;
	}
}
