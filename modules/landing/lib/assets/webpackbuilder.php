<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Landing\Landing;
use Bitrix\Main;

class WebpackBuilder extends Builder
{
	protected const PACKAGE_CRITICAL_NAME = 'landing_grid';

	/**
	 * @var ResourceCollection
	 */
	protected $criticalResources;
	/**
	 * @var array
	 */
	protected $normalizedCriticalResources = [];

	/**
	 * @var WebpackFile
	 */
	protected $webpackFile;

	/**
	 * WebpackBuilder constructor.
	 * @param ResourceCollection $resources
	 * @throws Main\ArgumentTypeException
	 */
	public function __construct(ResourceCollection $resources)
	{
		parent::__construct($resources);
		$this->criticalResources = new ResourceCollection();
	}

	/**
	 * Sorting resources by location, find critical resources
	 */
	protected function normalizeResources(): void
	{
		$this->normalizeCriticalResources();
		$this->normalizeBaseResources();
	}

	// todo: normalize lang in critical (like standartbuilder)
	protected function normalizeCriticalResources(): void
	{
		$this->criticalResources = $this->resources->getSliceByLocation(Location::LOCATION_BEFORE_ALL);
		$this->normalizedCriticalResources = $this->criticalResources->getNormalized();
	}

	protected function normalizeBaseResources(): void
	{
		$this->resources->remove($this->criticalResources->getPathes());
		$this->normalizedResources = $this->resources->getNormalized();
	}

	/**
	 * Add assets output at the page
	 */
	public function setOutput(): void
	{
		if ($this->resources->isEmpty())
		{
			return;
		}

		$this->normalizeResources();

		$this->buildFile();

		$this->setCriticalOutput();
		$this->setBaseOutput();
		$this->setStrings();
	}

	/**
	 * Create and configure webpack file. Get exist or create new.
	 */
	protected function buildFile(): void
	{
		$this->webpackFile = new WebpackFile();
		$this->webpackFile->setLandingId($this->landingId);
		$this->webpackFile->setPackageHash($this->createPackageHash());

		$this->fillPackageWithResources();

		$this->webpackFile->build();
	}

	/**
	 * Put added resources to webpack file
	 */
	protected function fillPackageWithResources(): void
	{
		foreach (Types::getAssetTypes() as $type)
		{
			if (array_key_exists($type, $this->normalizedResources))
			{
				if($type === Types::TYPE_LANG)
				{
					$this->webpackFile->setUseLang();
				}
				foreach ($this->normalizedResources[$type] as $resource)
				{
					$this->webpackFile->addResource($resource);
				}
			}
		}
	}

	/**
	 * Init critical resources like JS-extension. Need for primarily added on page
	 */
	protected function setCriticalOutput(): void
	{
		$this->initResourcesAsJsExtension($this->normalizedCriticalResources, self::PACKAGE_CRITICAL_NAME);
	}

	/**
	 * Init base resources like webpack load script
	 */
	protected function setBaseOutput(): void
	{
		Main\Page\Asset::getInstance()->addString($this->webpackFile->getOutput());
	}

	/**
	 * Create unique name for currently landing, assets set, version and view mode.
	 * @return string
	 */
	protected function createPackageHash(): string
	{
		// List can be different with equal assets, because is depends on the order of adding assets. Unique and sort them!
		$list = [];
		foreach ($this->normalizedResources as $type => $resources)
		{
			foreach ($resources as $resource)
			{
				$list[] = $resource;
			}
		}
		$list = array_unique($list);
		sort($list);

		$list[] = 'version_' . Main\ModuleManager::getVersion('landing');
		$list[] = 'lid_' . $this->landingId;
		$list[] = Landing::getPreviewMode() ? 'previewMode' : 'publicMode';

		return substr(md5(serialize($list)), 0, 10);
	}
}