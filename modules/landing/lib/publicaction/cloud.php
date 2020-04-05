<?php
namespace Bitrix\Landing\PublicAction;

class Cloud
{
	/**
	 * Get blocks from repository.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getRepository()
	{
		return Block::getRepository(null, true);
	}

	/**
	 * Get demo sites.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getDemoSiteList($type)
	{
		return Demos::getSiteList($type);
	}

	/**
	 * Get demo pages.
	 * @param string $type Type of demo-template (page, store, etc...).
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getDemoPageList($type)
	{
		return Demos::getPageList($type);
	}

	/**
	 * Get preview of url by code.
	 * @param string $code Code of page.
	 * @param string $type Code of content.
	 * @return \Bitrix\Landing\PublicActionResult
	 */
	public static function getUrlPreview($code, $type)
	{
		return Demos::getUrlPreview($code, $type);
	}
}