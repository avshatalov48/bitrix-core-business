<?php

namespace Bitrix\Bizproc\Api\Service;

use Bitrix\Bizproc\Api\Data\UserService\UsersToGet;
use Bitrix\Bizproc\Api\Response\UserService\GetCurrentUserResponse;
use Bitrix\Bizproc\Api\Response\UserService\GetUsersViewResponse;
use Bitrix\Bizproc\UI\UserView;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

class UserService
{
	public function getUsersView(UsersToGet $request): GetUsersViewResponse
	{
		$currentUserResponse = $this->getCurrentUser();
		if (!$currentUserResponse->isSuccess())
		{
			$response = new GetUsersViewResponse();
			$response->addErrors($currentUserResponse->getErrors());

			return $response;
		}

		$ids = $request->getUserIds();

		if (!$ids)
		{
			return new GetUsersViewResponse();
		}

		$userIterator = UserTable::query()
			->setSelect(['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO', 'WORK_POSITION'])
			->setFilter(['ID' => $ids])
			->exec()
		;

		$response = new GetUsersViewResponse();
		while ($user = $userIterator->fetchObject())
		{
			$response->addUserView(new UserView($user));
		}

		return $response;
	}

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