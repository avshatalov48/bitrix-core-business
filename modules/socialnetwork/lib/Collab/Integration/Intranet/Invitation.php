<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\Intranet;

use Bitrix\Intranet\Internals\InvitationTable;
use Bitrix\Main\Loader;

class Invitation
{
	public static function getFields(int $userId): ?array
	{
		if (!Loader::includeModule('intranet'))
		{
			return null;
		}

		$res = InvitationTable::getList([
			'filter' => [
				'USER_ID' => $userId,
			],
			'select' => ['ID', 'INVITATION_TYPE', 'INITIALIZED'],
			'limit' => 1,
		]);
		$invitationFields = $res->fetch();

		if ($invitationFields && $invitationFields['INITIALIZED'] === 'Y')
		{
			return null;
		}

		if ($invitationFields && $invitationFields['INITIALIZED'] === 'N')
		{
			InvitationTable::update($invitationFields['ID'], [
				'INITIALIZED' => 'Y',
			]);
		}
		else
		{
			$invitationFields = [];
		}

		return $invitationFields;
	}
}
