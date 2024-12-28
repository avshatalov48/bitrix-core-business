<?php

namespace Bitrix\Socialnetwork\Control\Handler\Trait;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Socialnetwork\Control\Command\AbstractCommand;
use Bitrix\Socialnetwork\Control\Command\AddCommand;
use Bitrix\Socialnetwork\Control\Command\UpdateCommand;
use Bitrix\Socialnetwork\Control\GroupResult;
use Bitrix\Socialnetwork\Control\Handler\HandlerResult;
use Bitrix\Socialnetwork\Control\Member\Trait\GetMembersTrait;
use Bitrix\Socialnetwork\Integration\HumanResources\AccessCodeConverter;
use Bitrix\Socialnetwork\Item\Workgroup;
use CSocNetUserToGroup;

trait SendInvitationTrait
{
	use GetMembersTrait;

	/**
	 * @throws ObjectPropertyException
	 * @throws LoaderException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function send(AbstractCommand $command, Workgroup $entity): HandlerResult
	{
		$handlerResult = new HandlerResult();
		if (!$this->isCommandCorrect($handlerResult, $command))
		{
			return $handlerResult;
		}

		$currentMembers = $this->getMemberIds($entity->getId());
		$membersByCommand = (new AccessCodeConverter(...$command->getMembers()))
			->getUsers()
			->getUserIds()
		;

		foreach ($membersByCommand as $memberId)
		{
			if (in_array($memberId, $currentMembers, true))
			{
				continue;
			}

			$requestResult = CSocNetUserToGroup::SendRequestToJoinGroup(
				$command->getInitiatorId(),
				$memberId,
				$entity->getId(),
				Loc::getMessage('SOCIALNETWORK_JOIN_PROJECT')
			);

			if (!$requestResult)
			{
				$handlerResult->addApplicationError();
			}
		}

		if (!empty($membersByCommand))
		{
			$handlerResult->setGroupChanged();
		}

		return $handlerResult;
	}

	private function isCommandCorrect(GroupResult $handlerResult, AbstractCommand $command): bool
	{
		if (!($command instanceof AddCommand || $command instanceof UpdateCommand))
		{
			$handlerResult->addError(
				new Error(Loc::getMessage('SOCIALNETWORK_SEND_INVITATION_COMMAND_TYPE_INCORRECT'))
			);

			return false;
		}

		if (empty($command->getMembers()))
		{
			return false;
		}

		return true;
	}
}