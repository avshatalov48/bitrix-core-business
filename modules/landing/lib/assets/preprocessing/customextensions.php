<?php

namespace Bitrix\Landing\Assets\PreProcessing;

use Bitrix\Landing\Assets;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Internals\BlockTable;
use Bitrix\Main\Web\Json;

class CustomExtensions
{
	protected const EXT_POPUP = 'landing_popup_link';
	protected const EXT_JQUERY = 'landing_jquery';
	protected const CRITICAL_EXTENSIONS = ['landing_jquery'];
	protected const HTML_BLOCK_CODE = 'html';

	/**
	 * @param Block $block Bock instance.
	 * @return void
	 */
	protected static function saveExtensions(Block $block): void
	{
		$newExtensions = [];
		$content = $block->getContent();
		if (!$content)
		{
			return;
		}

		// <a href=> and data-url=""
		if (preg_match('/target=["\']_popup["\']/i', $content))
		{
			$newExtensions[] = self::EXT_POPUP;
		}
		// pseudo-urls
		if(preg_match_all('/data-pseudo-url=["\'][^"\']*_popup[^"\']*["\']/i', $content, $pseudoUrls))
		{
			foreach ($pseudoUrls[0] as $pseudoUrl)
			{
				$params = htmlspecialcharsback($pseudoUrl);
				$params = str_replace('data-pseudo-url=', '', $params);
				$params = substr($params,0,-1);
				$params = substr($params,1);
				// preserve JSON syntax error if params in wrong format
				try
				{
					$params = Json::decode($params);
					if($params['enabled'])
					{
						$newExtensions[] = self::EXT_POPUP;
						break;
					}
				}
				catch (\Exception $e){}
			}
		}

		// for partners using jQuery always
		// todo: add flag 'no_jq' for different jq?
		if ($block->getRepoId())
		{
			$newExtensions[] = self::EXT_JQUERY;
		}

		if ($block->getCode() === self::HTML_BLOCK_CODE)
		{
			$newExtensions[] = self::EXT_JQUERY;
		}

		if (!empty($newExtensions))
		{
			$extensions = $block->getAsset()['ext'] ?: [];
			$extensions = array_merge($newExtensions, $extensions);
			$block->saveAssets([
				'ext' => array_unique($extensions)
			]);
			$block->save();
		}
	}

	/**
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
		self::saveExtensions($block);
	}

	/**
	 * Processing entire landing.
	 * @param int $landingId Landing id.
	 * @return void
	 */
	// todo: add version landing updater
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
	 * Shows fonts on the block output.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function view(Block $block): void
	{
		$blockAssets = $block->getAssets();
		if (isset($blockAssets['ext']))
		{
			$assets = Assets\Manager::getInstance();
			foreach ($blockAssets['ext'] as $ext)
			{
				$location = Assets\Location::getDefaultLocation();
				if (in_array($ext, self::CRITICAL_EXTENSIONS, true))
				{
					$location = Assets\Location::LOCATION_BEFORE_ALL;
				}
				$assets->addAsset($ext, $location);
			}
		}
	}
}