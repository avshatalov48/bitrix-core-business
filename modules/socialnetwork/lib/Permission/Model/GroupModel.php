<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Permission\Model;

use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;
use ReflectionClass;

class GroupModel implements AccessibleItem
{
	protected ?Workgroup $group = null;

	protected int $id = 0;
	protected int $ownerId = 0;
	protected string $siteId = '';

	public static function createFromArray(array $data): AccessibleItem
	{
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

	public static function createFromId(int $itemId): AccessibleItem
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

	public function getSiteId(): string
	{
		$this->siteId ??= (string)$this->getDomainObject()?->getSiteId();

		return $this->siteId;
	}

	public function getDomainObject(): ?Workgroup
	{
		if ($this->group === null)
		{
			$this->group = GroupRegistry::getInstance()->get($this->id);
		}

		return $this->group;
	}
}