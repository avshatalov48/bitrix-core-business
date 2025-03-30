<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\V2\Recent\Initializer\Queue\QueueItem;
use Bitrix\Im\V2\Recent\Initializer\Source\Collab;
use Bitrix\Im\V2\Recent\Initializer\Source\Collabs;
use Bitrix\Im\V2\Recent\Initializer\Source\Filter;
use Bitrix\Im\V2\Recent\Initializer\Source\FilterFactory;
use Bitrix\Main\ORM\Query\Query;

abstract class BaseSource implements Source
{
	protected const USER_ID_FIELD_NAME = 'OTHER_USER_ID';

	/**
	 * @var InitialiazerResult[]
	 */
	protected static array $cache = [];
	protected ?int $sourceId;
	protected int $targetId;
	protected Stage $stage;
	protected Filter $filter;
	protected bool $isFirstInit;

	public function __construct(int $targetId, ?int $sourceId, Stage $stage)
	{
		$this->targetId = $targetId;
		$this->sourceId = $sourceId;
		$this->stage = $stage;
		$this->filter = FilterFactory::getInstance()->get($this, $targetId);
	}

	public function getStage(): Stage
	{
		return $this->stage;
	}

	public function getSourceId(): ?int
	{
		return $this->sourceId;
	}

	public static function getInstance(SourceType $type, int $targetId, ?int $sourceId, Stage $stage): Source
	{
		return match ($type)
		{
			SourceType::Collab => new Collab($targetId, $sourceId ?? 0, $stage),
			SourceType::Collabs => new Collabs($targetId, null, $stage),
		};
	}

	public static function createFromQueueItem(QueueItem $queueItem): Source
	{
		$stage = BaseStage::createFromQueueItem($queueItem);
		$source = static::getInstance($queueItem->sourceType, $queueItem->userId, $queueItem->sourceId, $stage);

		return $source->setIsFirstInit($queueItem->isFirstInit);
	}

	public function setIsFirstInit(bool $flag): static
	{
		$this->isFirstInit = $flag;

		return $this;
	}

	public function getItems(string $pointer, int $limit): InitialiazerResult
	{
		$result = $this->getUsers($pointer, $limit);

		return $this->stage->getItems($result);
	}

	final protected function getUsers(string $pointer, int $limit): InitialiazerResult
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

	final protected function getUsersInternal(string $pointer, int $limit): InitialiazerResult
	{
		$query = $this->getBaseQuery($pointer, $limit);
		if ($this->isResultAffectedByStage())
		{
			$query = $this->modifyQueryByStage($query);
			if ($query === null)
			{
				return $this->getResultByRaw([], $limit);
			}
		}

		return $this->getResultByRaw($query->fetchAll(), $limit);
	}

	abstract protected function getBaseQuery(string $pointer, int $limit): Query;

	final protected function modifyQueryByStage(Query $query): ?Query
	{
		return $this->filter->apply($query, self::USER_ID_FIELD_NAME);
	}

	abstract protected function getResultByRaw(array $raw, int $limit): InitialiazerResult;

	abstract protected function isResultAffectedByStage(): bool;

	private function getCacheKey(string $pointer, int $limit): string
	{
		$sourceType = static::getType()->value;
		$stageType = $this->isResultAffectedByStage() ? $this->stage::getType()->value : 'none';
		$sourceId = $this->sourceId ?? 0;

		return "{$sourceType}_{$stageType}_{$sourceId}_{$this->targetId}_{$pointer}_{$limit}";
	}
}
