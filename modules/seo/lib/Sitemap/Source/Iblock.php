<?php

namespace Bitrix\Seo\Sitemap\Source;

use Bitrix\Main\SiteTable;
use Bitrix\Seo\Sitemap\File;
use Bitrix\Seo\Sitemap\Internals\IblockTable;
use Bitrix\Seo\RobotsFile;
use Bitrix\Seo\Sitemap\Internals\SitemapTable;
use Bitrix\Seo\Sitemap\Job;

class Iblock
{
	/**
	 * Event handler for multiple IBlock events
	 */
	public static function __callStatic($name, $arguments)
	{
		$name = mb_strtoupper($name);
		switch ($name)
		{
			case 'ADDELEMENT':
			case 'ADDSECTION':

				$fields = $arguments[0];
				if (
					$fields["ID"] > 0
					&& $fields['IBLOCK_ID'] > 0
					&& (
						!isset($fields['ACTIVE']) || $fields['ACTIVE'] == 'Y'
					)
				)
				{
					self::processIblock($fields['IBLOCK_ID']);
				}
				break;

			case 'DELETEELEMENT':
			case 'DELETESECTION':
			case 'UPDATEELEMENT':
			case 'UPDATESECTION':

				$fields = $arguments[0];
				if (is_array($fields) && $fields['ID'] > 0 && $fields['IBLOCK_ID'] > 0)
				{
					if (!isset($fields['RESULT']) || $fields['RESULT'] !== false)
					{
						self::processIblock($fields['IBLOCK_ID']);
					}
				}

				break;
		}
	}

	protected static function processIblock(int $iblockId): void
	{
		$iblocksForSitemap = IblockTable::query()
			->setSelect(['SITEMAP_ID'])
			->where('IBLOCK_ID', $iblockId)
			->exec()
		;

		foreach ($iblocksForSitemap as $iblock)
		{
			Job::markToRegenerate($iblock['SITEMAP_ID']);
		}
	}

	/**
	 * Replace some parts of URL-template, then not correct processing in replaceDetailUrl.
	 *
	 * @param string $url - String of URL-template.
	 * @param null $siteId - In NULL - #SERVER_NAME# will not replaced.
	 * @return mixed|string
	 */
	public static function prepareUrlToReplace($url, $siteId = null)
	{
		// REMOVE PROTOCOL - we put them later, based on user settings
		$url = str_replace(['http://', 'https://'], '', $url);

		// REMOVE SERVER_NAME from start position, because we put server_url later
		if (mb_substr($url, 0, mb_strlen('#SERVER_NAME#')) == '#SERVER_NAME#')
		{
			$url = mb_substr($url, mb_strlen('#SERVER_NAME#'));
		}

		// get correct SERVER_URL
		if ($siteId)
		{
			$filter = ['=LID' => $siteId];
			$dbSite = SiteTable::getList(array(
				'filter' => $filter,
				'select' => array('LID', 'DIR', 'SERVER_NAME'),
			));
			$currentSite = $dbSite->fetch();
			$serverName = $currentSite['SERVER_NAME'];
			$url = str_replace('#SERVER_NAME#', $serverName, $url);
		}

		return $url;
	}
}
