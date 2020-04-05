<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * @see \Bitrix\Main\ORM\Objectify\Collection
 */
class PhraseIndexCollection
	extends Index\Internals\EO_PhraseIndex_Collection
{
	/**
	 * @var bool
	 */
	static $verbose = false;

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
			$relPath = '/'. trim($filter->path, '/');
			$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');

			$topPathRes = Index\Internals\PathIndexTable::getList([
				'select' => ['ID'],
				'filter' => ['=PATH' => $relPath]
			]);
			if (!($topPath = $topPathRes->fetch()))
			{
				return 0;
			}

			$checkLanguages = Translate\Config::getEnabledLanguages();
			if (isset($filter, $filter->langId))
			{
				$checkLanguages = array_intersect($filter->langId, $checkLanguages);
			}

			$fileFilter = array(
				'=PATH.DESCENDANTS.PARENT_ID' => $topPath['ID'],//ancestor
				'=LANG_ID' => $checkLanguages,
				//todo: add filter by INDEXED_TIME
			);
			$totalItems = (int)Index\Internals\FileIndexTable::getCount($fileFilter);
		}
		else
		{
			$totalItems = (int)Index\Internals\FileIndexTable::getCount();
		}

		return $totalItems;
	}


	/**
	 * Collect index phrases.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param Translate\Controller\ITimeLimit $timer Time counter.
	 * @param Translate\Filter $seek Params to seek position.
	 *
	 * @return int
	 */
	public function collect(Translate\Filter $filter = null, Translate\Controller\ITimeLimit $timer = null, Translate\Filter $seek = null)
	{
		if (isset($filter, $filter->path))
		{
			$relPath = $filter->path;
		}
		else
		{
			$relPath = Translate\Config::getDefaultPath();
		}

		$relPath = '/'. trim($relPath, '/');
		$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');

		$checkLanguages = Translate\Config::getEnabledLanguages();
		if (isset($filter, $filter->langId))
		{
			$checkLanguages = array_intersect($filter->langId, $checkLanguages);
		}

		$topPathRes = Index\Internals\PathIndexTable::getList([
			'select' => ['ID'],
			'filter' => ['=PATH' => $relPath]
		]);
		if (!($topPath = $topPathRes->fetch()))
		{
			return 0;
		}

		$fileFilter = array(
			'=PATH.DESCENDANTS.PARENT_ID' => $topPath['ID'],//ancestor
			'=LANG_ID' => $checkLanguages,
			//todo: add filter by INDEXED_TIME
		);
		if (isset($seek, $seek->pathId))
		{
			$fileFilter['>PATH_ID'] = $seek->pathId;
		}

		Main\Application::getConnection()->queryExecute("SET SESSION group_concat_max_len = 100000");

		$fileListQuery = Index\Internals\FileIndexTable::query();

		$fileListQuery
			->addSelect('PATH_ID')

			->registerRuntimeField(new Main\ORM\Fields\ExpressionField('FILE_IDS', "GROUP_CONCAT(%s ORDER BY (%s) SEPARATOR '\\n')", ['ID', 'ID']))
			->addSelect('FILE_IDS')

			->registerRuntimeField(new Main\ORM\Fields\ExpressionField('LANG_IDS', "GROUP_CONCAT(%s ORDER BY (%s) SEPARATOR '\\n')", ['LANG_ID', 'ID']))
			->addSelect('LANG_IDS')

			->registerRuntimeField(new Main\ORM\Fields\ExpressionField('FULL_PATHS', "GROUP_CONCAT(%s ORDER BY (%s) SEPARATOR '\\n')", ['FULL_PATH', 'ID']))
			->addSelect('FULL_PATHS')

			->setFilter($fileFilter)
			->setOrder(['PATH_ID' => 'ASC'])
			->addGroup('PATH_ID')
		;

		$fileListRes = $fileListQuery->exec();

		$processedItemCount = 0;

		while (true)
		{
			$lastPathId = null;
			$filePortion = array();
			while ($pathRow = $fileListRes->fetch())
			{
				$filePortion[] = $pathRow;
				if (count($filePortion) >= 5)
				{
					break;
				}
			}
			if (empty($filePortion))
			{
				break;
			}

			$fileData = array();
			$phraseData = array();
			$pathIdPortion = array();
			$nonexistentFiles = array();

			foreach ($filePortion as $indexFile)
			{
				$pathId = (int)$indexFile['PATH_ID'];
				$pathIdPortion[] = $lastPathId = $pathId;

				$fileIds = [];
				foreach (explode("\n", $indexFile['FILE_IDS']) as $v)
				{
					$fileIds[] = (int)$v;
				}

				$langIds = [];
				foreach (explode("\n", $indexFile['LANG_IDS']) as $v)
				{
					$langIds[] = trim($v);
				}

				$filePaths = [];
				foreach (explode("\n", $indexFile['FULL_PATHS']) as $v)
				{
					$filePaths[] = trim($v);
				}


				foreach ($fileIds as $inx => $indexFileId)
				{
					$langId = $langIds[$inx];
					$fullPath = $filePaths[$inx];

					if (self::$verbose)
					{
						echo "Lang file: {$fullPath}\n";
					}

					$current = new Translate\File($fullPath);
					$current->setLangId($langId);
					if (!$current->load())
					{
						$nonexistentFiles[] = $indexFileId;
						continue;
					}

					$fileData[] = array(
						'ID' => $indexFileId,
						'PATH_ID' => $pathId,
						'LANG_ID' => $langId,
						'PHRASE_COUNT' => $current->count(),
						'FULL_PATH' => $current->getPath(),
						'INDEXED' => 'Y',
						'INDEXED_TIME' => new Main\Type\DateTime(),
					);

					foreach ($current as $code => $phrase)
					{
						$phraseData[] = array(
							'FILE_ID' => $indexFileId,
							'PATH_ID' => $pathId,
							'LANG_ID' => $langId,
							'CODE' => $code,
							'PHRASE' => $phrase,
						);
					}
				}

				$processedItemCount += count($fileIds);
			}

			Index\Internals\PhraseIndexTable::bulkDelete(['=PATH_ID' => $pathIdPortion, '=LANG_ID' => $checkLanguages]);

			if (count($nonexistentFiles) > 0)
			{
				Index\Internals\FileDiffTable::bulkDelete(['=FILE_ID' => $nonexistentFiles]);
				Index\Internals\PhraseIndexTable::bulkDelete(['=FILE_ID' => $nonexistentFiles]);
				Index\Internals\FileIndexTable::bulkDelete(['=ID' => $nonexistentFiles]);
			}
			if (count($phraseData) > 0)
			{
				Index\Internals\FileIndexTable::bulkAdd($fileData, 'ID');
				Index\Internals\PhraseIndexTable::bulkAdd($phraseData);
			}

			Index\Internals\PathIndexTable::bulkUpdate(
				['INDEXED' => 'Y', 'INDEXED_TIME' => new Main\Type\DateTime()],
				['=ID' => $pathIdPortion]
			);

			if ($timer !== null && $timer->hasTimeLimitReached())
			{
				if ($seek !== null)
				{
					$seek->nextPathId = $lastPathId;
				}
				break;
			}
		}


		if ($timer === null || !$timer->hasTimeLimitReached())
		{
			Index\Internals\PathIndexTable::bulkUpdate(
				['INDEXED' => 'Y', 'INDEXED_TIME' => new Main\Type\DateTime()],
				[
					'=IS_DIR' => 'Y',
				 	'=DESCENDANTS.PARENT_ID' => $topPath['ID'],//ancestor
				]
			);
		}

		return $processedItemCount;
	}


	/**
	 * Drop index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function purge(Translate\Filter $filter = null)
	{
		Index\Internals\PhraseIndexTable::purge($filter);

		return $this;
	}

	/**
	 * Returns phrase object by its code.
	 *
	 * @param string $code Phrase code to search.
	 *
	 * @return Index\PhraseIndex|null
	 */
	public function getPhraseByCode($code)
	{
		foreach ($this as $phrase)
		{
			if ($phrase->getCode() === $code)
			{
				return $phrase;
			}
		}

		return null;
	}
}

