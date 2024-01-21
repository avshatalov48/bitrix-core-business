<?php

namespace Bitrix\Bizproc\Api\Response\UserService;

use Bitrix\Bizproc\Result;
use Bitrix\Bizproc\Api\Response\Error;
use Bitrix\Main\Engine\CurrentUser;

class GetCurrentUserResponse extends Result
{
	public static function createUnauthorizedError(): static
	{
		return static::createError(Error::fromCode(Error::UNAUTHORIZED));
	}

	public function getUser(): ?CurrentUser
	{
		return $this->data['user'] ?? null;
	}
}