<?php
namespace Bitrix\Landing\Assets\PreProcessing;

use \Bitrix\Landing\Block;
use \Bitrix\Landing\Internals\BlockTable;

class Font
{
	/**
	 * Tries to find google fonts classes and save them assets to the block.
	 * @param Block $block Bock instance.
	 * @return void
	 */
	protected static function saveAssets(Block $block): void
	{
		$blockContent = $block->getContent();
		if (!$blockContent)
		{
			return;
		}

		$fonts = [];
		$found = preg_match_all(
			'/[\s"](g-font-[^\s"]+)/s',
			$blockContent,
			$matches
		);
		if ($found)
		{
			foreach ($matches[1] as $font)
			{
				if (
					strpos($font, 'g-font-size-') !== 0
					&& strpos($font, 'g-font-weight-') !== 0
					&& strpos($font, 'g-font-style-') !== 0
				)
				{
					$fonts[] = $font;
				}
			}
		}
		$block->saveAssets([
			'font' => array_unique($fonts)
		]);
	}

	/**
	 * Processing fonts in the block content.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function processing(Block $block): void
	{
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
	 * Shows fonts on the block output.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function view(Block $block): void
	{
		$blockAssets = $block->getAssets();
		if (isset($blockAssets['font']))
		{
			foreach ($blockAssets['font'] as $fontCode)
			{
				\Bitrix\Landing\Hook\Page\Fonts::setFontCode($fontCode);
			}
		}
	}
}