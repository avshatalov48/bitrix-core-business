<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;

class CollabUpdateEvent extends Event
{
	public function __construct(CollabUpdateCommand $command, Collab $collabBefore, Collab $collabAfter)
	{
		$parameters = [
			'command' => $command,
			'collabBefore' => $collabBefore,
			'collabAfter' => $collabAfter
		];

		parent::__construct('socialnetwork', 'OnCollabUpdate', $parameters);
	}

	public function getCommand(): CollabUpdateCommand
	{
		return $this->parameters['command'];
	}

	public function getCollabBefore(): Collab
	{
		return $this->parameters['collabBefore'];
	}

	public function getCollabAfter(): Collab
	{
		return $this->parameters['collabAfter'];
	}
}