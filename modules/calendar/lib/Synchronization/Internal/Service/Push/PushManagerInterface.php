<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Push;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\NotAuthorizedException;
use Bitrix\Calendar\Synchronization\Internal\Exception\Vendor\UnexpectedException;

interface PushManagerInterface
{
	/**
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 */
	public function subscribeConnection(Connection $connection): void;

	/**
	 * @throws NotAuthorizedException
	 * @throws UnexpectedException
	 */
	public function subscribeSection(SectionConnection $sectionConnection): void;

	public function resubscribeConnectionFully(Connection $connection): void;
}
