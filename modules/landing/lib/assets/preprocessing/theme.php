<?php
namespace Bitrix\Landing\Assets\PreProcessing;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Manager;

class Theme
{
	/**
	 * Manifest path template.
	 */
	const FILE_PATH_SITE_MANIFEST = '/bitrix/components/bitrix/landing.demo/data/site/#code#/.theme.php';

	/**
	 * Default page template for page creating.
	 */
	const DEFAULT_PAGE_TEMPLATE = 'empty';

	/**
	 * Returns all class attribute from block content.
	 * @param string $content Block content.
	 * @return array
	 */
	private static function getStyleClasses(string $content): array
	{
		if (preg_match_all('/class="([^"]+)"/', $content, $matches))
		{
			$allClasses = [];
			foreach ($matches[1] as $classes)
			{
				// some hack for future search optimization
				$allClasses[] = ' ' . $classes . ' ';
			}
			return $allClasses;
		}
		return [];
	}

	/**
	 * Returns manifest array by template code.
	 * @param string $tplCode Template code.
	 * @return array
	 */
	private static function getThemeManifest(string $tplCode): array
	{
		$path = self::FILE_PATH_SITE_MANIFEST;
		$path = Manager::getDocRoot() . str_replace('#code#', $tplCode, $path);
		if (file_exists($path))
		{
			$manifest = include $path;
			if (is_array($manifest))
			{
				return $manifest;
			}
		}
		return [];
	}

	/**
	 * Removes siblings classes and returns new class attribute.
	 * @param string $classString Attribute class string.
	 * @param array $targetClasses New classes for this attribute.
	 * @param string $namespace Namespace for getting styles data.
	 * @return string
	 */
	private static function removeSiblingsClasses(string $classString, array $targetClasses, string $namespace): string
	{
		static $classesGroups = [];

		$styleManifest = Block::getStyle();

		// build classes groups (static cache)
		if (!array_key_exists($namespace, $classesGroups))
		{
			$classesGroups[$namespace] = [];
			if (
				isset($styleManifest[$namespace]['style']) &&
				is_array($styleManifest[$namespace]['style'])
			)
			{
				foreach ($styleManifest[$namespace]['style'] as $style)
				{
					if (isset($style['items']) && is_array($style['items']))
					{
						$classesGroup = [];
						foreach ($style['items'] as $item)
						{
							if (isset($item['value']) && is_string($item['value']))
							{
								$classesGroup[] = trim($item['value']);
							}
						}
						if ($classesGroup)
						{
							$classesGroups[$namespace][] = $classesGroup;
						}
					}
				}
			}
		}

		$allClasses = $classesGroups[$namespace];

		// local function to find siblings
		$findSiblings = function($targetClass) use($allClasses)
		{
			$targetClass = trim($targetClass);
			foreach ($allClasses as $classes)
			{
				if (in_array($targetClass, $classes))
				{
					return $classes;
				}
			}

			return [];
		};

		// try to find siblings of each target class
		foreach ($targetClasses as $targetClass)
		{
			// and remove from class attribute
			foreach ($findSiblings($targetClass) as $classRemove)
			{
				$classString = str_replace(' ' . $classRemove . ' ', ' ', $classString);
			}
		}


		return trim($classString);
	}

	/**
	 * Processing theme manifest.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
		// first we check all we need
		
		$blockMetadata = $block->getMeta();
		if (!$blockMetadata['SITE_TPL_CODE'])
		{
			return;
		}

		$themeManifest = self::getThemeManifest($blockMetadata['SITE_TPL_CODE']);
		if (
			isset($themeManifest['newBlockStyle']) &&
			is_array($themeManifest['newBlockStyle'])
		)
		{
			$themeManifest = $themeManifest['newBlockStyle'];
		}
		else
		{
			return;
		}

		$contentWasChanged = false;
		$blockContent = $block->getContent();
		$blockClasses = self::getStyleClasses($blockContent);
		if (!$blockClasses)
		{
			return;
		}

		$blockManifest = $block->getManifest();
		if (!isset($blockManifest['namespace']))
		{
			return;
		}

		$blockNamespace = $blockManifest['namespace'];
		$semanticManifest = Block::getSemantic();
		if (isset($semanticManifest[$blockNamespace]))
		{
			$semanticManifest = $semanticManifest[$blockNamespace];
		}
		else
		{
			return;
		}

		// work with theme manifest
		foreach ($themeManifest as $semanticCode => $needClasses)
		{
			if (!isset($semanticManifest[$semanticCode]))
			{
				continue;
			}
			if (!is_array($needClasses))
			{
				$needClasses = (array)$needClasses;
			}

			// by specific style class we redefine some classes
			foreach ((array) $semanticManifest[$semanticCode] as $semanticClass)
			{
				$semanticClass = ' ' . $semanticClass . ' ';
				foreach ($blockClasses as $classesString)
				{
					if (mb_strpos($classesString, $semanticClass) !== false)
					{
						$contentWasChanged = true;
						$newClassString = self::removeSiblingsClasses(
							$classesString,
							$needClasses,
							$blockNamespace
						);
						$blockContent = str_replace(
							'class="' . trim($classesString) . '"',
							'class="' . $newClassString . ' ' . implode(' ', $needClasses) . '"',
							$blockContent
						);
					}
				}
			}
		}

		// save content to the block
		if ($contentWasChanged)
		{
			$block->saveContent($blockContent);
		}
	}

	/**
	 * Finds new page template in site manifest, returns DEFAULT_PAGE_TEMPLATE by default.
	 * @param int $siteId Site id.
	 * @return string
	 */
	public static function getNewPageTemplate(int $siteId): string
	{
		static $sites = [];

		if (!array_key_exists($siteId, $sites))
		{
			$sites[$siteId] = null;
			$res = Site::getList([
				'select' => [
					'XML_ID', 'TPL_CODE'
				],
				'filter' => [
					'ID' => $siteId
				]
			]);
			if ($row = $res->fetch())
			{
				if (!$row['TPL_CODE'] && mb_strpos($row['XML_ID'], '|'))
				{
					[, $row['TPL_CODE']] = explode('|', $row['XML_ID']);
				}
				if ($row['TPL_CODE'])
				{
					$manifest = self::getThemeManifest($row['TPL_CODE']);
					if (
						isset($manifest['newPageTemplate'][0]) &&
						is_string($manifest['newPageTemplate'][0])
					)
					{
						$sites[$siteId] = $manifest['newPageTemplate'][0];
					}
				}
			}
		}

		if ($sites[$siteId])
		{
			return $sites[$siteId];
		}

		return self::DEFAULT_PAGE_TEMPLATE;
	}
}