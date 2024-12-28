<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Decorator;

use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Decorator\AbstractGroupServiceDecorator;
use Bitrix\Socialnetwork\Control\GroupResult;

class CorrectModeratorDecorator extends AbstractGroupServiceDecorator
{
	public function add(AddCommand $command): GroupResult
	{
		if ($command->getInitiatorId() === $command->getOwnerId())
		{
			return $this->source->add($command);
		}

		$initiatorAccessCode = "U{$command->getInitiatorId()}";
		$moderatorAccessCodes = $command->getModeratorMembers() ?? [];

		if (!in_array($initiatorAccessCode, $moderatorAccessCodes, true))
		{
			$moderatorAccessCodes[] = $initiatorAccessCode;
			$command->setModeratorMembers($moderatorAccessCodes);
		}

		return $this->source->add($command);
	}
}