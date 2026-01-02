<?php

namespace Bitrix\Bizproc\Internal;

use Bitrix\Bizproc\Internal\Repository\Mapper\WorkflowStateMapper;
use Bitrix\Bizproc\Internal\Repository\WorkflowStateRepository\WorkflowStateRepository;
use Bitrix\Main\DI\ServiceLocator;

class Container
{
	protected array $types = [];

	public static function getInstance(): ?self
	{
		return self::getService('bizproc.container');
	}

	private static function getService(string $name): mixed
	{
		$prefix = 'bizproc.';
		if (mb_strpos($name, $prefix) !== 0)
		{
			$name = $prefix . $name;
		}
		$locator = ServiceLocator::getInstance();

		return $locator->has($name)
			? $locator->get($name)
			: null
		;
	}

	public static function getWorkflowStateRepository(): ?WorkflowStateRepository
	{
		return self::getService('bizproc.workflow.state.repository');
	}

	public static function getWorkflowStatRepositoryMapper(): ?WorkflowStateMapper
	{
		return self::getService('bizproc.workflow.state.repository.mapper');
	}
}
