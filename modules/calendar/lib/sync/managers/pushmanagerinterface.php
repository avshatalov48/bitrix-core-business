<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;
use Bitrix\Calendar\Sync\Push\Push;
use Bitrix\Calendar\Sync\Util\Result;

interface PushManagerInterface
{
	public function addConnectionPush(Connection $connection): Result;

	public function addSectionPush(SectionConnection $link): Result;

	public function renewPush(Push $pushChannel): Result;

	public function deletePush(Push $pushChannel): Result;
}
