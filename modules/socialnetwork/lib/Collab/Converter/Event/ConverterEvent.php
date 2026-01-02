<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Converter\Event;

use Bitrix\Main\Event;
use Bitrix\Socialnetwork\Collab\Converter\Command\AbstractConverterCommand;
use Bitrix\Socialnetwork\Item\Workgroup;

class ConverterEvent extends Event
{
	public function __construct(AbstractConverterCommand $command, Workgroup $entityBefore, Workgroup $entityAfter)
	{
		$parameters = [
			'command' => $command,
			'entityBefore' => $entityBefore,
			'entityAfter' => $entityAfter,
		];

		parent::__construct('socialnetwork', 'OnWorkgroupConvert', $parameters);
	}

	public function getCommand(): AbstractConverterCommand
	{
		return $this->parameters['command'];
	}

	public function getEntityBefore(): Workgroup
	{
		return $this->parameters['entityBefore'];
	}

	public function getEntityAfter(): Workgroup
	{
		return $this->parameters['entityAfter'];
	}
}