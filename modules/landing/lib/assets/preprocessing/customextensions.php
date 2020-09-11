<?php
namespace Bitrix\Landing\Assets\PreProcessing;

use Bitrix\Landing\Assets\Manager;
use \Bitrix\Landing\Block;
use \Bitrix\Landing\Internals\BlockTable;

class CustomExtensions
{
	protected const EXT_POPUP = 'landing_popup_link';
	protected const EXT_JQUERY = 'landing_jquery';

	/**
	 * @param Block $block Bock instance.
	 * @return void
	 */
	protected static function saveExtensions(Block $block): void
	{
		$newExtensions = [];
		$content = $block->getContent();

		// todo: decode and check "enable" flag
		if (
			preg_match('/target=["\']_popup["\']/i', $content)
			|| preg_match('/data-pseudo-url=["\'][^"\']*target&quot;:&quot;_popup[^"\']*["\']/i', $content)
		)
		{
			$newExtensions[] = self::EXT_POPUP;
		}

		// for partners using jQuery always
		// todo: add flag 'no_jq' for different jq?
		if($block->getRepoId())
		{
			$newExtensions[] = self::EXT_JQUERY;
		}

		if(!empty($newExtensions))
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
			$assets = Manager::getInstance();
			foreach ($blockAssets['ext'] as $ext)
			{
				$assets->addAsset($ext);
			}
		}
	}
}