<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

use Bitrix\HumanResources\Compatibility\Utils\DepartmentBackwardAccessCode;
use Bitrix\HumanResources\Contract\Repository\NodeRepository;
use Bitrix\HumanResources\Contract\Repository\StructureRepository;
use Bitrix\HumanResources\Contract\Service\NodeMemberService;
use Bitrix\HumanResources\Contract\Service\NodeService;
use Bitrix\HumanResources\Enum\DepthLevel;
use Bitrix\HumanResources\Enum\NodeActiveFilter;
use Bitrix\HumanResources\Item\Collection\NodeCollection;
use Bitrix\HumanResources\Item\Node;
use Bitrix\HumanResources\Item\NodeMember;
use Bitrix\HumanResources\Service\Container;
use Bitrix\Im\V2\Integration\HumanResources\Structure;
use Bitrix\Main\Config\Option;

class Department extends BaseDepartment
{
	protected static ?IDepartment $instance = null;

	protected NodeRepository $nodeRepository;
	protected NodeMemberService $nodeMemberService;
	protected StructureRepository $structureRepository;
	protected NodeService $nodeService;

	private function __construct()
	{
		$this->nodeRepository = Container::getNodeRepository();
		$this->nodeMemberService = Container::getNodeMemberService();
		$this->structureRepository = Container::getStructureRepository();
		$this->nodeService = Container::getNodeService();
	}

	public static function getInstance(): IDepartment
	{
		if (self::$instance !== null)
		{
			return self::$instance;
		}

		if (!Structure::isSyncAvailable() || Option::get('im', 'old_department_enabled', 'N') === 'Y')
		{
			self::$instance = new OldDepartment();

			return self::$instance;
		}

		self::$instance = new self();

		return self::$instance;
	}

	public function getTopId(): ?int
	{
		if (self::$wasSearchedTopId)
		{
			return self::$topId;
		}

		self::$wasSearchedTopId = true;

		$rootNode = $this->getRootNode();
		if ($rootNode === null)
		{
			return null;
		}

		preg_match('/D(\d+)/', $rootNode->accessCode ?? '', $matches);
		self::$topId = isset($matches[1]) ? (int)$matches[1] : null;

		return self::$topId;
	}

	public function getList(): array
	{
		if (!empty($this->structureDepartments))
		{
			return $this->structureDepartments;
		}

		$rootNode = $this->getRootNode();
		if ($rootNode === null)
		{
			return [];
		}

		$nodes = $this->nodeRepository->getChildOf($rootNode, DepthLevel::FULL, NodeActiveFilter::ONLY_ACTIVE);

		foreach ($nodes as $node)
		{
			$department = $this->formatNode($node);
			$this->structureDepartments[$department->id] = $department;
		}

		return $this->structureDepartments;
	}

	public function getListByIds(array $ids): array
	{
		if (!empty($this->structureDepartments))
		{
			return $this->filterDepartmentsByIds($this->structureDepartments, $ids);
		}

		$departments = [];
		$nodeCollection = $this->getNodesByIds($ids);

		foreach ($nodeCollection as $node)
		{
			$department = $this->formatNode($node);
			$departments[$department->id] = $department;
		}

		return $departments;
	}

	public function getListByXml(string $xmlId): array
	{
		$structure = $this->structureRepository->getByXmlId(\Bitrix\HumanResources\Item\Structure::DEFAULT_STRUCTURE_XML_ID);
		if ($structure === null)
		{
			return [];
		}

		$departments = [];
		foreach ($this->nodeRepository->findAllByXmlId($xmlId, NodeActiveFilter::ONLY_ACTIVE) as $node)
		{
			$department = $this->formatNode($node);
			$departments[$department->id] = $department;
		}

		return $departments;
	}

	protected function formatNode(Node $node): Entity
	{
		$parent = $this->nodeRepository->getById($node->parentId);
		$parentId = DepartmentBackwardAccessCode::extractIdFromCode($parent?->accessCode);

		$headMembers = $this->nodeMemberService->getDefaultHeadRoleEmployees($node->id);
		$id = DepartmentBackwardAccessCode::extractIdFromCode($node->accessCode);

		return new Entity(
			name: $node->name,
			headUserID: $headMembers->getIterator()->current()?->entityId ?? 0,
			id: $id,
			depthLevel: $node->depth,
			parent: $parentId,
			nodeId: $node->id
		);
	}

	public function getEmployeeIdsWithLimit(array $ids, int $limit = 50): array
	{
		$employees = $managers = [];
		$nodeCollection = $this->getNodesByIds($ids);
		$headRole = Container::getRoleRepository()->findByXmlId(NodeMember::DEFAULT_ROLE_XML_ID['HEAD'])?->id;

		$count = 0;
		foreach ($nodeCollection as $node)
		{
			$memberCollection = $this->nodeMemberService->getPagedEmployees(
				$node->id,
				false,
				0,
				$limit - $count
			);

			foreach ($memberCollection->getItemMap() as $member)
			{
				if (in_array($headRole, $member->roles ?? [], true))
				{
					$managers[$member->entityId] = $member->entityId;
				}
				else
				{
					$employees[$member->entityId] = $member->entityId;
				}
			}

			$count = count($managers + $employees);

			if ($count >= $limit)
			{
				break;
			}
		}

		return array_slice($managers + $employees, 0, $limit);
	}

	protected function getNodesByIds(array $ids): NodeCollection
	{
		$codes = array_map(
			static fn($id) => DepartmentBackwardAccessCode::makeById($id),
			$ids
		);

		return $this->nodeRepository->findAllByAccessCodes($codes);
	}

	protected function getRootNode(): ?Node
	{
		$structure = $this->structureRepository->getByXmlId(\Bitrix\HumanResources\Item\Structure::DEFAULT_STRUCTURE_XML_ID);
		if ($structure === null)
		{
			return null;
		}

		return $this->nodeRepository->getRootNodeByStructureId($structure->id);
	}

	protected function filterDepartmentsByIds(array $departments, array $ids): array
	{
		$departmentByIds = [];

		foreach ($departments as $department)
		{
			if (in_array($department->id, $ids, true))
			{
				$departmentByIds[$department->id] = $department;
			}
		}

		return $departmentByIds;
	}
}
