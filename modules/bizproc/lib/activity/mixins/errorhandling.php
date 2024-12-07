<?php

namespace Bitrix\Bizproc\Activity\Mixins;

use Bitrix\Main\ErrorCollection;

trait ErrorHandling
{
	protected static $errors;

	public static function getErrors(): array
	{
		return self::$errors->toArray();
	}

	public static function hasErrors(): bool
	{
		if (self::$errors instanceof ErrorCollection)
		{
			return !self::$errors->isEmpty();
		}

		return false;
	}
}