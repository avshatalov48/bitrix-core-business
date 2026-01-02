<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Public\Command\Push\Office365;

use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\PushException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\AuthorizationException;
use Bitrix\Main\Command\AbstractCommand;
use Bitrix\Main\Result;

class SubscribeSectionCommand extends AbstractCommand
{
	public function __construct(public readonly SectionConnection $sectionConnection)
	{
	}

	/**
	 * @throws PushException
	 * @throws AuthorizationException
	 */
	protected function execute(): Result
	{
		$result = new Result();

		(new SubscribeSectionCommandHandler())($this);

		return $result;
	}
}
