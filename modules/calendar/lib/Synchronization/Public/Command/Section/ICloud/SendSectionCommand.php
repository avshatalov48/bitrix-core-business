<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Section\ICloud;

use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Synchronization\Internal\Exception\SynchronizerException;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\Result;

class SendSectionCommand extends AbstractCommand
{
	public function __construct(public readonly Section $section)
	{
	}

	public function toArray(): array
	{
		return [
			'section' => $this->section->toArray(),
		];
	}

	/**
	 * @throws SynchronizerException
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	protected function execute(): Result
	{
		(new SendSectionCommandHandler())($this);

		return new Result();
	}
}
