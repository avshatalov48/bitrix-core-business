<?php

namespace Bitrix\Calendar\Integration\HumanResources;

use Bitrix\HumanResources;
use Bitrix\Main\Loader;

final class Structure
{
	private const INITIAL_ROOT_DEPARTMENT_ID = -1;
	private static ?int $rootDepartmentId = self::INITIAL_ROOT_DEPARTMENT_ID;

	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function getRootDepartmentId(): ?int
	{
		if (self::$rootDepartmentId === self::INITIAL_ROOT_DEPARTMENT_ID)
		{
			self::$rootDepartmentId = $this->loadRootDepartmentId();
		}

		return self::$rootDepartmentId;
	}

	private function loadRootDepartmentId(): ?int
	{
		if (!Loader::includeModule('humanresources'))
		{
			return null;
		}

		$structure = (new HumanResources\Repository\StructureRepository())->getByXmlId(
			HumanResources\Item\Structure::DEFAULT_STRUCTURE_XML_ID,
		);

		if (!isset($structure))
		{
			return null;
		}

		$rootNode = (new HumanResources\Repository\NodeRepository())->getRootNodeByStructureId($structure->id);
		if (!isset($rootNode))
		{
			return null;
		}

		preg_match('/D(\d+)/', $rootNode->accessCode ?? '', $matches);

		if (empty($matches[1]))
		{
			return null;
		}

		return (int)$matches[1];
	}
}
