<?php

namespace Bitrix\Calendar\Service;

use Bitrix\Calendar\Infrastructure\Persistence\OrmSectionRepository;
use Bitrix\Calendar\Integration\Intranet\UserService;
use Bitrix\Calendar\Service\Command;

class EventCommandHandler
{
	public function __invoke(Command\Event\Base $command): Command\Result
	{
		$command
			->setSectionRepository(new OrmSectionRepository())
			->setIntranetUserService(new UserService())
			->checkPermissions();

		return $command->execute();
	}
}