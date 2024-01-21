<?php

namespace Bitrix\Socialnetwork\Space\List\Invitation;

use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\UserToGroupTable;

final class InvitationManager
{
	public function __construct(private int $userId)
	{}

	public function getInvitations(): InvitationCollection
	{
		$queryResult = $this->getBaseQuery()->fetchAll();

		$invitations = new InvitationCollection();
		foreach ($queryResult as $invitationFields)
		{
			$invitations->add($this->buildInvitation($invitationFields));
		}

		return $invitations;
	}

	private function getBaseQuery(): Query
	{
		return UserToGroupTable::query()
			->setSelect([
				'GROUP_ID',
				'DATE_UPDATE',
				'INITIATED_BY_USER_ID',
				'SENDER_NAME' => 'SENDER.NAME',
				'SENDER_LAST_NAME' => 'SENDER.LAST_NAME',
				'SENDER_SECOND_NAME' => 'SENDER.SECOND_NAME',
			])
			->where('USER_ID', $this->userId)
			->where('ROLE', UserToGroupTable::ROLE_REQUEST)
			->where('INITIATED_BY_TYPE', UserToGroupTable::INITIATED_BY_GROUP)
			->registerRuntimeField(
				'SENDER',
				new Reference(
					'SENDER',
					UserTable::class,
					Join::on('this.INITIATED_BY_USER_ID', 'ref.ID'),
					['join_type' => Join::TYPE_INNER]
				)
			);
	}

	private function buildInvitation(array $invitationFields): Invitation
	{
		$nameFormat = \Bitrix\Main\Application::getInstance()->getContext()->getCulture()->getNameFormat();
		$invitation = (new Invitation());

		$senderData = [
			'NAME' => $invitationFields['SENDER_NAME'],
			'LAST_NAME' => $invitationFields['SENDER_LAST_NAME'],
			'SECOND_NAME' => $invitationFields['SENDER_SECOND_NAME'],
		];
		$formattedName = \CUser::FormatName($nameFormat, $senderData);
		$sender = new Sender((int)$invitationFields['INITIATED_BY_USER_ID'], $formattedName);

		$invitation
			->setSender($sender)
			->setReceiverId($this->userId)
			->setSpaceId((int)$invitationFields['GROUP_ID'])
		;
		if ($invitationFields['DATE_UPDATE'] instanceof DateTime)
		{
			$invitation->setInviteDate($invitationFields['DATE_UPDATE']);
		}

		return $invitation;
	}

	public function getInvitationBySpaceId(int $spaceId): ?Invitation
	{
		$queryResult =
			$this->getBaseQuery()
				->where('GROUP_ID', $spaceId)
				->fetch()
		;

		return !empty($queryResult) ? $this->buildInvitation($queryResult) : null;
	}
}