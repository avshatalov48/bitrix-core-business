<?php

namespace Bitrix\Main\Web\WebPacker\Resource;

/**
 * Class Package
 *
 * @package Bitrix\Main\Web\WebPacker\Resource
 */
class Package
{
	/** @var Asset[] $list  */
	protected $list = [];
	protected $onDemand = false;

	/**
	 * Get type list.
	 *
	 * @return array
	 */
	public static function getOrderedTypeList()
	{
		return [
			Asset::CSS,
			Asset::LANG,
			Asset::LAYOUT,
			Asset::JS,
		];
	}

	/**
	 * Package constructor.
	 *
	 * @param Asset[] $assets Assets.
	 */
	public function __construct(array $assets = [])
	{
		foreach ($assets as $asset)
		{
			$this->addAsset($asset);
		}
	}

	/**
	 * Add asset.
	 *
	 * @param Asset $item Item.
	 * @return $this
	 */
	public function addAsset(Asset $item)
	{
		$this->list[] = $item;
		return $this;
	}

	/**
	 * Get assets.
	 *
	 * @param string $type Type.
	 * @return Asset[]
	 */
	public function getAssets($type = null)
	{
		if ($type)
		{
			$list = [];
			foreach ($this->list as $asset)
			{
				if ($asset->getType() !== $type)
				{
					continue;
				}

				$list[] = $asset;
			}

			return $list;
		}

		return $this->list;
	}

	/**
	 * Enable loading by path for resources.
	 * Default - content loading.
	 *
	 * @return $this
	 */
	public function onDemand()
	{
		$this->onDemand = true;
		return $this;
	}

	/**
	 * Return true if on demand.
	 *
	 * @return bool
	 */
	public function isOnDemand()
	{
		return $this->onDemand;
	}

	/**
	 * Convert to array.
	 *
	 * @param string $type Type.
	 * @return array
	 */
	public function toArray($type = null)
	{
		$result = [];
		foreach ($this->list as $asset)
		{
			if ($type && $asset->getType() != $type)
			{
				continue;
			}

			$path = $asset->getUri();
			if ($this->onDemand && !$path)
			{
				continue;
			}

			$content = $asset->getContent();
			if (!$this->onDemand && !$content)
			{
				continue;
			}

			$result[] = [
				'type' => $asset->getType(),
				'path' => $path,
				'content' => $content,
				'cache' => true,
			];
		}

		return $result;
	}
}