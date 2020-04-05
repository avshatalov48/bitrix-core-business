<?php

namespace Bitrix\Sale\Discount\Preset;


use Bitrix\Main\Application;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Discount;
use Bitrix\Sale\Internals\DiscountTable;

Loc::loadMessages(__FILE__);

final class Manager
{
	const DEFAULT_PRESET_DIRECTORY = '/bitrix/modules/sale/handlers/discountpreset/';

	const CATEGORY_PRODUCTS = 4;
	const CATEGORY_PAYMENT  = 5;
	const CATEGORY_DELIVERY = 6;
	const CATEGORY_OTHER    = 7;

	/** @var  ErrorCollection */
	protected $errorCollection;
	/** @var  Manager */
	private static $instance;
	/** @var  BasePreset[] */
	private $presetList;
	/** @var $restrictedGroupsMode bool */
	private $restrictedGroupsMode = false;

	/**
	 * Returns Singleton of Manager
	 * @return Manager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Registers autoloader for presets.
	 * @return void
	 */
	public function registerAutoLoader()
	{
		if (!$this->isAlreadyRegisteredAutoLoader())
		{
			\spl_autoload_register(array($this, 'autoLoad'), true);
		}
	}

	private function isAlreadyRegisteredAutoLoader()
	{
		$autoLoaders = spl_autoload_functions();
		if(!$autoLoaders)
		{
			return false;
		}

		foreach ($autoLoaders as $autoLoader)
		{
			if(!is_array($autoLoader))
			{
				continue;
			}

			list($object, $method) = $autoLoader;

			if ($object instanceof $this)
			{
				return true;
			}
		}

		return false;
	}

	private function __construct()
	{
		$this->errorCollection = new ErrorCollection;

		$this->registerAutoLoader();
	}

	private function __clone()
	{}

	public function enableRestrictedGroupsMode($state)
	{
		$this->restrictedGroupsMode = $state === true;
	}

	public function isRestrictedGroupsModeEnabled()
	{
		return $this->restrictedGroupsMode;
	}

	public function autoLoad($className)
	{
		$file = ltrim($className, "\\");    // fix web env
		$file = strtr($file, Loader::ALPHA_UPPER, Loader::ALPHA_LOWER);

		$documentRoot = $documentRoot = rtrim($_SERVER["DOCUMENT_ROOT"], "/\\");

		if(preg_match("#[^\\\\/a-zA-Z0-9_]#", $file))
		{
			return;
		}

		$file = str_replace('\\', '/', $file);
		$fileParts = explode("/", $file);

		if($fileParts[0] !== "sale" || $fileParts[1] !== "handlers" || $fileParts[2] !== 'discountpreset')
		{
			return;
		}
		array_shift($fileParts);

		$filePath = $documentRoot . "/bitrix/modules/sale/" . implode("/", $fileParts) . ".php";

		if(file_exists($filePath))
		{
			require_once($filePath);
		}
	}

	private function buildPresets()
	{
		if($this->presetList === null)
		{
			$this->presetList = array_filter(
				array_merge(
					$this->buildDefaultPresets(),
					$this->buildCustomPresets()
				),
				function(BasePreset $preset)
				{
					return $preset->isAvailable();
				}
			);
		}

		return $this;
	}

	private function buildCustomPresets()
	{
		$presetList = array();

		$event = new Event('sale', 'OnSaleDiscountPresetBuildList');
		$event->send();

		foreach($event->getResults() as $evenResult)
		{
			if($evenResult->getType() != EventResult::SUCCESS)
			{
				continue;
			}

			$result = $evenResult->getParameters();
			if(!is_array($result))
			{
				throw new SystemException('Wrong event result by building preset list. Must be array.');
			}

			foreach($result as $preset)
			{
				if(empty($preset['CLASS']))
				{
					throw new SystemException('Wrong event result by building preset list. Could not find CLASS.');
				}

				if(is_string($preset['CLASS']) && class_exists($preset['CLASS']))
				{
					$preset = $this->createPresetInstance($preset['CLASS']);
					if($preset)
					{
						$presetList[] = $preset;
					}
				}
				else
				{
					throw new SystemException("Wrong event result by building preset list. Could not find class by CLASS {$preset['CLASS']}");
				}
			}
		}

		return $presetList;
	}

	private function buildDefaultPresets()
	{
		$documentRoot = Application::getDocumentRoot();
		
		if(!Directory::isDirectoryExists($documentRoot . self::DEFAULT_PRESET_DIRECTORY))
		{
			throw new SystemException('Could not find folder with default presets. ' . self::DEFAULT_PRESET_DIRECTORY);
		}

		$defaultList = array();
		$directory = new Directory($documentRoot . self::DEFAULT_PRESET_DIRECTORY);
		foreach($directory->getChildren() as $presetFile)
		{
			if(!$presetFile->isFile() || !$presetFile->getName())
			{
				continue;
			}

			$className = $this->getClassNameFromPath($presetFile->getPath());
			if($className)
			{
				$preset = $this->createPresetInstance($className);
				if($preset)
				{
					$defaultList[] = $preset;
				}
			}
		}

		return $defaultList;
	}

	/**
	 * @param string $className
	 * @return BasePreset
	 */
	private function createPresetInstance($className)
	{
		try
		{
			$class = new \ReflectionClass($className);

			/** @var BasePreset $instance */
			$instance = $class->newInstanceArgs([]);
			$instance->enableRestrictedGroupsMode($this->isRestrictedGroupsModeEnabled());

			return $instance;
		}
		catch (\ReflectionException $exception)
		{
		}

		return null;
	}
	
	private function getClassNameFromPath($path)
	{
		return "Sale\\Handlers\\DiscountPreset\\" . getFileNameWithoutExtension($path);
	}
	
	/**
	 * Returns list of presets.
	 *
	 * @return BasePreset[]
	 */
	public function getPresets()
	{
		return $this->buildPresets()->presetList;
	}

	/**
	 * Returns preset by id. Id is full class name.
	 *
	 * @param string $id Class name of preset
	 * @return BasePreset
	 */
	public function getPresetById($id)
	{
		if(class_exists($id))
		{
			return $this->createPresetInstance($id);
		}
		else
		{
			foreach($this->buildPresets()->presetList as $preset)
			{
				if($preset::className() === $id)
				{
					return $preset;
				}
			}
		}

		return null;
	}

	/**
	 * @param $category
	 * @return BasePreset[]
	 */
	public function getPresetsByCategory($category)
	{
		$presets = array();
		foreach($this->getPresets() as $preset)
		{
			if($preset->getCategory() === $category)
			{
				$presets[] = $preset;
			}
		}

		uasort($presets, function(BasePreset $a, BasePreset $b){
			return $a->getSort() > $b->getSort();
		});

		return $presets;
	}

	public function getCategoryList()
	{
		return array(
			self::CATEGORY_PRODUCTS => Loc::getMessage('SALE_PRESET_DISCOUNT_MANAGER_CATEGORY_PRODUCTS'),
			self::CATEGORY_PAYMENT => Loc::getMessage('SALE_PRESET_DISCOUNT_MANAGER_CATEGORY_PAYMENT'),
			self::CATEGORY_DELIVERY => Loc::getMessage('SALE_PRESET_DISCOUNT_MANAGER_CATEGORY_DELIVERY'),
			self::CATEGORY_OTHER => Loc::getMessage('SALE_PRESET_DISCOUNT_MANAGER_CATEGORY_OTHER'),
		);
	}

	public function getCategoryName($category)
	{
		$categoryList = $this->getCategoryList();

		return isset($categoryList[$category])? $categoryList[$category] : '';
	}

	public function hasCreatedDiscounts(BasePreset $preset)
	{
		$countQuery = new Query(DiscountTable::getEntity());
		$countQuery->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
		$countQuery->setFilter(array(
			'=PRESET_ID' => $preset::className(),
		));
		$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();

		return (bool)$totalCount['CNT'];
	}
}