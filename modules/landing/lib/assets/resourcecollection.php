<?php

namespace Bitrix\Landing\Assets;


/**
 * Class Manager
 * Collect assets, sort by locations, set output in different modes (webpack or default)
 *
 * @package Bitrix\Landing
 */
class ResourceCollection
{
	protected const KEY_PATH = 'path';
	protected const KEY_TYPE = 'type';
	protected const KEY_LOCATION = 'location';
	protected const KEY_ORDER = 'order';

	/**
	 * Collection of added resources. Key of array - path, values - array of parameters
	 * @var array
	 */
	protected $resources;
	/**
	 * Order variable for save added asset position
	 * @var int
	 */
	protected $order;
	/**
	 * Save assets string, like a <script> and <link>. May be external links, or local inline scripts e.g.
	 * @var array
	 */
	protected $strings = [];

	/**
	 * ResourceCollection constructor.
	 */
	public function __construct()
	{
		$this->resources = [];
		$this->order = 0;
	}

	/**
	 * @param string $path
	 * @param string $type
	 * @param int $location
	 */
	public function add(string $path, string $type, int $location): void
	{
		// overwrite only if new location more
		if (
			$this->isResourceAdded($path)
			&& !$this->isNeedRaiseLocation($path, $location)
		)
		{
			return;
		}

		$this->resources[$path] = [
			self::KEY_PATH => $path,
			self::KEY_TYPE => $type,
			self::KEY_LOCATION => $location,
			self::KEY_ORDER => $this->order++,
		];
	}

	protected function isResourceAdded(string $path): bool
	{
		return array_key_exists($path, $this->resources);
	}

	protected function isNeedRaiseLocation(string $path, int $location): bool
	{
		return $location < $this->resources[$path][self::KEY_LOCATION];
	}

	/**
	 * Save asset string in collection (like a <script> or <link>)
	 * @param string $string
	 */
	public function addString(string $string): void
	{
		if ($string && !in_array($string, $this->strings, true))
		{
			$this->strings[] = $string;
		}
	}

	/**
	 * Return added strings
	 * @return array of strings
	 */
	public function getStrings(): array
	{
		return $this->strings;
	}

	/**
	 * @param mixed $pathes
	 */
	public function remove($pathes): void
	{
		if (!is_array($pathes))
		{
			$pathes = [$pathes];
		}

		foreach ($pathes as $path)
		{
			$this->removeOnce($path);
		}
	}

	protected function removeOnce(string $path): void
	{
		if ($this->isResourceAdded($path))
		{
			unset($this->resources[$path]);
		}
	}

	/**
	 * Create new ResourceCollection object by location
	 * @param int $location
	 * @return ResourceCollection
	 */
	public function getSliceByLocation(int $location): ResourceCollection
	{
		return $this->getSliceByFilter(self::KEY_LOCATION, $location);
	}

	/**
	 * Create new ResourceCollection object by filter
	 *
	 * @param $field - field name
	 * @param $value - value of field
	 * @return ResourceCollection
	 */
	protected function getSliceByFilter($field, $value): ResourceCollection
	{
		$resourcesByFilter = new self();

		foreach ($this->resources as $resource)
		{
			if (array_key_exists($field, $resource) && $resource[$field] === $value)
			{
				$resourcesByFilter->add(
					$resource[self::KEY_PATH],
					$resource[self::KEY_TYPE],
					$resource[self::KEY_LOCATION]
				);
			}
		}

		return $resourcesByFilter;
	}

	/**
	 * Return pathes of added resources
	 * @return array
	 */
	public function getPathes(): array
	{
		return array_keys($this->resources);
	}

	/**
	 * Sort by location and group by types
	 * @return array
	 */
	public function getNormalized(): array
	{
		$this->sortByLocation();
		$normalizedResources = [];

		foreach ($this->resources as $resource)
		{
			if (!array_key_exists($resource[self::KEY_TYPE], $normalizedResources))
			{
				$normalizedResources[$resource[self::KEY_TYPE]] = [];
			}
			$normalizedResources[$resource[self::KEY_TYPE]][] = $resource[self::KEY_PATH];
		}

		return $normalizedResources;
	}

	protected function sortByLocation(): void
	{
		$columnLocation = array_column($this->resources, self::KEY_LOCATION);
		$columnOrder = array_column($this->resources, self::KEY_ORDER);
		array_multisort($columnLocation, $columnOrder, $this->resources);
	}

	/**
	 * Return true is no added resources and no added strings
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return (count($this->resources) === 0) && (count($this->strings) === 0);
	}
}