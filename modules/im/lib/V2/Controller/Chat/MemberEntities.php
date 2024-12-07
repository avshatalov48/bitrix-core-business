<?php

namespace Bitrix\Im\V2\Controller\Chat;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Controller\BaseController;
use Bitrix\Im\V2\Controller\Filter\CheckActionAccess;
use Bitrix\Im\V2\Integration\HumanResources\Structure;
use Bitrix\Im\V2\Relation\Reason;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\UI\EntitySelector\Converter;

class MemberEntities extends BaseController
{
	protected const LIMIT = 1000;

	/**
	 * @restMethod im.v2.Chat.MemberEntities.list
	 */
	public function listAction(Chat $chat): ?array
	{
		$relations = $chat->getRelationByReason(Reason::DEFAULT);
		$userCount = $relations->count();

		$users = [];
		if ($userCount < self::LIMIT)
		{
			foreach ($relations->getUserIds() as $userId)
			{
				$user = \Bitrix\Im\V2\Entity\User\User::getInstance($userId);
				if (!$user->isBot())
				{
					$users[] = ['user', $userId];
				}
			}
		}

		$departments = Converter::convertFromFinderCodes((new Structure($chat))->getChatDepartments());
		foreach ($departments as $key => $department)
		{
			if (is_numeric($department[1]))
			{
				$departments[$key][1] = (int)$department[1];
			}
		}

		return [
			'memberEntities' => array_merge($users, $departments),
			'userCount' => $userCount,
			'areUsersCollapsed' => $userCount >= self::LIMIT,
		];
	}
}
