<?php

namespace Bitrix\UI\Controller;

use Bitrix\HumanResources\Enum\DepthLevel;
use Bitrix\HumanResources\Type\NodeEntityType;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Builder\Structure\Filter\Column\IdFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\Column\Node\NodeTypeFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\NodeFilter;
use Bitrix\HumanResources\Builder\Structure\NodeDataBuilder;
use Bitrix\HumanResources\Enum\Direction;
use \Bitrix\HumanResources\Type\AccessCodeType;
use \Bitrix\HumanResources\Config\Storage;

class AccessRights extends Controller
{
	protected function getDefaultPreFilters(): array
	{
		$prefilters = parent::getDefaultPreFilters();
		$prefilters[] = new ActionFilter\Scope(ActionFilter\Scope::AJAX);

		return $prefilters;
	}

	public function getUserSortConfigAction(string $name): ?array
	{
		$userSortConfig =  \Bitrix\UI\AccessRights\V2\Config::getInstanceByContext($name)->getUserGroupsSortConfig();

		if (!is_array($userSortConfig))
		{
			return null;
		}

		return $this->normalizeUserSortConfig($userSortConfig);
	}

	public function setUserSortConfigAction(string $name, array $userSortConfig = []): bool
	{
		$newConfig = $this->normalizeUserSortConfig($userSortConfig);

		return \Bitrix\UI\AccessRights\V2\Config::getInstanceByContext($name)->updateConfig($newConfig);
	}

	private function normalizeUserSortConfig(array $config): array
	{
		$normalizeConfig = [];

		foreach ($config as $groupId => $sort)
		{
			if (is_numeric($sort))
			{
				$normalizeConfig[$groupId] = (int)$sort;
			}
		}

		return $normalizeConfig;
	}

	public function getAccessCodesAction(string $id, string $moduleId = '', array $params = []): array
	{
		$accessCodes = [$id];

		$perms = \Bitrix\UI\AccessRights\V2\Permission::getInstance($moduleId, $params);
		if (!$perms || !$perms->canUpdate())
		{
			return $accessCodes;
		}

		$accessCodeObject = new \Bitrix\Main\Access\AccessCode($id);

		if ($accessCodeObject->getEntityType() === \Bitrix\Main\Access\AccessCode::TYPE_USER)
		{
			$accessCodes = \CAccess::GetUserCodesArray($accessCodeObject->getEntityId()) ?? [];

			return $this->filterAccessCodes($accessCodes);

		}

		if ($accessCodeObject->getEntityType() === \Bitrix\Main\Access\AccessCode::TYPE_DEPARTMENT
		|| $accessCodeObject->getEntityType() === \Bitrix\Main\Access\AccessCode::TYPE_STRUCTURE_DEPARTMENT)
		{
			if (!\Bitrix\Main\Loader::includeModule('humanresources')
				|| !Storage::instance()->isCompanyStructureConverted())
			{
				return $accessCodes;
			}

			$accessCodes = array_merge($accessCodes, $this->getStructureAccessCodes($id, NodeEntityType::DEPARTMENT));

			return array_values(array_unique($accessCodes));
		}

		if ($accessCodeObject->getEntityType() === \Bitrix\Main\Access\AccessCode::TYPE_STRUCTURE_TEAM)
		{
			if (!\Bitrix\Main\Loader::includeModule('humanresources')
				|| !Storage::instance()->isCompanyStructureConverted())
			{
				return $accessCodes;
			}

			$accessCodes = array_merge($accessCodes, $this->getStructureAccessCodes($id, NodeEntityType::TEAM));

			return array_values(array_unique($accessCodes));
		}

		return [];
	}

	private function filterAccessCodes(array $accessCodes): array
	{
		$result = [];

		foreach ($accessCodes as $accessCode)
		{
			$accessCodeObject = new \Bitrix\Main\Access\AccessCode($accessCode);

			if (in_array($accessCodeObject->getEntityType(), $this->getValidEntityTypeForAccessCode()))
			{
				$result[] = $accessCode;
			}
		}

		return $result;
	}

	private function getValidEntityTypeForAccessCode(): array
	{
		return [
			\Bitrix\Main\Access\AccessCode::TYPE_USER,
			\Bitrix\Main\Access\AccessCode::TYPE_GROUP,
			\Bitrix\Main\Access\AccessCode::TYPE_SOCNETGROUP,
			\Bitrix\Main\Access\AccessCode::TYPE_DEPARTMENT,
			\Bitrix\Main\Access\AccessCode::TYPE_STRUCTURE_DEPARTMENT,
			\Bitrix\Main\Access\AccessCode::TYPE_STRUCTURE_TEAM,
		];
	}

	private function getStructureAccessCodes(string $id, NodeEntityType $nodeEntityType): array
	{
		$accessCodes = [];

		$nodeRepository = Container::getNodeRepository();
		$node = $nodeRepository->getByAccessCode($id);
		if (!$node)
		{
			return $accessCodes;
		}

		$nodeCollection
			= (new NodeDataBuilder())
				->addFilter(
					new NodeFilter(
						idFilter: IdFilter::fromIds([$node->id]),
						entityTypeFilter: NodeTypeFilter::fromNodeType($nodeEntityType),
						direction: Direction::ROOT,
						depthLevel: DepthLevel::FULL,
					),
				)
				->getAll()
		;

		foreach ($nodeCollection as $node)
		{
			if ($nodeEntityType->value === NodeEntityType::TEAM->value)
			{
				$accessCodes[] = AccessCodeType::HrTeamRecursiveType->value . $node->id;
			}
			elseif ($nodeEntityType->value === NodeEntityType::DEPARTMENT->value)
			{
				$accessCodes[] = AccessCodeType::HrDepartmentRecursiveType->value . $node->id;

				if (preg_match('/^' . AccessCodeType::IntranetDepartmentType->value . '(\d+)$/', $node->accessCode))
				{
					$accessCodes[] = str_replace(
						AccessCodeType::IntranetDepartmentType->value,
						AccessCodeType::IntranetDepartmentRecursiveType->value,
						$node->accessCode,
					);
				}
			}
		}

		return array_values(array_unique($accessCodes));
	}
}
