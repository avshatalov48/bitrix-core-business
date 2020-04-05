<?php

namespace Bitrix\Main\UI\Viewer\Renderer;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\Uri;

abstract class Renderer
{
	const JS_TYPE_UNKNOWN = 'unknown';

	protected $name;
	/** @var Uri */
	protected $sourceUri;
	/** @var array */
	protected $options;

	public function __construct($name, Uri $sourceUri, array $options = [])
	{
		$this->name = $name;
		$this->sourceUri = $sourceUri;
		$this->options = $options;
	}

	public function getOption($name, $defaultValue = null)
	{
		$value = $this->getByPath($this->options, $name, $defaultValue);
		if ($value instanceof \Closure)
		{
			return $value();
		}

		return $value;
	}

	/**
	 * Returns value, that belongs to path.
	 *
	 * @param array|\ArrayAccess $array Target array.
	 * @param string $path Path. Example data.altContent.src
	 * @param null $defaultValue Default value
	 * @return array|\ArrayAccess|mixed|null
	 * @throws ArgumentException
	 */
	private function getByPath($array, $path, $defaultValue = null)
	{
		if(!is_array($array) && !$array instanceof \ArrayAccess)
		{
			throw new ArgumentException("\$array is not array or don't implement ArrayAccess");
		}

		$pathItems = explode('.', $path);

		$lastArray = $array;
		foreach($pathItems as $pathItem)
		{
			if(!is_array($lastArray) && !$lastArray instanceof \ArrayAccess)
			{
				return $defaultValue;
			}

			if(!isset($lastArray[$pathItem]))
			{
				return $defaultValue;
			}

			$lastArray = $lastArray[$pathItem];
		}

		return $lastArray;
	}

	public static function getAllowedContentTypes()
	{
		return [];
	}

	public static function getJsType()
	{
		return self::JS_TYPE_UNKNOWN;
	}

	abstract public function render();

	public function getData()
	{
		return null;
	}
}