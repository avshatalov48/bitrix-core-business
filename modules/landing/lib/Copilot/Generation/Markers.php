<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation;

use Bitrix\Landing\Copilot\Converter;
use Bitrix\Landing\Copilot\Data;

class Markers
{
	//promptCode: landing_ai_data
	private const LABELS_SITE_DATA = [
		'siteConfig' => 'site_config',
		'siteDataOnly' => 'json_schema_site_data_only',
	];

	//promptCode: landing_ai_content
	private const LABELS_SITE_CONTENT = [
		'siteDataWithBlocks' => 'input_json_site_data_with_blocks',
		'blocks' => 'json_schema_blocks',
		'date' => 'date',
	];

	//promptCode: landing_ai_block_content
	private const LABELS_BLOCK_CONTENT = [
		'siteTitle' => 'site_title',
		'userQuery' => 'user_query',
		'inputJson' => 'input_json',
		'jsonSchema' => 'json_schema',
	];

	/**
	 * Generates an array of prompt markers for the site's data using compressed JSON strings.
	 *
	 * @param Data\Site $siteData The site data object to transform into prompt markers.
	 *
	 * @return array An associative array containing compressed site configuration and data skeletons keyed by template labels.
	 */
	public static function getSiteDataPromptMarkers(Data\Site $siteData): array
	{
		$siteJsonString = Converter\Json::getSiteJsonString($siteData, 'siteData');
		$siteJsonStringCompressed = Converter\Json::compressJsonString($siteJsonString);
		$skeletonForResponse = Converter\Json::getSkeletonForSiteDataResponse();

		return [
			self::LABELS_SITE_DATA['siteConfig'] => $siteJsonStringCompressed,
			self::LABELS_SITE_DATA['siteDataOnly'] => $skeletonForResponse,
		];
	}

	/**
	 * Generates an array of prompt markers for the site's block content and images, using compressed JSON strings.
	 * This function processes the site data object to extract block content and image node information, combining node values and additional site data.
	 *
	 * @param Data\Site $siteData The site data object containing block content and image node information to be processed.
	 *
	 * @return array An associative array with compressed block content data, image-node related data, and other site properties, keyed by template labels.
	 */
	public static function getSiteContentPromptMarkers(Data\Site $siteData): array
	{
		$siteJsonString = Converter\Json::getSiteJsonString($siteData, 'siteContent');
		$siteJsonStringCompressed = Converter\Json::compressJsonString($siteJsonString);
		$skeletonForResponse = Converter\Json::getSkeletonForBlocksContentResponse($siteJsonStringCompressed);

		return [
			self::LABELS_SITE_CONTENT['siteDataWithBlocks'] => $siteJsonStringCompressed,
			self::LABELS_SITE_CONTENT['blocks'] => $skeletonForResponse,
			self::LABELS_SITE_CONTENT['date'] => date('d.m.Y'),
		];
	}

	/**
	 * Generates an array of prompt markers related to block content, utilizing site data to create JSON strings and response skeletons.
	 *
	 * @param Data\Site $siteData The site data object from which block-related information is extracted.
	 *
	 * @return array An associative array containing block-related data, keyed by template labels.
	 * The array includes the main user query, a JSON string representation of the blocks,
	 * and a JSON schema for the block content response.
	 */
	public static function getBlockContentPromptMarkers(Data\Site $siteData): array
	{
		$mainWish = $siteData->getWishes()->getWishes()[0] ?? '';
		$blockJsonString = Converter\Json::getBlockJsonString($siteData->getBlocks());
		$skeletonForResponse = Converter\Json::getSkeletonForBlockContentResponse($blockJsonString);

		return [
			self::LABELS_BLOCK_CONTENT['siteTitle'] => $siteData->getSiteTitle(),
			self::LABELS_BLOCK_CONTENT['userQuery'] => $mainWish,
			self::LABELS_BLOCK_CONTENT['inputJson'] => $blockJsonString,
			self::LABELS_BLOCK_CONTENT['jsonSchema'] => $skeletonForResponse,
		];
	}
}