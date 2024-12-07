<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main;


abstract class Builder
{
	protected const TYPE_STANDART = 'STANDART';
	protected const TYPE_WEBPACK = 'WEBPACK';

	protected const PACKAGE_NAME = 'landing_assets';

	/**
	 * @var ResourceCollection
	 */
	protected $resources;
	/**
	 * @var array
	 */
	protected $normalizedResources = [];
	/**
	 * Asset pack may be attached to landing
	 * @var int
	 */
	protected $landingId = 0;

	/**
	 * Builder constructor.
	 * @param ResourceCollection $resources
	 * @throws ArgumentTypeException
	 */
	public function __construct(ResourceCollection $resources)
	{
		if ($resources instanceof ResourceCollection)
		{
			$this->resources = $resources;
		}
		else
		{
			throw new ArgumentTypeException($resources, 'ResourceCollection');
		}
	}

	/**
	 * @param ResourceCollection $resources	Resources object
	 * @param string $type Builder type
	 * @return Builder
	 * @throws ArgumentException
	 * @throws ArgumentTypeException
	 */
	public static function createByType(ResourceCollection $resources, string $type): ?Builder
	{
		switch ($type)
		{
			case self::TYPE_STANDART:
				return new StandartBuilder($resources);

			case self::TYPE_WEBPACK:
				return new WebpackBuilder($resources);

			default:
				throw new ArgumentException("Unknown landing asset builder type `$type`.");
		}
	}

	/**
	 * Assets pack must be attached only to once landing. Set ID
	 * @param int $lid - landing ID
	 */
	public function attachToLanding(int $lid): void
	{
		$this->landingId = (int)$lid;
	}

	/**
	 * Add assets to page
	 * @return mixed
	 */
	abstract public function setOutput();

	/**
	 * Get all assets as normalized array by types
	 * @return array
	 */
	public function getOutput(): array
	{
		$this->normalizeResources();

		return $this->normalizedResources;
	}

	abstract protected function normalizeResources();

	protected function initResourcesAsJsExtension(array $resources, $extName = null): void
	{
		if (!$extName)
		{
			$extName = self::PACKAGE_NAME;
		}
		$extFullName = $extName . '_' . md5(serialize($resources));

		$resources = array_merge($resources, [
			'bundle_js' => $extFullName,
			'bundle_css' => $extFullName,
			'skip_core' => true,
		]);
		/** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
		\CJSCore::registerExt($extName, $resources);
		\CJSCore::Init($extName);
	}

	/**
	 * Add assets strings to page
	 */
	protected function setStrings(): void
	{
		foreach ($this->resources->getStrings() as $string)
		{
			Main\Page\Asset::getInstance()->addString($string, false, Main\Page\AssetLocation::AFTER_JS);
		}
	}
}