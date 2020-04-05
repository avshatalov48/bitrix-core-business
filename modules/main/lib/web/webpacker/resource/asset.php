<?php

namespace Bitrix\Main\Web\WebPacker\Resource;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\IO;
use Bitrix\Main\Web\WebPacker;

/**
 * Class Asset
 *
 * @package Bitrix\Main\Web\WebPacker\Resource
 */
abstract class Asset
{
	const JS = 'js';
	const CSS = 'css';
	const LAYOUT = 'layout';
	const LANG = 'lang';

	/** @var string */
	protected $type;
	/** @var string|null $path Path. */
	protected $path;
	/** @var string|array|null $content Content. */
	protected $content;

	/**
	 * Create asset instance.
	 *
	 * @param string $path Relative path to asset.
	 * @return CssAsset|JsAsset|LangAsset|LayoutAsset
	 * @throws ArgumentException
	 */
	public static function create($path)
	{
		$type = self::detectType($path);
		switch ($type)
		{
			case self::LANG:
				return new LangAsset($path);
			case self::JS:
				return new JsAsset($path);
			case self::CSS:
				return new CssAsset($path);
			case self::LAYOUT:
				return new LayoutAsset($path);
			default:
				throw new ArgumentException("Unknown type `$type`.");
		}
	}

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getTypeList()
	{
		return [self::JS, self::CSS, self::LAYOUT, self::LANG];
	}

	/**
	 * Detect type by path.
	 *
	 * @param string $path Relative path to asset.
	 * @return null|string
	 */
	public static function detectType($path)
	{
		$type = strtolower(substr(strrchr($path, '.'), 1));
		switch ($type)
		{
			case 'js':
				return self::JS;
			case 'css':
				return self::CSS;
			case 'htm':
			case 'html':
				return self::LAYOUT;
			case 'php':
				return self::LANG;
			default:
				return null;
		}
	}

	/**
	 * Asset constructor.
	 *
	 * @param string|null $path Path to resource.
	 */
	public function __construct($path = null)
	{
		if ($path)
		{
			$this->setPath($path);
		}
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set path to resource.
	 *
	 * @param string $path Path to file.
	 * @return $this
	 */
	public function setPath($path)
	{
		if (!static::isExists($path))
		{
			throw new ArgumentException("Can not locate file by path `{$path}`.");
		}

		if (self::detectType($path) != $this->getType())
		{
			throw new ArgumentException("Path has wrong ext of `$path` for Asset with type `{$this->getType()}`.");
		}

		$this->path = $path;

		return $this;
	}

	/**
	 * Get uri.
	 *
	 * @return string|null
	 */
	public function getUri()
	{
		return $this->path ?
			WebPacker\Builder::getDefaultSiteUri()
				. $this->path
				. '?' . filemtime(self::getAbsolutePath($this->path))
				. '.' . filesize(self::getAbsolutePath($this->path))
			:
			null;
	}

	/**
	 * Set content.
	 *
	 * @param string|array $content Content.
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->path ? basename($this->path) : null;
	}

	/**
	 * Get content.
	 *
	 * @return string|null
	 */
	public function getContent()
	{
		if ($this->content)
		{
			return $this->content;
		}

		if (!static::isExists($this->path))
		{
			return null;
		}

		$path = self::getAbsolutePath($this->path);

		$mapPath = null;
		$useMinimized = false;
		$minPathPos = strrpos($path, '.');
		if ($minPathPos !== false)
		{
			$minPath = substr($path, 0, $minPathPos)
				. '.min'
				. substr($path, $minPathPos);

			if (IO\File::isFileExists($minPath))
			{
				$path = $minPath;
				$useMinimized = true;

				$minPathPos = strrpos($this->path, '.');
				$mapPath = substr($this->path, 0, $minPathPos)
					. '.map'
					. substr($this->path, $minPathPos);
			}
		}

		$content = IO\File::getFileContents($path) ?: '';
		if (!$content || !$useMinimized || !$mapPath)
		{
			return $content;
		}

		$parts = explode("\n", $content);
		$mapUri = '//# sourceMappingURL=';
		if (strpos(array_pop($parts), $mapUri) !== 0)
		{
			return $content;
		}

		$mapUri .=  WebPacker\Builder::getDefaultSiteUri() . $mapPath;
		array_push($parts, $mapUri);

		return implode("\n", $parts);

	}

	/**
	 * Return true if asset exists.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	public static function isExists($path)
	{
		return $path ? IO\File::isFileExists(self::getAbsolutePath($path)) : false;
	}

	/**
	 * Get absolute path.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	protected static function getAbsolutePath($path)
	{
		return $path ? Application::getDocumentRoot() . $path : null;
	}
}