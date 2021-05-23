<?

namespace Bitrix\UI\EntitySelector;

class Entity implements \JsonSerializable
{
	protected $id;
	protected $options = [];
	protected $searchable = true;
	protected $dynamicLoad = false;
	protected $dynamicSearch = false;
	protected $provider;

	public function __construct(array $options)
	{
		if (!empty($options['id']) && is_string($options['id']))
		{
			$this->id = strtolower($options['id']);
		}

		if (!empty($options['options']) && is_array($options['options']))
		{
			$this->options = $options['options'];
		}

		if (isset($options['searchable']) && is_bool($options['searchable']))
		{
			$this->setSearchable($options['searchable']);
		}

		if (isset($options['dynamicSearch']) && is_bool($options['dynamicSearch']))
		{
			$this->setDynamicSearch($options['dynamicSearch']);
		}

		if (isset($options['dynamicLoad']) && is_bool($options['dynamicLoad']))
		{
			$this->setDynamicLoad($options['dynamicLoad']);
		}
	}

	public static function create(array $entityOptions): ?Entity
	{
		$entity = new Entity($entityOptions);
		$provider = Configuration::getProvider($entity->getId(), $entity->getOptions());
		if ($provider && $provider->isAvailable())
		{
			$entity->setProvider($provider);

			return $entity;
		}

		return null;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getProvider(): BaseProvider
	{
		return $this->provider;
	}

	public function setProvider(BaseProvider $provider): self
	{
		$this->provider = $provider;

		return $this;
	}

	public function isSearchable(): bool
	{
		return $this->searchable;
	}

	public function setSearchable(bool $flag = true): self
	{
		$this->searchable = $flag;

		return $this;
	}

	public function hasDynamicSearch(): bool
	{
		return $this->dynamicSearch;
	}

	public function setDynamicSearch(bool $flag = true): self
	{
		$this->dynamicSearch = $flag;

		return $this;
	}

	public function hasDynamicLoad(): bool
	{
		return $this->dynamicLoad;
	}

	public function setDynamicLoad(bool $flag = true): self
	{
		$this->dynamicLoad = $flag;

		return $this;
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'dynamicSearch' => $this->hasDynamicSearch(),
		];
	}
}