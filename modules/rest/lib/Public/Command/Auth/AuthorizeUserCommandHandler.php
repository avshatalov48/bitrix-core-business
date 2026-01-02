<?php

declare(strict_types=1);

namespace Bitrix\Rest\Public\Command\Auth;

use Bitrix\Main;
use Bitrix\Rest\Internal\Repository;

class AuthorizeUserCommandHandler
{
	protected Repository\Auth\AuthorizationRepository $repo;
	protected bool $needToLog = true;
	protected \CUser $userAuthorizer;

	public function __construct(
		?Repository\Auth\AuthorizationRepository $repo = null,
		?bool $needToLog = null,
		?\CUser $userAuthorizer = null,
	)
	{
		$this->repo = $repo ?? new Repository\Auth\AuthorizationRepository();
		$this->needToLog = $needToLog ?? Main\Config\Option::get('main', 'event_log_login_success', 'N') === 'Y';
		if ($userAuthorizer)
		{
			$this->userAuthorizer = $userAuthorizer;
		}
		else
		{
			global $USER;

			if (!is_object($USER))
			{
				throw new Main\ObjectNotFoundException('user object not found');
			}

			$this->userAuthorizer = $USER;
		}
	}

	public function __invoke(AuthorizeUserCommand $command): void
	{
		$userId = $command->userId;
		$applicationId = (int)$command->applicationId;
		$currentHour = $command->timePeriod ?? new Main\Type\DateTime(date('Y-m-d H'), 'Y-m-d H');

		if (($userId <= 0 || $this->userAuthorizer->Authorize($userId, false, false, $applicationId)))
		{
			setSessionExpired(true);

			if ($this->needToLog)
			{
				$this->repo->saveAuthorization($userId, $applicationId, $currentHour);
			}
		}
		else
		{
			throw new Main\AccessDeniedException();
		}
	}
}
