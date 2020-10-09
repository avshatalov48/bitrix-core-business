<?

namespace Bitrix\UI\EntitySelector;

use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

final class Configuration
{
	static private $loaded = false;
	static private $entities = [];
	static private $providers = [];
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
	public static function getProvider($entityId, $options = [])
	{
		self::load();

		if (!is_string($entityId) || !isset(self::$entities[$entityId]))
		{
			return null;
		}

		if (array_key_exists($entityId, self::$providers))
		{
			return self::$providers[$entityId];
		}

		$moduleId = self::$entities[$entityId]['provider']['moduleId'] ?? null;;
		$className = self::$entities[$entityId]['provider']['className'] ?? null;

		self::$providers[$entityId] = self::createProvider($moduleId, $className, $options);

		return self::$providers[$entityId];
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
		}

		self::$loaded = true;
	}

	private static function createProvider($moduleId, $className, $options = [])
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

			return $reflectionClass->newInstance($options);

		}
		catch (\ReflectionException $exception)
		{

		}

		return null;
	}
}