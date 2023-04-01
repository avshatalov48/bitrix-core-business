<?php

namespace Bitrix\Sale\Services\Base;

final class RestrictionInfoCollection implements \IteratorAggregate
{

	/** @var array<string, RestrictionInfo> */
	private array $restrictionInfoCollection = [];

	public function __construct(RestrictionInfo ...$restrictionInfoList)
	{
		foreach ($restrictionInfoList as $restrictionInfo)
		{
			$this->add($restrictionInfo);
		}
	}

	public function add(RestrictionInfo $restrictionInfo): void
	{
		$this->restrictionInfoCollection[$restrictionInfo->getType()] = $restrictionInfo;
	}

	public function delete(string $restrictionType): void
	{
		unset($this->restrictionInfoCollection[$restrictionType]);
	}

	public function get(string $restrictionType): ?RestrictionInfo
	{
		return $this->restrictionInfoCollection[$restrictionType] ?? null;
	}

	public function getIterator(): \ArrayIterator
	{
		return (new \ArrayIterator($this->restrictionInfoCollection));
	}
}