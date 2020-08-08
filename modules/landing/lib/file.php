<?php
namespace Bitrix\Landing;

use \Bitrix\Landing\Internals\FileTable;

class File
{
	/**
	 * Entity type site.
	 */
	const ENTITY_TYPE_SITE  = 'S';

	/**
	 * Entity type landing.
	 */
	const ENTITY_TYPE_LANDING = 'L';

	/**
	 * Entity type block.
	 */
	const ENTITY_TYPE_BLOCK = 'B';
	
	/**
	 * Entity type asset.
	 */
	const ENTITY_TYPE_ASSET = 'A';

	/**
	 * Returns sanitized file name.
	 * @param string $fileName File name.
	 * @return string
	 */
	public static function sanitizeFileName(string $fileName): string
	{
		return preg_replace(
			'/[\(\)\s]+/s',
			'_',
			$fileName
		);
	}

	/**
	 * Add new record.
	 * @param int $fileId File id.
	 * @param int $entityId Entity id.
	 * @param int $entityType Entity type.
	 * @return void
	 */
	protected static function add($fileId, $entityId, $entityType)
	{
		$res = FileTable::getList(array(
			'select' => array(
				'ID'
			),
			'filter' => array(
				'FILE_ID' => $fileId,
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			)
		));
		if (!$res->fetch())
		{
			$res = FileTable::add(array(
				'FILE_ID' => $fileId,
				'ENTITY_ID' => $entityId,
				'ENTITY_TYPE' => $entityType
			));
			$res->isSuccess();
		}
	}

	/**
	 * Get all files id from entity.
	 * @param int $entityId Entity id.
	 * @param int $entityType Entity type.
	 * @return array
	 */
	protected static function getFiles($entityId, $entityType)
	{
		$files = [];
		$res = FileTable::getList(array(
			'select' => array(
				'FILE_ID'
			),
			'filter' => array(
				'ENTITY_ID' => $entityId,
				'=ENTITY_TYPE' => $entityType
			)
		));
		while ($row = $res->fetch())
		{
			$files[] = $row['FILE_ID'];
		}
		return $files;
	}

	/**
	 * Mark records for delete.
	 * @param int|array $fileId File id.
	 * @param int $entityId Entity id.
	 * @param int $entityType Entity type.
	 * @return void
	 */
	protected static function delete($fileId, $entityId, $entityType)
	{
		$filter = array(
			'ENTITY_ID' => $entityId,
			'=ENTITY_TYPE' => $entityType
		);
		if ($fileId)
		{
			$filter['FILE_ID'] = $fileId;
		}
		$res = FileTable::getList(array(
			'select' => array(
				'ID', 'FILE_ID'
			),
			'filter' => $filter
		));
		while ($row = $res->fetch())
		{
			$resUpdate = FileTable::update(
				$row['ID'],
				array(
					'FILE_ID' => -1 * abs($row['FILE_ID'])
				)
			);
			$resUpdate->isSuccess();
		}
	}

	/**
	 * Final delete all marked files.
	 * @param int $limit Records limit for one iteration.
	 * @return void
	 */
	public static function deleteFinal($limit = null)
	{
		$deletedFiles = array();

		$res = FileTable::getList(array(
		  	'select' => array(
		 		'ID', 'FILE_ID'
		  	),
	  		'filter' => array(
				'<FILE_ID' => 0
			),
			'limit' => $limit,
			'order' => array(
				'ID' => 'asc'
			)
		));
		while ($row = $res->fetch())
		{
			$row['FILE_ID'] *= -1;
			FileTable::delete($row['ID']);
			$deletedFiles[$row['FILE_ID']] = $row['FILE_ID'];
		}
		if (!empty($deletedFiles))
		{
			// don't delete still used
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'FILE_ID' => $deletedFiles
				)
			));
			while ($row = $res->fetch())
			{
				unset($deletedFiles[$row['FILE_ID']]);
			}
			foreach ($deletedFiles as $fid)
			{
				$fileData = self::getFileArray($fid);
				if ($fileData)
				{
					//@tmp log
					Debug::log(
						$fileData['SRC'],
						'fileId: ' . $fid,
						'LANDING_FILE_DELETE'
					);
					\CFile::delete($fid);
				}
			}
		}
	}

	/**
	 * Add new record for Site.
	 * @param int $id Site id.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function addToSite($id, $fileId)
	{
		if ($fileId > 0 && $id > 0)
		{
			self::add($fileId, $id, self::ENTITY_TYPE_SITE);
		}
	}

	/**
	 * Gets files id from site.
	 * @param int $siteId Site id.
	 * @return array
	 */
	public static function getFilesFromSite($siteId)
	{
		return self::getFiles(
			$siteId,
			self::ENTITY_TYPE_SITE
		);
	}

	/**
	 * Delete record from Site.
	 * @param int $id Site id.
	 * @param int|array $fileId File id (by default delete all files from landing).
	 * @return void
	 */
	public static function deleteFromSite($id, $fileId = array())
	{
		self::delete($fileId, $id, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Add new record for Landing.
	 * @param int $lid Landing id.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function addToLanding($lid, $fileId)
	{
		if ($fileId > 0 && $lid > 0)
		{
			self::add($fileId, $lid, self::ENTITY_TYPE_LANDING);
		}
	}

	/**
	 * Gets files id from landing.
	 * @param int $landingId Landing id.
	 * @return array
	 */
	public static function getFilesFromLanding($landingId)
	{
		return self::getFiles(
			$landingId,
			self::ENTITY_TYPE_LANDING
		);
	}

	/**
	 * Delete record from Landing.
	 * @param int $lid Landing id.
	 * @param int|array $fileId File id (by default delete all files from landing).
	 * @return void
	 */
	public static function deleteFromLanding($lid, $fileId = array())
	{
		self::delete($fileId, $lid, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Add new record(s) for Block.
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (or file ids).
	 * @return void
	 */
	public static function addToBlock($blockId, $fileId)
	{
		if ($blockId > 0)
		{
			if (!is_array($fileId))
			{
				$fileId = array($fileId);
			}
			foreach ($fileId as $fid)
			{
				if ($fid > 0)
				{
					self::add($fid, $blockId, self::ENTITY_TYPE_BLOCK);
				}
			}
		}
	}

	/**
	 * Add new record(s) for Block (old records will be deleted).
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (or file ids).
	 * @return void
	 */
	public static function replaceInBlock($blockId, $fileId)
	{
		if ($blockId > 0)
		{
			if (!is_array($fileId))
			{
				$fileId = array($fileId);
			}
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'ENTITY_ID' => $blockId,
					'=ENTITY_TYPE' => self::ENTITY_TYPE_BLOCK
				)
			));
			while ($row = $res->fetch())
			{
				if (!in_array($row['FILE_ID'], $fileId))
				{
					self::delete($row['FILE_ID'], $blockId, self::ENTITY_TYPE_BLOCK);
				}
			}
			self::addToBlock($blockId, $fileId);
		}
	}

	/**
	 * Delete record from Block.
	 * @param int $blockId Block id.
	 * @param int|array $fileId File id (by default delete all files from block).
	 * @return void
	 */
	public static function deleteFromBlock($blockId, $fileId = array())
	{
		self::delete($fileId, $blockId, self::ENTITY_TYPE_BLOCK);
	}

	/**
	 * Parse some content for search data-fileid for the block.
	 * @param int $blockId Block id for content.
	 * @param string $content Content.
	 * @return array
	 */
	public static function getFilesFromBlockContent($blockId, $content)
	{
		$fileIds = array();
		// parse from content
		if (preg_match_all('/data-fileid[2x]{0,2}="([\d]+)"/i', $content, $matches))
		{
			foreach ($matches[1] as $fid)
			{
				$fileIds[] = $fid;
			}
		}
		// check if files ids set in blockId
		if (!empty($fileIds))
		{
			$res = FileTable::getList(array(
				'select' => array(
					'FILE_ID'
				),
				'filter' => array(
					'FILE_ID' => $fileIds,
					'ENTITY_ID' => $blockId,
					'=ENTITY_TYPE' => self::ENTITY_TYPE_BLOCK
				)
			));
			$fileIds = array();
			while ($row = $res->fetch())
			{
				$fileIds[] = $row['FILE_ID'];
			}
		}
		return $fileIds;
	}

	/**
	 * Gets files id from block.
	 * @param int $blockId Block id.
	 * @return array
	 */
	public static function getFilesFromBlock($blockId)
	{
		return self::getFiles(
			$blockId,
			self::ENTITY_TYPE_BLOCK
		);
	}
	
	/**
	 * Add new record for Asset.
	 * @param int $assetId Id of landing to which attached asset.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function addToAsset($assetId, $fileId)
	{
		if ($fileId > 0 && $assetId > 0)
		{
			self::add($fileId, $assetId, self::ENTITY_TYPE_ASSET);
			self::markAssetRebuilded($assetId);
			// todo: res from add and check error
		}
	}
	
	/**
	 * Gets asset files for current landing.
	 * @param int $assetId Id of landing to which attached asset.
	 * @return array
	 */
	public static function getFilesFromAsset($assetId)
	{
		return self::getFiles(
			$assetId,
			self::ENTITY_TYPE_ASSET
		);
	}
	
	/**
	 * Delete asset files for current landing.
	 * Not remove from disk immediately, just marked for agent
	 * @param int $assetId Id of landing to which attached asset.
	 * @param int|int[] $fileId File id (by default delete all files from Asset).
	 * @return void
	 */
	public static function deleteFromAsset($assetId, $fileId = [])
	{
		self::delete($fileId, $assetId, self::ENTITY_TYPE_ASSET);
	}
	
	/**
	 * Mark file as "need rebuild", but not delete them. File will be exist until not created new file.
	 * @param int|int[] $assetId Id of landing to which attached asset. If not set - will marked all.
	 * @return void
	 */
	public static function markAssetToRebuild($assetId = [])
	{
		$filter = [
			'=ENTITY_TYPE' => self::ENTITY_TYPE_ASSET
		];
		if ($assetId)
		{
			$filter['ENTITY_ID'] = $assetId;
		}
		$res = FileTable::getList([
			'select' => ['ID', 'ENTITY_ID'],
			'filter' => $filter
		]);
		while ($row = $res->fetch())
		{
			$resUpdate = FileTable::update(
				$row['ID'],
				[
					'ENTITY_ID' => -1 * abs($row['ENTITY_ID'])
				]
			);
			$resUpdate->isSuccess();
		}
	}
	
	/**
	 * When file rebuilded - delete old files (marked as "need rebuild") for current asset ID (current landing)
	 * @param int|int[] $assetId Id of landing to which attached asset.
	 * @return void
	 */
	public static function markAssetRebuilded($assetId)
	{
		if(!is_array($assetId))
		{
			$assetId = [$assetId];
		}
		
		foreach ($assetId as $key => $id)
		{
			self::deleteFromAsset(-1 * abs($id));
		}
	}

	/**
	 * Copy files from one entity to another.
	 * @param int $from Entity id.
	 * @param int $to Entity id.
	 * @param string $entityType Entity type.
	 * @return void
	 */
	protected static function copyEntityFiles($from, $to, $entityType)
	{
		$res = FileTable::getList(array(
			'select' => array(
				'FILE_ID'
			),
			'filter' => array(
				'ENTITY_ID' => $from,
				'=ENTITY_TYPE' => $entityType
			)
		));
		while ($row = $res->fetch())
		{
			FileTable::add(array(
				'FILE_ID' => $row['FILE_ID'],
				'ENTITY_ID' => $to,
				'ENTITY_TYPE' => $entityType
			));
		}
	}

	/**
	 * Copy files from one site to another.
	 * @param int $from Site id.
	 * @param int $to Site id.
	 * @return void
	 */
	public static function copySiteFiles($from, $to)
	{
		self::copyEntityFiles($from, $to, self::ENTITY_TYPE_SITE);
	}

	/**
	 * Copy files from one landing to another.
	 * @param int $from Landing id.
	 * @param int $to Landing id.
	 * @return void
	 */
	public static function copyLandingFiles($from, $to)
	{
		self::copyEntityFiles($from, $to, self::ENTITY_TYPE_LANDING);
	}

	/**
	 * Copy files from one block to another.
	 * @param int $from Block id.
	 * @param int $to Block id.
	 * @return void
	 */
	public static function copyBlockFiles($from, $to)
	{
		self::copyEntityFiles($from, $to, self::ENTITY_TYPE_BLOCK);
	}

	/**
	 * Gets core file array.
	 * @param int $fileId File id.
	 * @return mixed
	 */
	public static function getFileArray($fileId)
	{
		$file = \CFile::getFileArray(
			$fileId
		);
		if (
			isset($file['MODULE_ID']) &&
			$file['MODULE_ID'] == 'landing'
		)
		{
			return $file;
		}
		return false;
	}

	/**
	 * Gets core file path.
	 * @param int $fileId File id.
	 * @return string
	 */
	public static function getFilePath($fileId)
	{
		$file = self::getFileArray($fileId);
		if (isset($file['SRC']))
		{
			return $file['SRC'];
		}
		return null;
	}

	/**
	 * Delete all file Id from File table.
	 * @param int $fileId File id to delete.
	 * @return void
	 */
	public static function releaseFile(int $fileId): void
	{
		$res = FileTable::getList(array(
			'select' => [
				'ID'
			],
			'filter' => [
				'FILE_ID' => $fileId
			]
		));
		while ($row = $res->fetch())
		{
			FileTable::delete($row['ID']);
		}
	}

	/**
	 * Physical delete file.
	 * @param int $fileId File id.
	 * @return void
	 */
	public static function deletePhysical(int $fileId): void
	{
		if (self::getFileArray($fileId))
		{
			self::releaseFile($fileId);
			\CFile::delete($fileId);
		}
	}
}