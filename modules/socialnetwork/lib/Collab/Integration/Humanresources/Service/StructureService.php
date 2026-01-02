<?php

namespace Bitrix\Socialnetwork\Collab\Integration\Humanresources\Service;

use Bitrix\HumanResources\Repository\NodeRelationRepository;
use Bitrix\HumanResources\Service\NodeMemberService;
use Bitrix\HumanResources\Service\NodeRelationService;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Item\NodeRelation;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Type\RelationEntityType;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\UI\EntitySelector\Converter;
use Bitrix\Socialnetwork\Collab\Control\CollabResult;
use Bitrix\Socialnetwork\Collab\Integration\Humanresources\Repository\UserToGroupRepository;
use Bitrix\Socialnetwork\Control\Member\Command\MembersCommand;
use Bitrix\Socialnetwork\UserToGroupTable;

class StructureService
{
	private readonly NodeMemberService $memberService;
	private readonly NodeRelationService $relationService;
	private readonly NodeRelationRepository $relationRepository;

	public function __construct()
	{
		Loader::requireModule('humanresources');

		$this->memberService = Container::getNodeMemberService();
		$this->relationService = Container::getNodeRelationService();
		$this->relationRepository = Container::getNodeRelationRepository();
	}

	public function handleRelationAdded(NodeRelation $relation): Result
	{
		$employees = $this->memberService->getPagedEmployees(
				$relation->nodeId,
				$relation->withChildNodes,
			)->getValues()
		;

		if (empty($employees))
		{
			return new Result();
		}

		$users = array_map(static fn(NodeMember $employee): array => ['user', $employee->entityId], $employees);
		$convertedMembers = Converter::convertToFinderCodes($users);

		return $this->addMembersForRelation($relation, $convertedMembers);
	}

	public function handleMemberAdded(NodeMember $member): CollabResult
	{
		$relations = $this->relationRepository->findRelationsByNodeIdAndRelationType(
			$member->nodeId,
			RelationEntityType::COLLAB,
		);

		$convertedMembers = Converter::convertToFinderCodes([['user', $member->entityId]]);

		$result = new CollabResult();
		foreach ($relations as $relation)
		{
			$result->merge($this->addMembersForRelation($relation, $convertedMembers));
		}

		return $result;
	}

	public function handleRelationDeleted(NodeRelation $relation): Result
	{
		$employees = $this->memberService->getPagedEmployees(
				$relation->nodeId,
				$relation->withChildNodes,
			)->getValues()
		;

		$users = array_map(static fn(NodeMember $employee): int => $employee->entityId, $employees);

		return $this->deleteMembersForRelation($relation, $users);
	}

	public function handleMemberDeleted(NodeMember $member): CollabResult
	{
		$relations = $this->relationRepository->findRelationsByNodeIdAndRelationType(
			$member->nodeId,
			RelationEntityType::COLLAB,
		);

		$result = new CollabResult();
		foreach ($relations as $relation)
		{
			$result->merge($this->deleteMembersForRelation($relation, [$member->entityId]));
		}

		return $result;
	}

	protected function addMembersForRelation(NodeRelation $relation, array $convertedMembers): Result
	{
		$command = (new MembersCommand())
			->setMembers($convertedMembers)
			->setInitiatorId($relation->createdBy)
			->setGroupId($relation->entityId)
			->setInitiatedByType(UserToGroupTable::INITIATED_BY_STRUCTURE)
		;

		return ServiceLocator::getInstance()->get('socialnetwork.collab.member.facade')->add($command);
	}

	protected function deleteMembersForRelation(NodeRelation $relation, array $employees): Result
	{
		$filteredUsers = $this->relationService->getUsersNotInRelation(
			RelationEntityType::COLLAB,
			$relation->entityId,
			$employees
		);

		$groupUsers = ServiceLocator::getInstance()
			->get(UserToGroupRepository::class)
			->getForRelation($relation, $filteredUsers)
		;

		$filteredUsers = array_column($groupUsers, 'USER_ID');

		if (empty($filteredUsers))
		{
			return new Result();
		}

		$users = array_map(static fn(int $userId): array => ['user', $userId], $filteredUsers);
		$convertedMembers = Converter::convertToFinderCodes($users);

		$command = (new MembersCommand())
			->setMembers($convertedMembers)
			->setInitiatorId($relation->createdBy)
			->setGroupId($relation->entityId)
		;

		return ServiceLocator::getInstance()->get('socialnetwork.collab.member.facade')->delete($command);
	}
}
