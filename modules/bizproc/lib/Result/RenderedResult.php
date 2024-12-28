<?php

namespace Bitrix\Bizproc\Result;

class RenderedResult
{
	public const NO_RESULT = 0;
	public const BB_CODE_RESULT = 1;
	public const USER_RESULT = 2;

	public const NO_RIGHTS = 3;


	public function __construct(
		public readonly string $text,
		public readonly int $status,
	) {}

	public static function makeNoRights(): RenderedResult
	{

		return new self('', self::NO_RIGHTS);
	}

	public static function makeNoResult(): RenderedResult
	{

		return new self('', self::NO_RESULT);
	}
}
