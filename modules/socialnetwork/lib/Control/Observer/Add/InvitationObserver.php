<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Observer\Add;

use Bitrix\Main\Type\Collection;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Observer\ObserverInterface;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\UserToGroupTable;
use CSocNetUserToGroup;

class InvitationObserver implements ObserverInterface
{
	public function update(AbstractCommand $command, Workgroup $entity): void
	{
		if (!$command instanceof AddCommand)
		{
			return;
		}

		$members = $this->getMembers($entity);

		foreach ($command->members as $memberId)
		{
			if (in_array($memberId, $members, true))
			{
				continue;
			}

			CSocNetUserToGroup::SendRequestToJoinGroup(
				$command->initiatorId,
				$memberId,
				$entity->getId(),
				'Join to group!'
			);
		}
	}

	private function getMembers(Workgroup $entity): array
	{
		$query = UserToGroupTable::query()
			->setSelect(['USER_ID'])
			->where('GROUP_ID', $entity->getId());

		$memberIds =  array_column($query->exec()->fetchAll(), 'USER_ID');

		Collection::normalizeArrayValuesByInt($memberIds, false);

		return $memberIds;
	}
}