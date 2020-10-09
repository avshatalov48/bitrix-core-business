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

	public function getId()
	{
		return $this->id;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getProvider(): BaseProvider
	{
		return $this->provider;
	}

	public function setProvider(BaseProvider $provider)
	{
		$this->provider = $provider;
	}

	public function isSearchable()
	{
		return $this->searchable;
	}

	public function setSearchable(bool $flag = true)
	{
		$this->searchable = $flag;

		return $this;
	}

	public function hasDynamicSearch()
	{
		return $this->dynamicSearch;
	}

	public function setDynamicSearch(bool $flag = true)
	{
		$this->dynamicSearch = $flag;

		return $this;
	}

	public function hasDynamicLoad()
	{
		return $this->dynamicLoad;
	}

	public function setDynamicLoad(bool $flag = true)
	{
		$this->dynamicLoad = $flag;

		return $this;
	}

	public function jsonSerialize()
	{
		return [
			'id' => $this->getId(),
			'dynamicSearch' => $this->hasDynamicSearch(),
		];
	}
}