<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Main\Localization;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * @see \Bitrix\Main\ORM\Objectify\Collection
 */
class PathLangCollection
	extends Index\Internals\EO_PathLang_Collection
{
	/**
	 * @var bool
	 */
	static $verbose = false;

	/** @var string */
	private static $documentRoot;

	/** @var string[] */
	private static $enabledLanguages;

	/** @var bool */
	private static $useTranslationRepository;
	/** @var string[] */
	private static $translationRepositoryLanguages;
	/** @var string[] */
	private static $translationEnabledLanguages;
	/** @var string */
	private static $translationRepositoryRoot;

	/**
	 * Sets up configuration.
	 *
	 * @return void
	 */
	private static function configure()
	{
		self::$documentRoot = \rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		self::$enabledLanguages = Translate\Config::getEnabledLanguages();

		self::$useTranslationRepository = Localization\Translation::useTranslationRepository();
		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryLanguages = Translate\Config::getTranslationRepositoryLanguages();
			self::$translationRepositoryRoot = \rtrim(Localization\Translation::getTranslationRepositoryPath(), '/');

			// only active languages
			self::$translationEnabledLanguages = \array_intersect(self::$translationRepositoryLanguages, self::$enabledLanguages);
		}
	}

	/**
	 * Counts items to process.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return int
	 */
	public function countItemsToProcess(?Translate\Filter $filter = null): int
	{
		$relPath = isset($filter, $filter->path) ? $filter->path : '';

		if (!empty($relPath))
		{
			$relPath = '/'. \trim($relPath, '/');
			$totalItems = (int)Index\Internals\PathLangTable::getCount(['=%PATH' => $relPath .'%']);
		}
		else
		{
			$totalItems = (int)Index\Internals\PathLangTable::getCount();
		}

		return $totalItems;
	}

	/**
	 * Collects lang folder paths.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 * @param Translate\Controller\ITimeLimit|null $timer Time counter.
	 * @param Translate\Filter|null $seek Params to seek position.
	 *
	 * @return int
	 */
	public function collect(?Translate\Filter $filter = null, ?Translate\Controller\ITimeLimit $timer = null, ?Translate\Filter $seek = null): int
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

		// If it is lang folder, do nothing
		if (\basename($relPath) == 'lang')
		{
			Index\Internals\PathLangTable::add(['PATH' => $relPath]);

			return 1;
		}

		$seekAncestors = [];
		if (isset($seek, $seek->path))
		{
			$seekAncestors = \explode('/', \trim($seek->path, '/'));
			$seek->lookForSeek = true;
		}

		$checkLanguages = [];
		if (self::$useTranslationRepository)
		{
			$checkLanguages = self::$translationEnabledLanguages;
			if (isset($filter, $filter->langId))
			{
				$checkLanguages = \array_intersect($filter->langId, $checkLanguages);
			}
		}

		$pathDepthLevel = \count(\explode('/', \trim($relPath, '/'))) - 1;

		$cache = [];
		$processedItemCount = 0;

		/**
		 * @param string $relPath
		 * @param bool $isTop
		 *
		 * @return \Generator|string
		 */
		$lookForLangDirectory = function ($startRoot, $relPath, $depthLevel, $isTop = false)
			use (&$lookForLangDirectory, &$cache, $checkLanguages, &$seek, $seekAncestors, &$processedItemCount)
		{
			$childrenList = [];

			$mergeChildrenList = function($childrenList0, $langId = '') use (&$childrenList)
			{
				foreach ($childrenList0 as $childPath)
				{
					$name = \basename($childPath);
					if (\in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}
					if (isset($childrenList[$name]))
					{
						continue;
					}

					if (self::$useTranslationRepository && $langId != '')
					{
						$childPath = \str_replace(
							self::$translationRepositoryRoot. '/'. $langId,
							self::$documentRoot. '/bitrix/modules',
							$childPath
						);
					}

					$childrenList[$name] = $childPath;
				}
			};

			$mergeChildrenList(Translate\IO\FileSystemHelper::getFolderList($startRoot. $relPath));

			if (self::$useTranslationRepository)
			{
				foreach ($checkLanguages as $langId)
				{
					$path1 = Localization\Translation::convertLangPath($startRoot. $relPath, $langId);
					if ($path1 != $startRoot)
					{
						$mergeChildrenList(Translate\IO\FileSystemHelper::getFolderList($path1), $langId);
					}
				}
			}

			if (!empty($childrenList))
			{
				$ignoreDev = \implode('|', Translate\IGNORE_MODULE_NAMES);
				foreach ($childrenList as $childPath)
				{
					$name = \basename($childPath);
					$relChildPath = \str_replace($startRoot, '', $childPath);

					if (\in_array($name, Translate\IGNORE_FS_NAMES))
					{
						continue;
					}
					if (\in_array($relChildPath, Translate\IGNORE_BX_NAMES))
					{
						continue;
					}
					// /bitrix/modules/[smth]/dev/
					if (\preg_match("#/bitrix/modules/[^/]+/({$ignoreDev})$#", $relChildPath))
					{
						continue;
					}

					$isLang = ($name == 'lang');

					if ($seek !== null && $seek->lookForSeek === true)
					{
						if ($seekAncestors[$depthLevel + 1] == $name)
						{
							if ($relChildPath == $seek->path)
							{
								if (self::$verbose)
								{
									echo "Seek folder: {$relChildPath}\n";
								}
								$seek->lookForSeek = false;// found
								continue;
							}

							if (!$isLang)
							{
								foreach ($lookForLangDirectory($startRoot, $relChildPath, $depthLevel + 1) as $subChildPath)// go deeper
								{
								}
							}
						}

						continue;
					}

					if ($isLang)
					{
						$cache[] = [
							'PATH' => $relChildPath,
						];

						if (\count($cache) >= 50)
						{
							Index\Internals\PathLangTable::bulkAdd($cache);
							$processedItemCount += \count($cache);
							$cache = [];
						}
					}
					else
					{
						foreach ($lookForLangDirectory($startRoot, $relChildPath, $depthLevel + 1) as $subChildPath)// go deeper
						{
							yield $subChildPath;
						}
					}

					if ($isLang)
					{
						yield $relChildPath;
					}
				}
			}

			if ($isTop && \count($cache) > 0)
			{
				Index\Internals\PathLangTable::bulkAdd($cache);
				$processedItemCount += \count($cache);
				$cache = [];
			}
		};

		foreach ($lookForLangDirectory(self::$documentRoot, $relPath, $pathDepthLevel, true) as $langPath)
		{
			if (self::$verbose)
			{
				if (!$langPath instanceof \Generator)
				{
					echo "Lang folder: {$langPath}\n";
				}
			}
			if ($timer !== null)
			{
				if ($timer->hasTimeLimitReached())
				{
					$seek->nextPath = $langPath;
					break;
				}
			}
			// check user abortion
			if (\connection_status() !== \CONNECTION_NORMAL)
			{
				throw new Main\SystemException('Process has been broken course user aborted connection.');
			}
		}

		if (\count($cache) > 0)
		{
			Index\Internals\PathLangTable::bulkAdd($cache);
			$processedItemCount += \count($cache);
		}

		return $processedItemCount;
	}

	/**
	 * Drops index.
	 *
	 * @param Translate\Filter|null $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function purge(?Translate\Filter $filter = null): self
	{
		Index\Internals\PathLangTable::purge($filter);

		return $this;
	}
}
