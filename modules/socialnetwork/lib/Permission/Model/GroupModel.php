<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission\Model;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Permission\AccessModelInterface;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Site\Site;
use ReflectionClass;

class GroupModel implements AccessModelInterface
{
	protected ?Workgroup $group = null;

	protected int $id = 0;
	protected ?int $ownerId = null;
	protected ?array $siteIds = null;

	public static function createFromArray(array|Arrayable $data): static
	{
		if ($data instanceof Arrayable)
		{
			$data = $data->toArray();
		}

		$model = new static();

		$reflection = new ReflectionClass($model);

		foreach ($data as $key => $value)
		{
			if ($reflection->hasProperty($key))
			{
				$model->{$key} = $value;
			}
		}

		return $model;
	}

	public static function createFromId(int $itemId): static
	{
		$model = new static();
		$model->id = $itemId;

		return $model;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getOwnerId(): int
	{
		$this->ownerId ??= (int)$this->getDomainObject()?->getOwnerId();

		return $this->ownerId;
	}

	public function getSiteIds(): array
	{
		if ($this->siteIds !== null)
		{
			return $this->siteIds;
		}

		if ($this->id <= 0)
		{
			$this->siteIds = $this->getDefaultSiteIds();
		}
		else
		{
			$this->siteIds = $this->getDomainObject()?->getSiteIds();
		}

		return $this->siteIds;
	}

	public function getDomainObject(): ?Workgroup
	{
		if ($this->group === null)
		{
			$this->group = $this->getRegistry()->get($this->id);
		}

		return $this->group;
	}

	protected function getRegistry(): GroupRegistry
	{
		return GroupRegistry::getInstance();
	}

	protected function getDefaultSiteIds(): array
	{
		return [Site::getInstance()->getMainSiteId()];
	}
}