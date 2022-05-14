<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\Counter\Push;

use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Internals\Counter;

class WorkgroupRequestsInSender extends WorkgroupRequestsSender
{
	protected function getWorkgroupCounters($workgroupId): array
	{
		return [
			Counter\CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN => Counter\CounterController::getValue(Counter\CounterDictionary::COUNTER_WORKGROUP_REQUESTS_IN, $workgroupId),
		];
	}

	protected function getRoleFilterValue($initiatePermsValue): array
	{
		switch ($initiatePermsValue)
		{
			case UserToGroupTable::ROLE_MODERATOR:
				$roleFilterValue = [ UserToGroupTable::ROLE_OWNER, UserToGroupTable::ROLE_MODERATOR ] ;
				break;
			default:
				$roleFilterValue = [ UserToGroupTable::ROLE_OWNER ];
		}

		return $roleFilterValue;
	}
}
