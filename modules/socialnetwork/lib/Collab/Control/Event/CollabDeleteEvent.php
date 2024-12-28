<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabDeleteCommand;

class CollabDeleteEvent extends Event
{
	public function __construct(CollabDeleteCommand $command, Collab $collab)
	{
		$parameters = [
			'command' => $command,
			'collab' => $collab,
		];

		parent::__construct('socialnetwork', 'OnCollabDelete', $parameters);
	}

	public function getCommand(): CollabDeleteCommand
	{
		return $this->parameters['command'];
	}

	public function getCollab(): Collab
	{
		return $this->parameters['collab'];
	}
}