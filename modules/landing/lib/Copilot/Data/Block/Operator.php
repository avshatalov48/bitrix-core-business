<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Data\Block;

use Bitrix\Landing\Block;
use Bitrix\Landing\Copilot\Data;
use Bitrix\Landing;

/**
 * Class Operator
 *
 * Provides utility operations for handling blocks within a site.
 */
class Operator
{
	private const ZONES_WHERE_NEED_CHECK_RESPONSE = ['ru'];
	private const DEFAULT_ASPECT_RATIO = '3by2';
	private const DEFAULT_WIDTH = 720;

	public static function getTitleStyleClass(): string
	{
		$titleStyleClasses = [
			'u-heading-v2-0',
			'u-heading-v2-3--bottom',
			'u-heading-v2-8--bottom',
			'u-heading-v2-3--top',
			'u-heading-v2-8--top',
			'u-heading-v2-13-2--left',
			'u-heading-v2-13-2--right',
		];

		return $titleStyleClasses[array_rand($titleStyleClasses)];
	}

	/**
	 * Checks if the number of blocks in the site data matches the number in the JSON response.
	 *
	 * @param Data\Site $siteData The site data object whose block count is being verified.
	 * @param array $json An associative array containing block data from an external response.
	 *
	 * @return bool True if the block counts match; false otherwise.
	 */
	public static function checkBlocksAmount(Data\Site $siteData, array $json): bool
	{
		if (
			!isset($json['blocks'])
			|| !is_array($json['blocks'])
		)
		{
			return false;
		}

		$blocksData = $siteData->getBlocks();

		$countBlocksFromResponse = count($json['blocks']);
		$countBlocksForRequest = 0;
		foreach ($blocksData as $blockData)
		{
			if (!$blockData->isSeparator() && !$blockData->isMenu())
			{
				$countBlocksForRequest++;
			}
		}

		return $countBlocksFromResponse === $countBlocksForRequest;
	}

	/**
	 * Determines if data from the response needs to be checked based on the current zone.
	 *
	 * @return bool True if the data from the response needs to be checked; false otherwise.
	 */
	public static function isNeedCheckDataFromResponse(): bool
	{
		$zone = Landing\Manager::getZone();

		return in_array($zone, self::ZONES_WHERE_NEED_CHECK_RESPONSE, true);
	}

	/**
	 * Prepares node data from the response by processing each data item for the current zone.
	 *
	 * @param array $nodeDataFromResponse An array of data items extracted from the response for a node.
	 *
	 * @return array The prepared data array with any necessary zone-specific adjustments applied.
	 */
	public static function prepareDataFromResponse(array $nodeDataFromResponse): array
	{
		$preparedData = [];
		foreach ($nodeDataFromResponse as $dataItem)
		{
			if (is_string($dataItem))
			{
				$preparedData[] = self::replaceWordsForZone($dataItem);
			}
			if (is_array($dataItem))
			{
				$preparedData[] = $dataItem;
			}
		}

		return $preparedData;
	}


	/**
	 * Retrieves size data images for a specific CSS selector within a block of HTML content.
	 *
	 * @param string $selector The CSS selector used to find elements within the block.
	 * @param string $blockContent The HTML content of the block to search within.
	 *
	 * @return array An array containing the image data related to the specified selector in the block.
	 */
	public static function getSizeDataImagesBySelector(string $selector, string $blockContent): array
	{
		$tagsWithClass = self::findTagsWithClass(substr($selector, 1), $blockContent);
		$defaultImageLinks = self::findImageLinks($tagsWithClass);

		return self::getSizeDataImages($defaultImageLinks);
	}

	/**
	 * Generates default image src and 2x based on image data.
	 *
	 * @param array $dataImages images data for selector in block.
	 *
	 * @return array Array of default image links.
	 */
	public static function getDefaultSrc(array $dataImages): array
	{
		$defaultSrc = [
			'src' => [],
			'src2x' => [],
		];

		$baseUrl = 'https://cdn.bitrix24.site/bitrix/images/landing/default/';

		foreach ($dataImages as $dataImage)
		{
			if (!isset($dataImage['aspectRatio'], $dataImage['width']))
			{
				continue;
			}

			$aspectRatio = $dataImage['aspectRatio'];
			$width = (int)$dataImage['width'];

			$defaultSrc['src'][] = "{$baseUrl}{$aspectRatio}/{$width}.png";
			$defaultSrc['src2x'][] = "{$baseUrl}{$aspectRatio}/" . ($width * 2) . '.png';
		}

		return $defaultSrc;
	}

	/**
	 * Extracts CSS classes associated with a specific node from block content.
	 *
	 * @param string $nodeCode The CSS selector or node code to search for within the block content.
	 * @param string $blockContent The HTML content of the block to search through.
	 *
	 * @return string The extracted class list including the node code, or an empty string if not found.
	 */
	public static function extractNodeClasses(string $nodeCode, string $blockContent): string
	{
		if (str_starts_with($nodeCode, '.'))
		{
			$nodeCode = substr($nodeCode, 1);
		}
		$pattern = '/class="([^"]*' . $nodeCode . '(\s|")(?:|")[^"]*)"/';
		if (preg_match($pattern, $blockContent, $matches))
		{
			return $matches[1];
		}

		return '';
	}

	/**
	 * Extracts wrapper classes from block content.
	 *
	 * @param string $blockContent The content of the block.
	 *
	 * @return string The extracted wrapper classes.
	 */
	public static function extractWrapperClasses(string $blockContent): string
	{
		if (preg_match('/class="([^"]*landing-block[^"]*)"/', $blockContent, $matches))
		{
			return $matches[1];
		}

		return '';
	}

	/**
	 * Provides a map of allowed aspect ratios.
	 *
	 * @return int[] An associative array where keys are aspect ratio labels, and values are their numerical representations.
	 */
	public static function getAllowedAspectRatios(): array
	{
		return [
			'9by16' => 0.56,
			'2by3' => 0.67,
			'1by1' => 1,
			'3by2' => 1.5,
			'16by9' => 1.78,
		];
	}

	public static function isBlockAvailableForScenarioChangeBlock(int $blockId): bool
	{
		$blockInstance = new Block($blockId);
		if ($blockInstance->getId() <= 0)
		{
			return false;
		}

		$blockManifest = $blockInstance->getManifest();
		$blockContent = $blockInstance->getContent();

		$notAllowedSections = [
			'countdowns',
			'separator',
			'menu',
			'sidebar',
			'store',
			'other',
			'video',
		];
		$blockSection = (array)($blockManifest['block']['section'] ?? null);
		$sectionIntersect = array_intersect($notAllowedSections, $blockSection);
		if (!empty($sectionIntersect))
		{
			return false;
		}

		$notAllowedNodeTypes = ['embed'];
		$imageElements = 0;
		foreach ($blockManifest['nodes'] as $node)
		{
			if (isset($node['type']) && in_array($node['type'], $notAllowedNodeTypes, true))
			{
				return false;
			}

			if (isset($node['type']) && $node['type'] === 'img')
			{
				$nodeCode = ltrim($node['code'], '.');
				$imageElements += substr_count($blockContent, $nodeCode . ' ');
				$imageElements += substr_count($blockContent, $nodeCode . '"');
			}
		}

		if ($imageElements > 4)
		{
			return false;
		}

		if (self::hasNotAllowedTagInBlockContent($blockContent))
		{
			return false;
		}

		if ($blockInstance->getRepoId())
		{
			return false;
		}

		return true;
	}

	private static function hasNotAllowedTagInBlockContent(string $blockContent): bool
	{
		$notAllowedTags = ['table'];

		foreach ($notAllowedTags as $tag)
		{
			if (preg_match('/<\s*' . preg_quote($tag, '/') . '\b/i', $blockContent))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Replaces specific words in a data item based on the current zone.
	 *
	 * @param string $dataItem The original data string that may contain words needing replacement.
	 *
	 * @return string The modified string with any applicable zone-specific substitutions applied.
	 */
	private static function replaceWordsForZone(string $dataItem): string
	{
		$zone = Landing\Manager::getZone();

		$replacementMap = [
			'ru' => [
				'fa-square-instagram' => 'fa-pinterest',
				'fa-instagram' => 'fa-pinterest',
				'fa-meta' => 'fa-vk',
				'fa-square-facebook' => 'fa-vk',
				'fa-facebook-messenger' => 'fa-vk',
				'fa-facebook-f' => 'fa-vk',
				'fa-facebook' => 'fa-vk',
				'Instagram' => 'Pinterest',
				'Facebook' => 'VK',
			],
		];

		if (isset($replacementMap[$zone]))
		{
			foreach ($replacementMap[$zone] as $search => $replace)
			{
				$dataItem = preg_replace_callback(
					'/' . preg_quote($search, '/') . '/i',
					static function() use ($replace) {
						return $replace;
					},
					$dataItem
				);
			}
		}

		return $dataItem;
	}

	/**
	 * Finds HTML tags with a specific class in the content block.
	 *
	 * @param string $class The class to search for.
	 * @param string $contentBlock The content block to search within.
	 *
	 * @return array Array of tags containing the specified class.
	 */
	private static function findTagsWithClass(string $class, string $contentBlock): array
	{
		$patternPart1 = '/<([a-z]+)\b[^>]*class=["][^"]*\b';
		$patternPart2 = '\b(?:["\s][^"]*)?["][^>]*>/i';
		$pattern = $patternPart1 . $class . $patternPart2;

		preg_match_all($pattern, $contentBlock, $matches, PREG_SET_ORDER);

		$tags = [];
		foreach ($matches as $match)
		{
			$tags[] = $match[0];
		}

		return $tags;
	}

	/**
	 * Finds image links within HTML tags.
	 *
	 * @param array $tags Array of HTML tags to search for image links.
	 *
	 * @return array Array of image links found within the tags.
	 */
	private static function findImageLinks(array $tags): array
	{
		$links = [];

		foreach ($tags as $tag)
		{
			$pattern = '/\b(?:https?:\/\/|www\.)\S+\.(?:jpg|jpeg|png)(?=\b|[^a-z0-9])/i';
			if (preg_match($pattern, $tag, $match))
			{
				$links[] = $match[0];
			}
			else
			{
				$links[] = '';
			}
		}

		return $links;
	}

	/**
	 * Retrieves size data images based on their default links.
	 *
	 * @param array $defaultImageLinks Array of default image links.
	 *
	 * @return array Array of image data including aspect ratio and width.
	 */
	private static function getSizeDataImages(array $defaultImageLinks): array
	{
		$imagesData = [];

		foreach ($defaultImageLinks as $defaultImageLink)
		{
			[$width, $height, $aspectRatio] = Images::getImageData($defaultImageLink);

			$closestAspectRatioKey = self::getClosestAspectRatioKey($aspectRatio);

			$closestWidth = self::getClosestWidth((int)$width, $closestAspectRatioKey);

			$imagesData[] = [
				'width' => $closestWidth, // value from default copilot image
				'height' => $height, // todo: value from default url in block
				'aspectRatio' => $closestAspectRatioKey, // value from default copilot image
			];
		}

		return $imagesData;
	}

	/**
	 * Provides a map of allowed widths for each aspect ratio.
	 *
	 * @return array An associative array where keys are aspect ratio labels, and values are arrays of allowed widths.
	 */
	private static function getAllowedWidths(): array
	{
		return [
			'9by16' => ['180', '270', '360', '450', '540', '720'],
			'2by3' => ['240', '320', '360', '480', '600', '720', '880'],
			'1by1' => ['180', '256', '512', '720', '1024', '1280', '1440', '1920'],
			'3by2' => ['360', '480', '540', '720', '900', '1080', '1320', '1620', '1920'],
			'16by9' => ['320', '450', '480', '640', '800', '960', '1280', '1440', '1600', '1920'],
		];
	}

	/**
	 * Get the closest matching aspect ratio from allowed aspect ratios.
	 *
	 * @param float $aspectRatio The calculated aspect ratio of an image.
	 *
	 * @return string|null The key of the closest aspect ratio, or null if none match closely.
	 */
	public static function getClosestAspectRatioKey(float $aspectRatio): ?string
	{
		$allowedAspectRatios = self::getAllowedAspectRatios();
		$closestAspectRatioKey = null;
		$closestAspectRatioDiff = PHP_FLOAT_MAX;

		foreach ($allowedAspectRatios as $key => $value)
		{
			$diff = abs($aspectRatio - $value);
			if ($diff < $closestAspectRatioDiff)
			{
				$closestAspectRatioDiff = $diff;
				$closestAspectRatioKey = $key;
			}
		}

		return $closestAspectRatioKey;
	}

	/**
	 * Get random avatar links set by gender data.
	 *
	 * @param array $gendersData Array of gender data.
	 *
	 * @return array of default avatar links.
	 */
	public static function getAvatarLinks(array $gendersData): array
	{
		$genderMaxValues = [
			'male'   => 50,
			'female' => 52,
			'unisex' => 18,
		];

		$links = [];

		foreach ($gendersData as $gender)
		{
			$gender = in_array($gender, ['male', 'female']) ? $gender : 'unisex';

			if (!isset($usedRandInt[$gender]))
			{
				$usedRandInt[$gender] = [];
			}

			do
			{
				$randInt = rand(1, $genderMaxValues[$gender]);
			}
			while (in_array($randInt, $usedRandInt[$gender], true));

			$usedRandInt[$gender][] = $randInt;

			$links[] = 'https://cdn.bitrix24.site/bitrix/images/landing/default/' . $gender . '/' . $randInt . '.png';
		}

		return $links;
	}

	public static function getDefaultAspectRatio(): string
	{
		return self::DEFAULT_ASPECT_RATIO;
	}

	public static function getDefaultWidth(string $aspectRatio): int
	{
		$allowedWidths = self::getAllowedWidths();

		if (!isset($allowedWidths[$aspectRatio]))
		{
			return self::DEFAULT_WIDTH;
		}

		$widths = $allowedWidths[$aspectRatio];
		$middleIndex = (int)(count($widths) / 2);

		return (int)$widths[$middleIndex];
	}

	public static function getDefaultAvatarLinks(array $gendersData): array
	{
		$defaultAvatarLink = 'https://cdn.bitrix24.site/bitrix/images/landing/default/1by1/512.png';
		$defaultAvatarLink2x = 'https://cdn.bitrix24.site/bitrix/images/landing/default/1by1/1024.png';
		$links = [];
		$countLinks = count($gendersData);
		for ($i = 0; $i < $countLinks; $i++)
		{
			$links[] = [
				'src' => $defaultAvatarLink,
				'src2x' => $defaultAvatarLink2x,
			];
		}

		return $links;
	}

	/**
	 * Determines the closest allowed width for the image based on the aspect ratio.
	 *
	 * @param int $width The current width of the image.
	 * @param string|null $closestAspectRatioKey The key of the closest aspect ratio.
	 *
	 * @return int|null The closest allowed width, or null if no suitable width is found.
	 */
	public static function getClosestWidth(int $width, ?string $closestAspectRatioKey): ?int
	{
		$allowedWidths = self::getAllowedWidths();

		if ($closestAspectRatioKey === null)
		{
			return null;
		}

		$closest = $allowedWidths[$closestAspectRatioKey][0] ?? null;
		$smallestDifference = abs($width - $closest);

		foreach ($allowedWidths[$closestAspectRatioKey] as $allowedWidth)
		{
			$difference = abs($width - $allowedWidth);
			if ($difference < $smallestDifference)
			{
				$smallestDifference = $difference;
				$closest = $allowedWidth;
			}
		}

		return (int)$closest;
	}
}