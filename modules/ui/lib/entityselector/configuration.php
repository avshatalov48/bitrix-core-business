<?

namespace Bitrix\UI\EntitySelector;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

final class Configuration
{
	static private $loaded = false;
	static private $entities = [];
	static private $providers = [];
	static private $filters = [];
	static private $extensions = [];

	public static function getExtensions(): array
	{
		self::load();

		return self::$extensions;
	}

	/**
	 * @param string $entityId
	 *
	 * @param array $options
	 *
	 * @return BaseProvider|null
	 */
	public static function getProvider(Entity $entity)
	{
		$entityId = $entity->getId();
		$options = $entity->getOptions();

		self::load();

		if (!is_string($entityId) || !isset(self::$entities[$entityId]))
		{
			return null;
		}

		if (array_key_exists($entityId, self::$providers))
		{
			return self::$providers[$entityId];
		}

		$substituteEntityId = $entity->getSubstituteEntityId();
		if (
			is_string($substituteEntityId)
			&& isset(self::$entities[$substituteEntityId]['substitutes'])
			&& self::$entities[$substituteEntityId]['substitutes'] === $entityId
		)
		{
			$moduleId = self::$entities[$substituteEntityId]['provider']['moduleId'] ?? null;;
			$className = self::$entities[$substituteEntityId]['provider']['className'] ?? null;
		}
		else
		{
			$moduleId = self::$entities[$entityId]['provider']['moduleId'] ?? null;;
			$className = self::$entities[$entityId]['provider']['className'] ?? null;
		}

		self::$providers[$entityId] = self::createProvider($moduleId, $className, $options);

		return self::$providers[$entityId];
	}

	/**
	 * @param string $entityId
	 * @param array $filterOptions
	 * @return array|null
	 */
	public static function getFilters(string $entityId, array $filterOptions = []): ?array
	{
		self::load();

		if (!is_string($entityId) || !isset(self::$entities[$entityId]))
		{
			return null;
		}

		$filterConfigs = self::$filters[$entityId] ?? null;
		if (!is_array($filterConfigs) || count($filterConfigs) === 0)
		{
			return null;
		}

		$filters = [];
		foreach ($filterOptions as $filterOption)
		{
			if (!array_key_exists($filterOption['id'], $filterConfigs))
			{
				continue;
			}

			$moduleId = FilterControllerResolver::getModuleId($filterOption['id']);
			$className = $filterConfigs[$filterOption['id']]['className'] ?? null;
			$options = isset($filterOption['options']) && is_array($filterOption['options']) ? $filterOption['options'] : [];

			$filters[] = self::createFilter($moduleId, $className, $options);
		}

		return $filters;
	}

	public static function getEntities()
	{
		self::load();

		return self::$entities;
	}

	private static function load()
	{
		if (self::$loaded)
		{
			return;
		}

		foreach (ModuleManager::getInstalledModules() as $moduleId => $moduleDesc)
		{
			$settings = \Bitrix\Main\Config\Configuration::getInstance($moduleId)->get('ui.entity-selector');
			if (empty($settings) || !is_array($settings))
			{
				continue;
			}

			if (!empty($settings['extensions']) && is_array($settings['extensions']))
			{
				self::$extensions = array_merge(self::$extensions, $settings['extensions']);
			}

			if (!empty($settings['entities']) && is_array($settings['entities']))
			{
				foreach ($settings['entities'] as $entity)
				{
					if (is_array($entity) && !empty($entity["entityId"]) && is_string($entity["entityId"]))
					{
						self::$entities[$entity["entityId"]] = $entity;
					}
				}
			}

			if (!empty($settings['filters']) && is_array($settings['filters']))
			{
				foreach ($settings['filters'] as $filter)
				{
					if (
						is_array($filter)
						&& !empty($filter['id'])
						&& is_string($filter['id'])
						&& !empty($filter['entityId'])
						&& is_string($filter['entityId'])
						&& !empty($filter['className'])
						&& is_string($filter['className'])
					)
					{
						self::$filters[$filter['entityId']][$filter['id']] = $filter;
					}
				}
			}
		}

		self::$loaded = true;
	}

	private static function createProvider($moduleId, $className, $options = []): ?BaseProvider
	{
		if (!is_string($className))
		{
			return null;
		}

		if (is_string($moduleId))
		{
			Loader::includeModule($moduleId);
		}

		try
		{
			$reflectionClass = new \ReflectionClass($className);
			if ($reflectionClass->isAbstract())
			{
				return null;
			}

			if (!$reflectionClass->isSubclassOf(BaseProvider::class))
			{
				return null;
			}

			/** @var BaseProvider $provider */
			$provider = $reflectionClass->newInstance($options);

			return $provider;

		}
		catch (\ReflectionException $exception)
		{

		}

		return null;
	}

	private static function createFilter($moduleId, $className, $options = []): ?BaseFilter
	{
		if (!is_string($className))
		{
			return null;
		}

		if (is_string($moduleId))
		{
			Loader::includeModule($moduleId);
		}

		try
		{
			$reflectionClass = new \ReflectionClass($className);
			if ($reflectionClass->isAbstract())
			{
				return null;
			}

			if (!$reflectionClass->isSubclassOf(BaseFilter::class))
			{
				return null;
			}

			/** @var BaseFilter $filter */
			$filter = $reflectionClass->newInstance($options);

			return $filter;
		}
		catch (\ReflectionException $exception)
		{

		}

		return null;
	}
}
