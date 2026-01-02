<?php

declare(strict_types=1);

namespace Bitrix\Rest\Public\Command\Auth;

use \Bitrix\Main;

class AuthorizeUserCommand extends Main\Command\AbstractCommand
{
	public function __construct(
		public readonly int $userId,
		public readonly ?int $applicationId,
		public readonly ?Main\Type\DateTime $timePeriod = null,
	)
	{
	}

	protected function execute(): Main\Result
	{
		$result = new Main\Result();
		try
		{
			(new AuthorizeUserCommandHandler())($this);
		}
		catch (Main\AccessDeniedException | Main\ObjectNotFoundException $e)
		{
			$result->addError(new Main\Error($e->getMessage(), $e->getCode()));
		}

		return $result;
	}

	public function toArray(): array
	{
		return [
			'userId' => $this->userId,
			'applicationId' => $this->applicationId,
		];
	}
}
