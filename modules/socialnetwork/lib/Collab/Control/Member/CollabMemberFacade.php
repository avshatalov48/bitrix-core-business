<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Member;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Socialnetwork\Collab\Control\CollabResult;
use Bitrix\Socialnetwork\Collab\Control\CollabService;
use Bitrix\Socialnetwork\Collab\Control\Command\CollabUpdateCommand;
use Bitrix\Socialnetwork\Collab\Registry\CollabRegistry;
use Bitrix\Socialnetwork\Control\Member\AbstractMemberService;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;

class CollabMemberFacade extends AbstractMemberService
{
	protected CollabService $collabService;

	public function __construct()
	{
		parent::__construct();

		$this->collabService = ServiceLocator::getInstance()->get('socialnetwork.collab.service');
	}

	public function addImplementation(MembersCommand $command, Workgroup $group): CollabResult
	{
		$result = new CollabResult();

		$updateCommand = (new CollabUpdateCommand())
			->setAddMembers($command->getMembers())
			->setInitiatorId($command->getInitiatorId())
			->setId($command->getGroupId())
		;

		$updateResult = $this->collabService->update($updateCommand);

		return $result->merge($updateResult);
	}

	public function inviteImplementation(MembersCommand $command, Workgroup $group): Result
	{
		$updateCommand = (new CollabUpdateCommand())
			->setId($command->getGroupId())
			->setInitiatorId($command->getInitiatorId())
			->setAddInvitedMembers($command->getMembers())
		;

		return $this->collabService->update($updateCommand);
	}

	protected function addModeratorsImplementation(MembersCommand $command, Workgroup $group): CollabResult
	{
		$updateCommand = (new CollabUpdateCommand())
			->setId($command->getGroupId())
			->setInitiatorId($command->getInitiatorId())
			->setAddModeratorMembers($command->getMembers())
		;

		return $this->collabService->update($updateCommand);
	}

	protected function deleteModeratorsImplementation(MembersCommand $command, Workgroup $group): CollabResult
	{
		$updateCommand = (new CollabUpdateCommand())
			->setId($command->getGroupId())
			->setInitiatorId($command->getInitiatorId())
			->setDeleteModeratorMembers($command->getMembers())
		;

		return $this->collabService->update($updateCommand);
	}

	protected function deleteImplementation(MembersCommand $command, Workgroup $group): CollabResult
	{
		$updateCommand = (new CollabUpdateCommand())
			->setInitiatorId($command->getInitiatorId())
			->setId($command->getGroupId())
			->setDeleteMembers($command->getMembers())
		;

		return $this->collabService->update($updateCommand);
	}

	protected function getRegistry(): GroupRegistry
	{
		return CollabRegistry::getInstance();
	}
}
