<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Response\UserService\GetCurrentUserResponse;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;

class UserService
{
	public function getCurrentUser(): GetCurrentUserResponse
	{
		$currentUser = CurrentUser::get();

		if (!$this->isAuthorised((int)$currentUser->getId()))
		{
			return GetCurrentUserResponse::createUnauthorizedError();
		}

		return GetCurrentUserResponse::createOk(['user' => $currentUser]);
	}

	public function isAuthorised(int $userId): bool
	{
		return $userId > 0;
	}

	public function isCurrentUserPortalAdmin(): bool
	{
		$result = $this->getCurrentUser();

		return (
			$result->isSuccess()
			&& (
				$result->getUser()?->isAdmin()
				|| (
					Loader::includeModule('bitrix24')
					&& \CBitrix24::IsPortalAdmin($result->getUser()?->getId())
				)
			)
		);
	}
}