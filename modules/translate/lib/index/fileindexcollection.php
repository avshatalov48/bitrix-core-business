<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * @see \Bitrix\Main\ORM\Objectify\Collection
 */
class FileIndexCollection
	extends Index\Internals\EO_FileIndex_Collection
{
	/** @var bool */
	static $verbose = false;

	/** @var string */
	private static $documentRoot;

	/** @var string[] */
	private static $enabledLanguages;

	/** @var string[] */
	private $checkLanguages = array();


	/**
	 * Sets up configuration.
	 *
	 * @return void
	 */
	private static function configure()
	{
		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$enabledLanguages = Translate\Config::getEnabledLanguages();
	}


	/**
	 * Counts items to process.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return int
	 */
	public function countItemsToProcess(Translate\Filter $filter = null)
	{
		if (isset($filter, $filter->path))
		{
			$relPath = '/'. \trim($filter->path, '/');
			$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');

			$topPathRes = Index\Internals\PathIndexTable::getList([
				'select' => ['ID'],
				'filter' => ['=PATH' => $relPath]
			]);
			if (!($topPath = $topPathRes->fetch()))
			{
				return 0;
			}

			$pathFilter = array(
				'=IS_DIR' => 'N',
				'=IS_LANG' => 'Y',
				'=%PATH' => $relPath.'%#LANG_ID#%',
				'=DESCENDANTS.PARENT_ID' => $topPath['ID'],//ancestor
				//todo: add filter by INDEXED_TIME
			);
			$totalItems = (int)Index\Internals\PathIndexTable::getCount($pathFilter);
		}
		else
		{
			$totalItems = (int)Index\Internals\PathIndexTable::getCount();
		}

		return $totalItems;
	}


	/**
	 * Collect index file.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param Translate\Controller\ITimeLimit $timer Time counter.
	 * @param Translate\Filter $seek Params to seek position.
	 *
	 * @return int
	 */
	public function collect(Translate\Filter $filter = null, Translate\Controller\ITimeLimit $timer = null, Translate\Filter $seek = null)
	{
		self::configure();

		if (isset($filter, $filter->path))
		{
			$relPath = $filter->path;
		}
		else
		{
			$relPath = Translate\Config::getDefaultPath();
		}

		$relPath = '/'. \trim($relPath, '/');
		$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');

		$this->checkLanguages = self::$enabledLanguages;
		if (isset($filter, $filter->langId))
		{
			$this->checkLanguages = \array_intersect($filter->langId, $this->checkLanguages);
		}


		$topPathRes = Index\Internals\PathIndexTable::getList([
			'select' => ['ID'],
			'filter' => ['=PATH' => $relPath]
		]);
		if (!($topPath = $topPathRes->fetch()))
		{
			return 0;
		}

		$pathFilter = array(
			'=IS_DIR' => 'N',
			'=IS_LANG' => 'Y',
			'=%PATH' => $relPath.'%#LANG_ID#%',
			'=DESCENDANTS.PARENT_ID' => $topPath['ID'],//ancestor
			//todo: add filter by INDEXED_TIME
		);
		if (isset($seek, $seek->pathId))
		{
			$pathFilter['>ID'] = $seek->pathId;
		}

		// path list
		$pathListRes = Index\Internals\PathIndexTable::getList([
			'select' => ['ID', 'PATH'],
			'filter' => $pathFilter,
			'order' => ['ID' => 'ASC'],
			//todo: add limit here
		]);

		$processedItemCount = 0;

		while (true)
		{
			$lastPathId = null;
			$pathPortion = array();
			$pathIdPortion = array();
			while ($pathRow = $pathListRes->fetch())
			{
				$pathIdPortion[] = $lastPathId = (int)$pathRow['ID'];
				$pathPortion[$lastPathId] = $pathRow['PATH'];
				if (\count($pathIdPortion) >= 100)
				{
					break;
				}
			}
			if (empty($pathIdPortion))
			{
				break;
			}

			$indexFileCacheRes = Index\Internals\FileIndexTable::getList([
				'select' => ['ID', 'PATH_ID', 'LANG_ID'],
				'filter' => [
					'=PATH_ID' => $pathIdPortion,
					'=LANG_ID' => $this->checkLanguages,
				]
			]);
			$indexFileCache = array();
			while ($indexFile = $indexFileCacheRes->fetch())
			{
				if (!isset($indexFileCache[(int)$indexFile['PATH_ID']]))
				{
					$indexFileCache[(int)$indexFile['PATH_ID']] = array();
				}
				$indexFileCache[(int)$indexFile['PATH_ID']][$indexFile['LANG_ID']] = (int)$indexFile['ID'];
			}
			unset($indexFileCacheRes, $indexFile);

			$nonexistentFiles = array();
			$fileData = array();

			foreach ($pathPortion as $pathId => $path)
			{
				foreach ($this->checkLanguages as $langId)
				{
					$fullPath = self::$documentRoot. \str_replace('#LANG_ID#', $langId, $path);
					$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);

					if (self::$verbose)
					{
						echo "Lang file: {$fullPath}\n";
					}

					if (!\file_exists($fullPath))
					{
						if (isset($indexFileCache[$pathId][$langId]))
						{
							// remove file from index
							$nonexistentFiles[] = $indexFileCache[$pathId][$langId];
						}
						continue;
					}

					if (!isset($indexFileCache[$pathId][$langId]))
					{
						$fileData[] = array(
							'PATH_ID' => $pathId,
							'LANG_ID' => $langId,
							'FULL_PATH' => $fullPath,
						);
					}
				}
			}
			if (\count($fileData) > 0)
			{
				Index\Internals\FileIndexTable::bulkAdd($fileData);
			}

			if (\count($nonexistentFiles) > 0)
			{
				Index\Internals\FileIndexTable::purge(new Translate\Filter(['fileId' => $nonexistentFiles]), true);
			}

			$processedItemCount += \count($pathIdPortion);

			if ($timer !== null && $timer->hasTimeLimitReached())
			{
				if ($seek !== null)
				{
					$seek->nextPathId = $lastPathId;
				}
				break;
			}
		}

		return $processedItemCount;
	}


	/**
	 * Drop index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param bool $recursively Drop index recursively.
	 *
	 * @return self
	 */
	public function purge(Translate\Filter $filter = null, $recursively = true)
	{
		Index\Internals\FileIndexTable::purge($filter, $recursively);

		return $this;
	}

	/**
	 * Unvalidate index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function unvalidate(Translate\Filter $filter = null)
	{
		if (($filterOut = Index\Internals\FileIndexTable::processFilter($filter)) !== false)
		{
			Index\Internals\FileIndexTable::bulkUpdate(['INDEXED' => 'N'], $filterOut);
		}

		return $this;
	}
}

