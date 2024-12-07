<?php

namespace Bitrix\Socialnetwork\Space\List\RecentActivity\Collector;

use Bitrix\Socialnetwork\Space\List\RecentActivity\Item\RecentActivityData;

abstract class AbstractProvider implements ProviderInterface
{
	/** @var array<RecentActivityData> $recentActivityDataItems */
	protected array $recentActivityDataItems = [];

	protected array $entities = [];
	abstract protected function isAvailable(): bool;
	abstract protected function fill(): void;

	protected function addEntity(int $id, $entity): void
	{
		$this->entities[$id] = $entity;
	}

	protected function getEntity(int $id)
	{
		return $this->entities[$id] ?? null;
	}

	public function addItem(RecentActivityData $recentActivityData): void
	{
		$this->recentActivityDataItems[] = $recentActivityData;
	}

	final public function fillData(): void
	{
		if (!$this->isAvailable() || empty($this->recentActivityDataItems))
		{
			return;
		}

		$this->fill();
	}
}
