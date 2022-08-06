<?php

namespace Bitrix\Landing\Assets;

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main;
use Bitrix\Landing;
use Bitrix\Main\IO\File;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * Collect assets, sort by locations, set output in different modes (webpack or default)
 *
 * @package Bitrix\Landing
 */
class Manager
{
	protected const MODE_STANDART = 'STANDART';
	protected const MODE_WEBPACK = 'WEBPACK';

	protected const REGISTERED_KEY_CODE = 'code';
	protected const REGISTERED_KEY_LOCATION = 'location';

	private static $instance;

	/**
	 * webpack or standart
	 * @var string
	 */
	protected $mode;
	/**
	 * Collection of already added assets
	 * @var array
	 */
	protected $registered = [];
	/**
	 * @var ResourceCollection
	 */
	protected $resources;
	/**
	 * @var Builder
	 */
	protected $builder;

	/**
	 * Singleton instance.
	 * @return Manager
	 */
	public static function getInstance(): Manager
	{
		if (self::$instance === null)
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Manager constructor.
	 */
	public function __construct()
	{
		$this->mode = self::MODE_STANDART;
		$this->resources = new ResourceCollection();
	}

	/**
	 * Set webpack mode of builder
	 */
	public function setWebpackMode(): void
	{
		$this->mode = self::MODE_WEBPACK;
	}

	/**
	 * Set standart mode of builder
	 */
	public function setStandartMode(): void
	{
		$this->mode = self::MODE_STANDART;
	}

	/**
	 * Get current mode
	 * @return string
	 */
	public function getMode(): string
	{
		return $this->mode;
	}

	/**
	 * @param string $code - Name of asset or CJSCore extension.
	 * @return mixed
	 */
	protected function isAssetRegistered($code)
	{
		return array_key_exists($code, $this->registered);
	}

	/**
	 * Return location of later added asset
	 * @param string $code Code of added asset
	 * @return bool|mixed asset location or false, if asset not added
	 */
	protected function getRegisteredAssetLocation(string $code)
	{
		if ($this->isAssetRegistered($code))
		{
			return $this->registered[$code][self::REGISTERED_KEY_LOCATION];
		}

		return false;
	}

	/**
	 * @param string $code - Name of asset or CJSCore extension.
	 */
	protected function markAssetRegistered($code, $location): void
	{
		$this->registered[$code] = [
			self::REGISTERED_KEY_CODE => $code,
			self::REGISTERED_KEY_LOCATION => $location,
		];
		
		if($code !== 'main.core' && $code !== 'core')
		{
			\CJSCore::markExtensionLoaded($code);
		}
	}


	/**
	 * Recursive (by 'rel' key) adding assets in WP packege
	 *
	 * @param [string]|string $code
	 * @param int|null $location - Where will be placed asset.
	 */
	public function addAsset($code, $location = null): void
	{
		// recursive for arrays
		if (is_array($code))
		{
			foreach ($code as $item)
			{
				$this->addAsset((string)$item, $location);
			}
		}
		else
		{
			$this->addAssetRecursive($code, $location);
		}
	}

	/**
	 * @param string $code
	 * @param int|null $location
	 */
	protected function addAssetRecursive(string $code, $location = null): void
	{
		$location = Location::verifyLocation($location);

		// just once, but if new location more critical - readd
		if (!$this->isNeedAddAsset($code, $location))
		{
			return;
		}

		// get data from CJSCore
		if ($ext = \CJSCore::getExtInfo($code))
		{
			$asset = $ext;
		}
		else if ($ext = Extension::getConfig($code))
		{
			$asset = $ext;
		}
		// if name - it path
		else if ($type = self::detectType($code))
		{
			$asset = [$type => [$code]];
		}
		else
		{
			return;
		}

		$this->processAsset($asset, $location);
		$this->markAssetRegistered($code, $location);
	}

	/**
	 * @param string $code
	 * @param int $location
	 * @return bool
	 */
	protected function isNeedAddAsset(string $code, int $location): bool
	{
		if ($this->isAssetRegistered($code))
		{
			return $location < $this->getRegisteredAssetLocation($code);
		}

		if (\CJSCore::isExtensionLoaded($code))
		{
			return false;
		}

		return true;
	}


	/**
	 * Get parts of asset and add them in pack
	 *
	 * @param array $asset - array of asset data
	 * @param string $location - where will be placed asset.
	 */
	protected function processAsset(array $asset, int $location): void
	{
		foreach (Types::getAssetTypes() as $type)
		{
			if (!isset($asset[$type]) || empty($asset[$type]))
			{
				continue;
			}

			if (!is_array($asset[$type]))
			{
				$asset[$type] = [$asset[$type]];
			}

			switch ($type)
			{
				case Types::KEY_RELATIVE:
				{
					foreach ($asset[$type] as $rel)
					{
						$this->addAsset($rel, $location);
					}
					break;
				}

				case Types::TYPE_JS:
				case Types::TYPE_CSS:
				case Types::TYPE_LANG:
				{
					foreach ($asset[$type] as $path)
					{
						if (\CMain::isExternalLink($path))
						{
							$this->resources->addString($this->createStringFromPath($path, $type));
						}
						// todo: check is file exist
						else if (self::detectType($path))
						{
							$this->resources->add($path, $type, $location);
						}
					}
					break;
				}

				case Types::TYPE_FONT:
				{
					// preload fonts add immediately
					foreach ($asset[$type] as $fontFile)
					{
						if (
							!\CMain::isExternalLink($fontFile)
							&& File::isFileExists(Landing\Manager::getDocRoot() . $fontFile)
						)
						{
							$this->resources->addString($this->createStringFromPath($fontFile, $type));
						}
					}
					break;
				}

				default:
					break;
			}
		}
	}

	/**
	 * Create <link> or <script> string for adding
	 * @param string $path
	 * @param string $type from Bitrix\Landing\Assets\Types
	 * @return string
	 */
	protected function createStringFromPath(string $path, string $type): string
	{
		$externalLink = '';

		switch ($type)
		{
			case Types::TYPE_CSS:
			{
				$externalLink = "<link href=\"$path\" type=\"text/css\" rel=\"stylesheet\">";
				break;
			}

			case Types::TYPE_JS:
			{
				$externalLink = "<script type=\"text/javascript\" src=\"$path\"></script>";
				break;
			}

			case Types::TYPE_FONT:
			{
				$fontType = self::checkFontLinkType($path);
				$externalLink = '<link rel="preload" href="' . $path
					. '" as="font" crossorigin="anonymous" type="' . $fontType . '" crossorigin>';
				break;
			}

			default:
				break;
		}

		return $externalLink;
	}


	/**
	 * Detect type by path.
	 *
	 * @param string $path Relative path to asset.
	 * @return null|string
	 */
	protected static function detectType(string $path): ?string
	{
		$path = parse_url($path)['path'];
		$type = mb_strtolower(mb_substr(strrchr($path, '.'), 1));
		switch ($type)
		{
			case 'js':
				return Types::TYPE_JS;

			case 'css':
				return Types::TYPE_CSS;

			case 'php':
				return Types::TYPE_LANG;

			case 'woff':
			case 'woff2':
				return Types::TYPE_FONT;

			default:
				return null;
		}
	}

	protected static function checkFontLinkType(string $path): string
	{
		//woff2 must be before woff, because strpos find woff in woff2 ;)
		$available = [
			'woff2' => 'font/woff2',
			'woff' => 'font/woff',
			'ttf' => 'font/ttf',
			'eot' => 'application/vnd.ms-fontobject',
			'svg' => 'image/svg+xml',
		];

		$linkType = '';
		foreach ($available as $type => $value)
		{
			if (mb_strpos($path, $type) !== false)
			{
				$linkType = $value;
				break;
			}
		}

		return $linkType;
	}

	/**
	 * Add asset string
	 *
	 * @param string $string
	 */
	public function addString(string $string): void
	{
		$this->resources->addString(trim($string));
	}

	/**
	 * Add extensions on page
	 * @param int $lid - ID of current landing.
	 */
	public function setOutput(int $lid = 0): void
	{
		if ($lid === 0)
		{
			trigger_error(
				"You must to pass ID of current landing to the \Bitrix\Landing\Assets\Manager::setOutput",
				E_USER_WARNING
			);
		}
		$this->createBuilder();
		$this->builder->attachToLanding($lid);
		$this->builder->setOutput();
	}

	/**
	 * Create builder object by currently set mode
	 * @throws Main\ArgumentException
	 */
	protected function createBuilder(): void
	{
		$this->builder = Builder::createByType($this->resources, $this->mode);
	}

	/**
	 * When updated assets files - need rebuild webpack file. Marked packs for all landing as "need rebuild".
	 */
	public static function rebuildWebpack(): void
	{
		WebpackFile::markAllToRebuild();
	}

	/**
	 * When updated assets files - need rebuild webpack file. Marked packs for current landing as "need rebuild".
	 * @param int|[int] $lid - ID of landing.
	 */
	public static function rebuildWebpackForLanding($lid = []): void
	{
		WebpackFile::markToRebuild($lid);
	}
}