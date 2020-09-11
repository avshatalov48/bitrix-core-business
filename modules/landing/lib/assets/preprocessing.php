<?php

namespace Bitrix\Landing\Assets;

use \Bitrix\Landing\Block;
use \Bitrix\Crm\CompanyTable;
use \Bitrix\Landing\Site;
use \Bitrix\Main\Loader;

class PreProcessing
{
	/**
	 * Processing the block on adding.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockAddProcessing(Block $block): void
	{
		PreProcessing\Theme::processing($block);
		PreProcessing\Icon::processing($block);
		PreProcessing\Font::processing($block);
		PreProcessing\CrmContacts::processing($block);
	}

	/**
	 * Processing the block on nodes updating.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockUpdateNodeProcessing(Block $block): void
	{
		PreProcessing\Icon::processing($block);
		PreProcessing\Font::processing($block);
		PreProcessing\CrmContacts::processing($block);
	}

	/**
	 * Processing the block on classes updating.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockUpdateClassesProcessing(Block $block): void
	{
		PreProcessing\Font::processing($block);
	}

	/**
	 * Processing the block on undeleting.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockUndeleteProcessing(Block $block): void
	{
		PreProcessing\Icon::processing($block);
		PreProcessing\Font::processing($block);
	}

	/**
	 * Processing the block on output.
	 * @param Block $block Block instance.
	 * @param bool $editMode Edit mode.
	 * @return void
	 */
	public static function blockViewProcessing(Block $block, bool $editMode = false): void
	{
		if (!$editMode)
		{
			PreProcessing\Icon::view($block);
			PreProcessing\CustomExtensions::view($block);
		}
		PreProcessing\Font::view($block);
	}

	/**
	 * Processing the block on publication.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockPublicationProcessing(Block $block): void
	{
		if (self::isLazyloadEnable($block->getSiteId()))
		{
			PreProcessing\Lazyload::processing($block);
		}
		PreProcessing\CustomExtensions::processing($block);
	}

	/**
	 * Processing the dynamic setting to the block.
	 * @param Block $block Block instance.
	 * @return void
	 */
	public static function blockSetDynamicProcessing(Block $block): void
	{
		if (self::isLazyloadEnable($block->getSiteId()))
		{
			PreProcessing\Lazyload::processingDynamic($block);
		}
	}

	/**
	 * Check Speed Use Lazy hook.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	protected static function isLazyloadEnable(int $siteId): bool
	{
		static $result;
		if ($result !== null)
		{
			return $result;
		}

		$hooks = Site::getHooks($siteId);
		$result =
			array_key_exists('SPEED', $hooks)
			&& $hooks['SPEED']->getPageFields()['SPEED_USE_LAZY']->getValue() !== 'N';

		return $result;
	}
}