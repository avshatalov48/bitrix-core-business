<?php

namespace Bitrix\Socialnetwork\Integration\HumanResources;

use Bitrix\HumanResources\Item\Node;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Repository\NodeRepository;
use Bitrix\HumanResources\Service\Container;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Closure;

class AccessCodeConverter
{
	/**
	 * @var string[]
	 */
	private array $accessCodes;
	private NodeRepository $nodeRepository;

	/**
	 * @throws LoaderException
	 */
	public function __construct(string ...$accessCodes)
	{
		if (!Loader::includeModule('humanresources'))
		{
			throw new LoaderException('Humanresources is not loaded');
		}

		$this->accessCodes = $accessCodes;
		$this->nodeRepository = Container::getNodeRepository();
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @return int[]
	 */
	public function getUserIds(): array
	{
		$userIds = $this->getDepartmentUserIds();
		$userIds = array_merge($userIds, $this->getUsers()->getAccessCodeIdList());

		return array_unique($userIds);
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 * @throws LoaderException
	 * @throws ObjectPropertyException
	 * @return int[]
	 */
	private function getDepartmentUserIds(): array
	{
		$departmentIds = $this->getDepartments()->accessCodes;
		$departmentIdsWithoutRecursive = array_map(
			static fn(string $departmentId): string => str_replace("DR", "D", $departmentId),
			$departmentIds,
		);

		$nodeCollection = $this->nodeRepository->findAllByAccessCodes($departmentIdsWithoutRecursive);

		$userIds = [];

		/**
		 * @var Node $node
		 */
		foreach ($nodeCollection as $node)
		{
			$departmentId = $this->getEntityIdByAccessCode($node->accessCode);
			$withAllChildNodes = in_array("DR${departmentId}", $this->accessCodes, true);

			$memberCollectionByNode = Container::getNodeMemberService()->getAllEmployees(
				$node->id,
				$withAllChildNodes,
			);

			$memberIdsByNode = array_map(
				static fn(NodeMember $member) => $member->entityId,
				$memberCollectionByNode->getItemMap()
			);

			$userIds = [...$userIds, ...$memberIdsByNode];
		}

		return array_unique($userIds);
	}

	private function getEntityIdByAccessCode(string $accessCode): int
	{
		return (new AccessCode($accessCode))->getEntityId();
	}

	public function getAccessCodeIdList(): array
	{
		$ids = array_map(static fn(string $code): int => (new AccessCode($code))->getEntityId(), $this->accessCodes);

		return array_filter(array_unique($ids));
	}

	/**
	 * @throws LoaderException
	 */
	public function getUsers(): static
	{
		$users = array_filter($this->accessCodes, $this->getFilter(AccessCode::TYPE_USER));
		return new static(...$users);
	}

	/**
	 * @throws LoaderException
	 */
	public function getDepartments(): static
	{
		$departments = array_filter($this->accessCodes, $this->getFilter(AccessCode::TYPE_DEPARTMENT));
		return new static(...$departments);
	}

	private function getFilter(string $entityType): Closure
	{
		return static fn (string $code): bool => (new AccessCode($code))->getEntityType() === $entityType;
	}
}