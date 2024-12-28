<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\V2\Recent\Initializer\Source\Collab;
use Bitrix\Im\V2\Recent\Initializer\Source\Collabs;

abstract class BaseSource implements Source
{
	/**
	 * @var InitialiazerResult[]
	 */
	protected static array $cache = [];
	protected ?int $sourceId;
	protected int $targetId;

	public function __construct(int $targetId, ?int $sourceId)
	{
		$this->targetId = $targetId;
		$this->sourceId = $sourceId;
	}

	public function getSourceId(): ?int
	{
		return $this->sourceId;
	}

	public static function getInstance(SourceType $type, int $targetId, ?int $sourceId): Source
	{
		return match ($type)
		{
			SourceType::Collab => new Collab($targetId, $sourceId ?? 0),
			SourceType::Collabs => new Collabs($targetId, null),
		};
	}

	public function getUsers(string $pointer, int $limit): InitialiazerResult
	{
		$cacheKey = $this->getCacheKey($pointer, $limit);
		if (isset(self::$cache[$cacheKey]))
		{
			$result = clone self::$cache[$cacheKey];

			return $result->setSelectedItemsCount(0);
		}

		self::$cache[$cacheKey] = $this->getUsersInternal($pointer, $limit);

		return clone self::$cache[$cacheKey];
	}

	abstract protected function getUsersInternal(string $pointer, int $limit): InitialiazerResult;

	private function getCacheKey(string $pointer, int $limit): string
	{
		return static::getType()->value . '_' . ($this->sourceId ?? 0) . '_' . $this->targetId . '_' . $pointer . '_' . $limit;
	}
}
