<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabAddCommand;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;

class CollabAddEvent extends Event
{
	public function __construct(CollabAddCommand $command, Collab $collab)
	{
		$parameters = [
			'command' => $command,
			'collab' => $collab,
		];

		parent::__construct('socialnetwork', 'OnCollabAdd', $parameters);
	}

	public function getCommand(): CollabAddCommand
	{
		return $this->parameters['command'];
	}

	public function getCollab(): Collab
	{
		return $this->parameters['collab'];
	}
}