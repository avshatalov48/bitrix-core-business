<?php
namespace Bitrix\Landing\Assets\PreProcessing;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Node;
use \Bitrix\Landing\Config;
use \Bitrix\Landing\Manager;
use \Bitrix\Landing\Assets;
use \Bitrix\Landing\Internals\BlockTable;
use \Bitrix\Main\IO\File;

class Icon
{
	/**
	 * File name with icon content.
	 */
	const CONTENT_ICON_FILE_NAME = 'content.css';

	/**
	 * File name with icon css rules.
	 */
	const RULE_ICON_FILE_NAME = 'style.css';

	/**
	 * File names of two types of fonts
	 */
	const ICON_FONT_FILE_NAME = 'font.woff';
	const ICON_FONT_FILE_NAME_2 = 'font.woff2';

	/**
	 * Tries to resolve and returns icon file path.
	 * @param string $vendorName Vendor folder code.
	 * @return string|null
	 */
	protected static function getIconsPath(string $vendorName): ?string
	{
		$iconSrc = Config::get('icon_src');
		$iconSrc = Manager::getDocRoot() . $iconSrc . $vendorName;
		if (is_dir($iconSrc))
		{
			return $iconSrc;
		}

		return null;
	}

	/**
	 * Parses icon file and returns content for each icon class.
	 * @param string $vendorName Vendor folder code.
	 * @return array
	 */
	protected static function getIconsContentByVendor(string $vendorName): array
	{
		static $vendorContent = [];

		if (!array_key_exists($vendorName, $vendorContent))
		{
			$vendorContent[$vendorName] = [];
			$path = self::getIconsPath($vendorName);
			if ($path)
			{
				$cssFileName = $path . '/' . self::CONTENT_ICON_FILE_NAME;
				if (File::isFileExists($cssFileName))
				{
					$cssContent = File::getFileContents($cssFileName);
					if ($cssContent)
					{
						$found = preg_match_all(
							'/.(' . $vendorName . '-[^:]+):before\s*{\s*content:\s*"([^"]+)";\s*}/',
							$cssContent,
							$matches
						);
						if ($found)
						{
							foreach ($matches[1] as $i => $match)
							{
								$vendorContent[$vendorName][$match] = $matches[2][$i];
							}
						}
					}
				}
			}
		}

		return $vendorContent[$vendorName];
	}

	/**
	 * Returns icon css content.
	 * @param string $className Class name.
	 * @param string $vendorName Vendor folder code.
	 * @return string|null
	 */
	protected static function getIconContentByClass(string $className, string $vendorName): ?string
	{
		$contentAll = self::getIconsContentByVendor($vendorName);
		if (isset($contentAll[$className]))
		{
			return $contentAll[$className];
		}

		return null;
	}

	/**
	 * Tries to find any icons and save them assets to the block.
	 * @param Block $block Bock instance.
	 * @return void
	 */
	protected static function saveAssets(Block $block): void
	{
		$iconSrc = Config::get('icon_src');
		$iconVendors = Config::get('icon_vendors');
		$blockContent = $block->getContent();

		if (!$iconSrc || !$iconVendors || !$blockContent)
		{
			return;
		}

		$assetsIcon = [];
		$iconVendors = (array) $iconVendors;
		$found = preg_match_all(
			'/[\s"](' . implode('|', $iconVendors) . ')-([^\s"\/\\\]+)/s',
			$blockContent,
			$matches
		);
		if ($found)
		{
			foreach ($matches[0] as $i => $class)
			{
				$vendor = trim($matches[1][$i]);
				$class = trim($class);
				if (!isset($assetsIcon[$vendor]))
				{
					$assetsIcon[$vendor] = [];
				}
				$assetsIcon[$vendor][$class] = self::getIconContentByClass(
					$class,
					$vendor
				);
				if ($assetsIcon[$vendor][$class] === null)
				{
					unset($assetsIcon[$vendor][$class]);
				}
				if (!$assetsIcon[$vendor])
				{
					unset($assetsIcon[$vendor]);
				}
			}
		}

		$block->saveAssets([
			'icon' => $assetsIcon
		]);
	}

	/**
	 * Processing icons in the block content.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
		// find assets always, because block can use icon not only as icon-node, but also just in html
		self::saveAssets($block);
	}

	/**
	 * Processing entire landing.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	public static function processingLanding(int $landingId): void
	{
		$res = BlockTable::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'LID' => $landingId,
				'=DELETED' => 'N'
			]
		]);
		while ($row = $res->fetch())
		{
			$block = new Block($row['ID']);
			self::processing($block);
			$block->save();
		}
	}

	/**
	 * Shows icons styles on the block output.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function view(Block $block): void
	{
		$blockAssets = $block->getAssets();
		if (isset($blockAssets['icon']))
		{
			$assetsManager = Assets\Manager::getInstance();
			$iconSrc = Config::get('icon_src');
			if (!$iconSrc)
			{
				return;
			}
			foreach ($blockAssets['icon'] as $vendorName => $icons)
			{
				// preload woff and/or woff2 fonts
				$fontFile = $iconSrc . $vendorName . '/' . self::ICON_FONT_FILE_NAME;
				$assetsManager->addAsset($fontFile);
				$fontFile2 = $iconSrc . $vendorName . '/' . self::ICON_FONT_FILE_NAME_2;
				$assetsManager->addAsset($fontFile2);

				$stylesFile = $iconSrc . $vendorName . '/' . self::RULE_ICON_FILE_NAME;
				$assetsManager->addAsset($stylesFile);

				$stylesString = '<style>';
				foreach ($icons as $className => $content)
				{
					$stylesString .= '.' . $className . ':before{content:"' . $content . '";}';
				}
				$stylesString .= '</style>';
				$assetsManager->addString($stylesString);
			}
		}
	}
}