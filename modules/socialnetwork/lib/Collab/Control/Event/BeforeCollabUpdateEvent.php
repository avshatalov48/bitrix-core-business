<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;

class BeforeCollabUpdateEvent extends Event
{
	public function __construct(CollabUpdateCommand $command, Collab $collab)
	{
		$parameters = [
			'command' => $command,
			'collab' => $collab,
		];

		parent::__construct('socialnetwork', 'OnBeforeCollabUpdate', $parameters);
	}

	public function getCommand(): CollabUpdateCommand
	{
		return $this->parameters['command'];
	}

	public function getCollab(): Collab
	{
		return $this->parameters['collab'];
	}
}
