<?
namespace Bitrix\UI\EntitySelector;

class SearchQuery implements \JsonSerializable
{
	protected $queryWords = [];
	protected $query = '';
	protected $cacheable = true;
	protected $dynamicSearchEntities = [];
	protected $rawQuery = '';

	public function __construct(array $options)
	{
		if (isset($options['queryWords']) && is_array($options['queryWords']))
		{
			$this->setQueryWords($options['queryWords']);
		}

		if (isset($options['cacheable']) && is_bool($options['cacheable']))
		{
			$this->setCacheable($options['cacheable']);
		}

		if (isset($options['dynamicSearchEntities']) && is_array($options['dynamicSearchEntities']))
		{
			$this->setDynamicSearchEntities($options['dynamicSearchEntities']);
		}

		if (isset($options['query']) && is_string($options['query']))
		{
			$this->setRawQuery($options['query']);
		}
	}

	public function getQueryWords(): array
	{
		return $this->queryWords;
	}

	public function getQuery(): string
	{
		return $this->query;
	}

	protected function setQueryWords(array $queryWords)
	{
		foreach ($queryWords as $queryWord)
		{
			if (is_string($queryWord) && mb_strlen(trim($queryWord)) > 0)
			{
				$this->queryWords[] = trim($queryWord);
				$this->query = join(' ', $this->queryWords);
			}
		}
	}

	public function isCacheable(): bool
	{
		return $this->cacheable;
	}

	public function setDynamicSearchEntities(array $entities): void
	{
		$this->dynamicSearchEntities = $entities;
	}

	public function hasDynamicSearchEntity(string $entityId): bool
	{
		return in_array($entityId, $this->dynamicSearchEntities);
	}

	public function setCacheable(bool $flag = true)
	{
		if (is_bool($flag))
		{
			$this->cacheable = $flag;
		}

		return $this;
	}

	public function jsonSerialize()
	{
		return [
			'queryWords' => $this->getQueryWords(),
			'cacheable' => $this->isCacheable()
		];
	}

	public function getRawQuery(): string
	{
		return $this->rawQuery;
	}

	protected function setRawQuery(string $rawQuery): void
	{
		$this->rawQuery = $rawQuery;
	}
}