<?php
namespace Bitrix\Bizproc\Workflow\Template\Collection;

class Usages
{
	protected $list;

	public function __construct()
	{
		$this->list = [];
	}

	public function add($ownerId, $sourceType, $value)
	{
		if (!isset($this->list[$sourceType]))
		{
			$this->list[$sourceType] = [];
		}
		$key = "{$ownerId}#{$value}";
		$this->list[$sourceType][$key] = [$ownerId, $value];
		return $this;
	}

	public function addOwnerSources($ownerId, array $sources)
	{
		foreach ($sources as $source)
		{
			$this->add($ownerId, $source[0], $source[1]);
		}
		return $this;
	}

	public function getBySourceType($sourceType)
	{
		return isset($this->list[$sourceType]) ? array_values($this->list[$sourceType]) : [];
	}

	public function getValuesBySourceType($sourceType): array
	{
		$result = $this->getBySourceType($sourceType);
		$result = array_column($result, 1);
		return array_unique($result);
	}

	public function getByOwner($ownerId)
	{
		$list = [];
		foreach ($this->list as $sourceType => $items)
		{
			foreach ($items as list($id, $value))
			{
				if ($ownerId === $id)
				{
					$list[] = [$sourceType, $value];
				}
			}
		}
		return $list;
	}

	public function getAll()
	{
		return $this->list;
	}
}