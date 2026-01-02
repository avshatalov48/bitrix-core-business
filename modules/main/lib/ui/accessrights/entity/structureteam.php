<?php

namespace Bitrix\Main\UI\AccessRights\Entity;

use Bitrix\HumanResources\Builder\Structure\Filter\Column\IdFilter;
use Bitrix\HumanResources\Builder\Structure\Filter\NodeFilter;
use Bitrix\HumanResources\Builder\Structure\NodeDataBuilder;
use Bitrix\HumanResources\Item\Node;
use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\Loader;

class StructureTeam extends EntityBase
{
	/**
	 * @return ?Node
	 */
	public function getModel()
	{
		return $this->model ?? null;
	}

	public function getType(): string
	{
		return AccessCode::TYPE_STRUCTURE_TEAM;
	}

	public function getName(): string
	{
		return $this->getModel()?->name ?? '';
	}

	public function getUrl(): string
	{
		return '';
	}

	public function getAvatar(int $width = 58, int $height = 58): ?string
	{
		return '';
	}

	protected function loadModel(): void
	{
		if (
			!$this->model
			&& Loader::includeModule('humanresources')
			&& class_exists(NodeDataBuilder::class)
		)
		{
			$this->model = NodeDataBuilder::createWithFilter(
				new NodeFilter(IdFilter::fromId($this->getId()))
			)
				->get()
			;
		}
	}
}
