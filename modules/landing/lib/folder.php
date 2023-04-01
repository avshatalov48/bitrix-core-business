<?php
namespace Bitrix\Landing;

class Folder extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'FolderTable';

	/**
	 * Deletes all folders for site.
	 * @param int $siteId Site id.
	 * @return void
	 */
	public static function deleteForSite(int $siteId): void
	{
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'SITE_ID' => $siteId
			]
		]);
		while ($row = $res->fetch())
		{
			parent::delete($row['ID']);
		}
	}

	/**
	 * Changes SITE_ID for folder, all sub folders, all sub landings.
	 * @param int $folderId Folder id.
	 * @param int $newSiteId New folder site id.
	 * @return void
	 */
	public static function changeSiteIdRecursive(int $folderId, int $newSiteId)
	{
		// move sub folders
		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'PARENT_ID' => $folderId
			]
		]);
		while ($row = $res->fetch())
		{
			$resAffected = self::update($row['ID'], [
				'SITE_ID' => $newSiteId
			]);
			if ($resAffected->isSuccess())
			{
				self::changeSiteIdRecursive($row['ID'], $newSiteId);
			}
		}

		// move sub landings
		$res = Landing::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'FOLDER_ID' => $folderId
			]
		]);
		while ($row = $res->fetch())
		{
			Landing::update($row['ID'], [
				'SITE_ID' => $newSiteId
			]);
		}

	}

	/**
	 * Recursively collects all subfolder ids for specific folder.
	 *
	 * @param int $folderId Folder id.
	 * @return array
	 */
	public static function getSubFolderIds(int $folderId): array
	{
		$ids = [];

		$res = self::getList([
			'select' => ['ID'],
			'filter' => [
				'PARENT_ID' => $folderId,
			],
		]);
		while ($row = $res->fetch())
		{
			array_push($ids, $row['ID'], ...self::getSubFolderIds($row['ID']));
		}

		return $ids;
	}

	/**
	 * Returns site's folders id.
	 * @param int $siteId Site id.
	 * @param array $additionalFilter Optional additional filter.
	 * @return array
	 */
	public static function getFolderIdsForSite(int $siteId, array $additionalFilter = []): array
	{
		$return = [];
		$additionalFilter['SITE_ID'] = $siteId;

		$res = self::getList([
			'select' => [
				'ID'
			],
			'filter' => $additionalFilter
		]);
		while ($row = $res->fetch())
		{
			$return[] = $row['ID'];
		}

		return $return;
	}

	/**
	 * Returns breadcrumbs for folder of site.
	 * @param int $folderId Folder id.
	 * @param int|null $siteId Site id (optional, but desirable for optimisation).
	 * @return array
	 */
	public static function getBreadCrumbs(int $folderId, ?int $siteId = null): array
	{
		static $cacheFolders = [];
		$crumbs = [];

		if (!$siteId)
		{
			$res = self::getList([
				'select' => [
					'SITE_ID'
				],
				'filter' => [
					'ID' => $folderId
				],
				'limit' => 1
			]);
			if ($row = $res->fetch())
			{
				$siteId = $row['SITE_ID'];
			}
		}

		if (!$siteId)
		{
			return $crumbs;
		}

		// get all folder's chunks for the site
		if (!array_key_exists($siteId, $cacheFolders))
		{
			$cacheFolders[$siteId] = [];
			$res = self::getList([
				'select' => [
					'ID', 'TITLE', 'INDEX_ID', 'CODE',
					'PARENT_ID', 'ACTIVE', 'DELETED'
				],
				'filter' => [
					'SITE_ID' => $siteId
				]
			]);
			while ($row = $res->fetch())
			{
				$cacheFolders[$siteId][$row['ID']] = $row;
			}
		}

		if (!$cacheFolders[$siteId])
		{
			return $crumbs;
		}

		// build recursively
		$folders = $cacheFolders[$siteId];
		do
		{
			if (!isset($folders[$folderId]))
			{
				break;
			}
			$crumbs[] = $folders[$folderId];
			$folderId = $folders[$folderId]['PARENT_ID'];
		}
		while ($folderId);

		return array_reverse($crumbs);
	}

	/**
	 * Returns breadcrumbs for folder of site as string.
	 * @param int $folderId Folder id.
	 * @param string $glue Glue for implode breadcrumbs.
	 * @param int|null $siteId Site id (optional, but desirable for optimisation).
	 * @return string
	 */
	public static function getBreadCrumbsString(int $folderId, string $glue, ?int $siteId = null): string
	{
		$path = [];
		$crumbs = self::getBreadCrumbs($folderId, $siteId);
		foreach ($crumbs as $crumb)
		{
			$path[] = $crumb['TITLE'];
		}
		return implode($glue, $path);
	}

	/**
	 * Returns folder's full path with parents folders.
	 * @param int $folderId Folder id.
	 * @param int|null $siteId Site id (optional, but desirable for optimisation).
	 * @param array &$lastFolder Last folder item.
	 * @return string
	 */
	public static function getFullPath(int $folderId, ?int $siteId = null, array &$lastFolder = []): string
	{
		$codes = [];

		foreach (self::getBreadCrumbs($folderId, $siteId) as $folder)
		{
			$lastFolder = $folder;
			$codes[] = $folder['CODE'];
		}

		return '/' . implode('/', $codes) . '/';
	}
}