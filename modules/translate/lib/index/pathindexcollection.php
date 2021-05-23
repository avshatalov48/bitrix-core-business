<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;

/**
 * @see \Bitrix\Main\ORM\Objectify\Collection
 */
class PathIndexCollection
	extends Index\Internals\EO_PathIndex_Collection
{
	/** @var bool */
	static $verbose = false;

	/** @var string */
	private static $documentRoot;

	/** @var string[] */
	private static $enabledLanguages;
	/** @var string[] */
	private static $availableLanguages;

	/** @var bool */
	private static $useTranslationRepository;
	/** @var string[] */
	private static $translationRepositoryLanguages;
	/** @var string[] */
	private static $translationEnabledLanguages;
	/** @var string */
	private static $translationRepositoryRoot;

	/** @var array */
	private $immediateChildren = array();

	/** @var array */
	private $ancestorsPaths = array();

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
		self::$availableLanguages = Translate\Config::getAvailableLanguages();

		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryLanguages = Translate\Config::getTranslationRepositoryLanguages();
			self::$translationRepositoryRoot = rtrim(Main\Localization\Translation::getTranslationRepositoryPath(), '/');

			// only active languages
			self::$translationEnabledLanguages = array_intersect(self::$translationRepositoryLanguages, self::$enabledLanguages);
		}
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
			$relPath = '/'. trim($filter->path, '/');
			$totalItems = (int)Index\Internals\PathLangTable::getCount(array('=%PATH' => $relPath .'%'));
		}
		else
		{
			$totalItems = (int)Index\Internals\PathLangTable::getCount();
		}

		return $totalItems;
	}

	/**
	 * Collect path structure.
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
		$relPath = '/'. trim($relPath, '/');

		if (self::$useTranslationRepository)
		{
			$this->checkLanguages = self::$translationEnabledLanguages;
			if (isset($filter, $filter->langId))
			{
				$this->checkLanguages = array_intersect($filter->langId, $this->checkLanguages);
			}
		}

		$pathFilter = array(
			'=%PATH' => $relPath.'%'
		);
		if (isset($seek, $seek->pathLangId))
		{
			$pathFilter['>ID'] = $seek->pathLangId;
		}

		$cachePathLangRes = Index\Internals\PathLangTable::getList(array(
			'filter' => $pathFilter,
			'order' => array('ID' => 'ASC'),
			'select' => ['ID', 'PATH'],
		));
		$processedItemCount = 0;
		while ($pathLang = $cachePathLangRes->fetch())
		{
			$this->collectFilePath($pathLang['PATH']);

			$processedItemCount ++;

			if ($timer !== null && $timer->hasTimeLimitReached())
			{
				if ($seek !== null)
				{
					$seek->nextLangPathId = (int)$pathLang['ID'];
				}
				break;
			}
		}

		return $processedItemCount;
	}



	/**
	 * Collect path structure.
	 *
	 * @param string $relPath Path to lang folder to index.
	 *
	 * @return int
	 */
	private function collectFilePath($relPath)
	{
		$fullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$relPath);

		$topPath = $this->constructAncestorsByPath($relPath);

		$topPathId = (int)$topPath['ID'];
		$topDepthLevel = (int)$topPath['DEPTH_LEVEL'];
		$isTopLang = $topPath['IS_LANG'];

		$topLangId = null;
		if ($isTopLang)
		{
			$topLangId = Translate\IO\Path::extractLangId($relPath);
		}

		$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');

		if ($isTopLang)
		{
			if ($langSettings = Translate\Settings::instantiateByPath(self::$documentRoot.'/'.$relPath))
			{
				if (!$langSettings->isExists() || !$langSettings->load())
				{
					unset($langSettings);
				}
			}
		}

		/**
		 * @param string $parentFullPath Full real path of the parent folder.
		 * @param string $parentRelPath Relative project path of the parent folder.
		 * @param int $parent The Id of of the parent folder index record.
		 * @param bool $isParentLang The flag that is parent folder is language folder.
		 * @param string $parentLangId The lang Id of the parent folder.
		 * @param int $depthLevel Current depth level.
		 *
		 * @return \Generator|int
		 */
		$lookForLangDirectory =
			function (
				$parentFullPath,
				$parentRelPath,
				$parentId,
				$isParentLang = false,
				$parentLangId = null,
				$depthLevel = 0
			)
			use (
				&$lookForLangDirectory,
				/** @var Translate\Settings */
				&$langSettings
			)
			{
				$processedItemCount = 0;

				$this->getImmediateChildren($parentId);

				if ($isParentLang)
				{
					$childrenList = Translate\IO\FileSystemHelper::getFileList($parentFullPath);
					if (!empty($childrenList))
					{
						foreach ($childrenList as $fullPath)
						{
							$name = basename($fullPath);
							if (in_array($name, Translate\IGNORE_FS_NAMES))
							{
								continue;
							}
							if (mb_substr($name, -4) !== '.php')
							{
								continue;
							}
							if (!is_file($fullPath))
							{
								continue;
							}

							$relPath = Translate\IO\Path::replaceLangId($parentRelPath . '/'. $name, '#LANG_ID#');

							$pathId = null;

							if (isset($this->immediateChildren[$parentId][$relPath]))
							{
								$pathId = $this->immediateChildren[$parentId][$relPath];
							}

							if (self::$verbose)
							{
								echo "File path: {$relPath}";
							}
							if ($pathId === null)
							{
								$nodeData = array(
									'PARENT_ID' => $parentId,
									'NAME' => $name,
									'PATH' => $relPath,
									'IS_LANG' => 'Y',
									'IS_DIR' => 'N',
									'DEPTH_LEVEL' => $depthLevel,
								);

								if ($langSettings instanceof Translate\Settings)
								{
									$settings = $langSettings->getOptions($relPath);
									if (!empty($settings[Translate\Settings::OPTION_LANGUAGES]))
									{
										$nodeData['OBLIGATORY_LANGS'] = implode(',', $settings[Translate\Settings::OPTION_LANGUAGES]);
									}
								}

								$res = Index\Internals\PathIndexTable::add($nodeData);
								$pathId = $res->getId();

								$this->immediateChildren[$parentId][$relPath] = $pathId;
							}

							if (self::$verbose)
							{
								echo "\tIndex id: {$pathId}\n";
							}

							//yield $pathId;
							$processedItemCount ++;
						}
					}
				}

				// dir only
				$childrenList = Translate\IO\FileSystemHelper::getFolderList($parentFullPath);
				if (empty($childrenList))
				{
					$childrenList = array();
				}

				if ($parentLangId === null && basename($parentFullPath) === 'lang')
				{
					foreach ($childrenList as $i => $fullPath)
					{
						$name = basename($fullPath);
						if (in_array($name, Translate\IGNORE_FS_NAMES))
						{
							unset($childrenList[$i]);
						}
						if (!in_array($name, self::$enabledLanguages))
						{
							unset($childrenList[$i]);
						}
					}
					unset($i, $fullPath, $name);
					if (self::$useTranslationRepository)
					{
						// translation Repository
						foreach ($this->checkLanguages as $langId)
						{
							$fullPathLang = Main\Localization\Translation::convertLangPath($parentFullPath.'/'.$langId, $langId);
							if (file_exists($fullPathLang))
							{
								$childrenList[] = $fullPathLang;
							}
						}
						unset($langId, $fullPathLang);
					}
				}

				if (!empty($childrenList))
				{
					$ignoreDev = implode('|', Translate\IGNORE_MODULE_NAMES);
					foreach ($childrenList as $fullPath)
					{
						$name = basename($fullPath);
						if (in_array($name, Translate\IGNORE_FS_NAMES))
						{
							continue;
						}

						$relPath = $parentRelPath . '/'. $name;

						if (!is_dir($fullPath))
						{
							continue;
						}

						if (in_array($relPath, Translate\IGNORE_BX_NAMES))
						{
							continue;
						}

						// /bitrix/modules/[smth]/dev/
						if (preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})$#", trim($relPath, '/')))
						{
							continue;
						}

						if ($isParentLang && in_array($name, Translate\IGNORE_LANG_NAMES))
						{
							continue;
						}

						$isLang = $isParentLang || ($name === 'lang');
						if ($isLang)
						{
							if (in_array($name, self::$availableLanguages))
							{
								// only active languages
								if (!in_array($name, self::$enabledLanguages))
								{
									continue;
								}
								$parentLangId = $name;
								$name = '#LANG_ID#';
							}
							$relPath = Translate\IO\Path::replaceLangId($relPath, '#LANG_ID#');
						}

						$pathId = null;

						if (isset($this->immediateChildren[$parentId][$relPath]))
						{
							$pathId = $this->immediateChildren[$parentId][$relPath];
						}

						if (self::$verbose)
						{
							echo "Path folder: {$relPath}";
						}
						if ($pathId === null)
						{
							$nodeData = array(
								'PARENT_ID' => $parentId,
								'NAME' => $name,
								'PATH' => $relPath,
								'IS_LANG' => $isLang ? 'Y' : 'N',
								'IS_DIR' => 'Y',
								'DEPTH_LEVEL' => $depthLevel,
							);

							if ($langSettings instanceof Translate\Settings)
							{
								$settings = $langSettings->getOptions($relPath);
								if (!empty($settings[Translate\Settings::OPTION_LANGUAGES]))
								{
									$nodeData['OBLIGATORY_LANGS'] = implode(',', $settings[Translate\Settings::OPTION_LANGUAGES]);
								}
							}

							$res = Index\Internals\PathIndexTable::add($nodeData);
							$pathId = $res->getId();

							$this->immediateChildren[$parentId][$relPath] = $pathId;
							$this->immediateChildren[$pathId] = [];

							$this->ancestorsPaths[$relPath] = array(
								'ID' => $pathId,
								'DEPTH_LEVEL' => $depthLevel,
								'IS_LANG' => $isLang,
								'PATH' => $relPath,
							);
						}

						if (self::$verbose)
						{
							echo "\tIndex id: {$pathId}\n";
						}

						$processedItemCount += $lookForLangDirectory($fullPath, $relPath, $pathId, $isLang, $parentLangId, $depthLevel + 1);// go deeper
						$processedItemCount ++;
					}
				}

				return $processedItemCount;
			};

		$processedItemCount = $lookForLangDirectory($fullPath, $relPath, $topPathId, $isTopLang, $topLangId, $topDepthLevel + 1);

		if ($isTopLang && isset($langSettings))
		{
			/** @var Translate\Settings $langSettings */
			$settings = $langSettings->getOptions('*');
			if (!empty($settings) && !empty($settings[Translate\Settings::OPTION_LANGUAGES]))
			{
				Index\Internals\PathIndexTable::bulkUpdate(
					['OBLIGATORY_LANGS' => implode(',', $settings[Translate\Settings::OPTION_LANGUAGES])],
					[
						'LOGIC' => 'OR',
						'=PATH' => $relPath,
						'=%PATH' => $relPath. '/%',
					]
				);
			}
			foreach ($langSettings as $settingPath => $settings)
			{
				if (strpos($settingPath, '*') !== false && $settingPath !== '*' && !empty($settings['languages']))
				{
					$settingPath = str_replace('*', '', $settingPath);
					Index\Internals\PathIndexTable::bulkUpdate(
						['OBLIGATORY_LANGS' => implode(',', $settings[Translate\Settings::OPTION_LANGUAGES])],
						[
							'LOGIC' => 'OR',
							'=PATH' => $relPath .'/#LANG_ID#/'. $settingPath,
							'=%PATH' => $relPath .'/#LANG_ID#/'. $settingPath. '/%',
						]
					);
				}
			}
			foreach ($langSettings as $settingPath => $settings)
			{
				if (mb_substr($settingPath, -4) === '.php' && !empty($settings[Translate\Settings::OPTION_LANGUAGES]))
				{
					Index\Internals\PathIndexTable::bulkUpdate(
						['OBLIGATORY_LANGS' => implode(',', $settings[Translate\Settings::OPTION_LANGUAGES])],
						['=PATH' => $relPath .'/#LANG_ID#/'. $settingPath]
					);
				}
			}
		}


		return $processedItemCount;
	}



	/**
	 * Searchs or creates ancestor index by path.
	 *
	 * @param string $path Path to search.
	 *
	 * @return array|null
	 */
	public function constructAncestorsByPath($path)
	{
		if (isset($this->ancestorsPaths[$path]))
		{
			return $this->ancestorsPaths[$path];
		}

		$pathParts = explode('/', trim($path, '/'));

		$searchPath = '';
		$ancestorsPathSearch = array();
		foreach ($pathParts as $part)
		{
			$searchPath .= '/'. $part;
			if (isset($this->ancestorsPaths[$searchPath]))
			{
				continue;
			}
			$ancestorsPathSearch[] = $searchPath;
		}
		$pathRes = Index\Internals\PathIndexTable::getList([
			'select' => ['ID', 'DEPTH_LEVEL', 'IS_LANG', 'PATH'],
			'filter' => ['=PATH' => $ancestorsPathSearch],
		]);
		while ($pathInx = $pathRes->fetch())
		{
			$pathInx['IS_LANG'] = ($pathInx['IS_LANG'] == 'Y');
			$this->ancestorsPaths[$pathInx['PATH']] = $pathInx;
		}

		if (isset($this->ancestorsPaths[$path]))
		{
			return $this->ancestorsPaths[$path];
		}


		$pathInx = null;
		$searchPath = '';
		$searchParentId = 0;
		$searchDepthLevel = 0;
		$isLang = false;

		foreach ($pathParts as $part)
		{
			$searchPath .= '/'. $part;

			if (isset($this->ancestorsPaths[$searchPath]) && $searchPath !== $path)
			{
				$searchParentId = (int)$this->ancestorsPaths[$searchPath]['ID'];
				$searchDepthLevel = (int)$this->ancestorsPaths[$searchPath]['DEPTH_LEVEL'] + 1;
				$isLang = $this->ancestorsPaths[$searchPath]['IS_LANG'];
				continue;
			}

			if ($isLang === false)
			{
				$isLang = ($part === 'lang');
			}

			$nodeData = array(
				'NAME' => $part,
				'PATH' => $searchPath,
				'PARENT_ID' => $searchParentId,
				'DEPTH_LEVEL' => $searchDepthLevel,
				'IS_LANG' => $isLang ? 'Y' : 'N',
				'IS_DIR' => (mb_substr($part, -4) === '.php' ? 'N' : 'Y'),
			);

			$pathInx = Index\Internals\PathIndexTable::add($nodeData);
			$searchParentId = $pathInx->getId();

			$this->ancestorsPaths[$searchPath] = array(
				'ID' => $searchParentId,
				'DEPTH_LEVEL' => $searchDepthLevel,
				'IS_LANG' => $isLang,
				'PATH' => $searchPath,
			);

			$searchDepthLevel ++;
		}

		return $this->ancestorsPaths[$path];
	}


	/**
	 * Looks for immediate children.
	 *
	 * @param int $parentId Parent Id.
	 *
	 * @return Index\PathIndex[]
	 */
	private function getImmediateChildren($parentId)
	{
		if (!isset($this->immediateChildren[$parentId]))
		{
			$this->immediateChildren[$parentId] = array();

			$nodeRes = Index\Internals\PathIndexTable::getList(array(
				'filter' => ['=PARENT_ID' => $parentId],
				'select' => ['ID', 'PATH'],
			));
			while ($nodeInx = $nodeRes->fetch())
			{
				$this->immediateChildren[$parentId][$nodeInx['PATH']] = (int)$nodeInx['ID'];
			}
		}

		return $this->immediateChildren[$parentId];
	}


	/**
	 * Looks for ancestors by path.
	 *
	 * @param int $nodeId Index path to search ancestors.
	 * @param int $topNodeId The highest index path.
	 *
	 * @return Index\PathIndex[]
	 */
	private function getAncestors($nodeId, $topNodeId = -1)
	{
		$nodeRes = Index\Internals\PathIndexTable::getList([
			'filter' => ['=ID' => (int)$nodeId],
		]);

		$result = array();
		if ($nodeInx = $nodeRes->fetchObject())
		{
			$result[$nodeInx->getId()] = $nodeInx;

			if ((int)$nodeInx->getParentId() > 0)
			{
				$nodeRes = Index\Internals\PathIndexTable::getList([
					'filter' => [
						'=DESCENDANTS.PARENT_ID' => $nodeInx->getId(),//ancestor
					],
					'order' => ['DESCENDANTS.DEPTH_LEVEL' => 'DESC'],
				]);
				while ($nodeInx = $nodeRes->fetchObject())
				{
					$result[$nodeInx->getId()] = $nodeInx;

					if ((int)$nodeInx->getParentId() == 0)
					{
						break;
					}
					if ($topNodeId > 0 && (int)$nodeInx->getId() == $topNodeId)
					{
						break;
					}
				}
			}
		}

		return array_reverse($result, true);
	}

	/**
	 * Rearrange tree as nested set structure.
	 *
	 * @return self
	 */
	public function arrangeTree()
	{
		$pathList = Index\Internals\PathIndexTable::getList([
			'filter' => [
				'=PARENT_ID' => 0,
				'=IS_DIR' => 'Y',
			],
			'select' => ['ID'],
		]);

		while ($path = $pathList->fetch())
		{
			Index\Internals\PathIndexTable::arrangeTree($path['ID']);
		}

		return $this;
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
		Index\Internals\PathIndexTable::purge($filter, $recursively);

		return $this;
	}

	/**
	 * Unvalidate index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param bool $recursively Drop index recursively.
	 *
	 * @return self
	 */
	public function validate(Translate\Filter $filter = null, $recursively = true)
	{
		if (($filterOut = Index\Internals\PathIndexTable::processFilter($filter)) !== false)
		{
			$update = ['INDEXED' => 'Y', 'INDEXED_TIME' => new Main\Type\DateTime()];
			Index\Internals\PathIndexTable::bulkUpdate($update, $filterOut);

			if ($recursively)
			{
				if (($filterOut = Index\Internals\FileIndexTable::processFilter($filter)) !== false)
				{
					Index\Internals\FileIndexTable::bulkUpdate($update, $filterOut);
				}
			}
		}

		return $this;
	}

	/**
	 * Unvalidate index.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 * @param bool $recursively Drop index recursively.
	 *
	 * @return self
	 */
	public function unvalidate(Translate\Filter $filter = null, $recursively = true)
	{
		if (($filterOut = Index\Internals\PathIndexTable::processFilter($filter)) !== false)
		{
			Index\Internals\PathIndexTable::bulkUpdate(['INDEXED' => 'N'], $filterOut);

			if ($recursively)
			{
				if (($filterOut = Index\Internals\FileIndexTable::processFilter($filter)) !== false)
				{
					Index\Internals\FileIndexTable::bulkUpdate(['INDEXED' => 'N'], $filterOut);
				}
			}
		}

		return $this;
	}


	/**
	 * Collect sssignment file to module.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function collectModuleAssignment(Translate\Filter $filter = null)
	{
		$searchPath = isset($filter, $filter->path) ? $filter->path : '';

		if (!empty($searchPath))
		{
			$pathStartRes = Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=PATH' => $searchPath,
				],
				'select' => ['ID', 'PATH'],
			]);
			if ($path = $pathStartRes->fetchObject())
			{
				$relPathParts = explode('/', trim($path->getPath(), '/'));

				// /bitrix/modules/[smth]/
				if (count($relPathParts) >= 3 && $relPathParts[0] == 'bitrix' && $relPathParts[1] == 'modules')
				{
					$moduleId = $path->detectModuleId();
					if ($moduleId !== null)
					{
						Index\Internals\PathIndexTable::bulkUpdate(
							['MODULE_ID' => $moduleId],
							['=DESCENDANTS.PARENT_ID' => $path->getId()]
						);
					}
				}

				//todo: else select sub nodes
			}

		}
		else
		{
			$pathModulesRes = Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=PATH' => '/bitrix/modules',
				],
				'select' => ['ID'],
			]);
			while ($pathModules = $pathModulesRes->fetch())
			{
				$pathList = Index\Internals\PathIndexTable::getList([
					'filter' => [
						'=PARENT_ID' => $pathModules['ID'],
					],
					'select' => ['ID', 'PATH'],
				]);
				while ($path = $pathList->fetchObject())
				{
					$moduleId = $path->detectModuleId();
					if ($moduleId !== null)
					{
						Index\Internals\PathIndexTable::bulkUpdate(
							['MODULE_ID' => $moduleId],
							['=DESCENDANTS.PARENT_ID' => $path->getId()]
						);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Collect file asssignment.
	 *
	 * @param Translate\Filter $filter Params to filter file list.
	 *
	 * @return self
	 */
	public function collectAssignment(Translate\Filter $filter = null)
	{
		// /bitrix/(mobileapp|templates|components|activities|wizards|gadgets|js|..)
		foreach (Translate\ASSIGNMENT_TYPES as $assignmentId)
		{
			$pathEntryRes = Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=PATH' => '/bitrix/'. $assignmentId,
				],
				'select' => ['ID', 'PATH'],
			]);
			while ($path = $pathEntryRes->fetchObject())
			{
				Index\Internals\PathIndexTable::bulkUpdate(
					['ASSIGNMENT' => $assignmentId],
					['=DESCENDANTS.PARENT_ID' => $path->getId()]
				);
			}
		}

		$pathModulesRes = Index\Internals\PathIndexTable::getList([
			'filter' => [
				'=PATH' => '/bitrix/modules',
			],
			'select' => ['ID'],
		]);
		while ($pathModules = $pathModulesRes->fetch())
		{
			$pathList = Index\Internals\PathIndexTable::getList([
				'filter' => [
					'=PARENT_ID' => $pathModules['ID'],
					'!=MODULE_ID' => null,
				],
				'select' => ['ID', 'PATH', 'MODULE_ID'],
			]);
			while ($modulePath = $pathList->fetchObject())
			{
				$moduleId = $modulePath->getModuleId();

				foreach (Translate\ASSIGNMENT_TYPES as $assignmentId)
				{
					$filterPaths = [
						// /bitrix/modules/[moduleName]/install/[smth]
						'/bitrix/modules/'.$moduleId.'/install/'. $assignmentId,
						// /bitrix/modules/[moduleName]/lang/#LANG_ID#/[smth]
						'/bitrix/modules/'.$moduleId.'/lang/#LANG_ID#/'. $assignmentId,
						// /bitrix/modules/[moduleName]/lang/#LANG_ID#/install/[smth]
						'/bitrix/modules/'.$moduleId.'/lang/#LANG_ID#/install/'. $assignmentId,
						// /bitrix/modules/[moduleName]/install/bitrix/templates/[templateName]
						'/bitrix/modules/'.$moduleId.'/install/bitrix/'. $assignmentId,
						// /bitrix/modules/[moduleName]/handlers/delivery/[smth]
						// /bitrix/modules/[moduleName]/handlers/paysystem/[smth]
						'/bitrix/modules/'.$moduleId.'/handlers/'. $assignmentId,
					];
					if ($assignmentId == 'templates')
					{
						// /bitrix/modules/[moduleName]/install/public/templates/[templateName]
						$filterPaths[] = '/bitrix/modules/'.$moduleId.'/install/public/'. $assignmentId;
					}
					$pathEntryRes = Index\Internals\PathIndexTable::getList([
						'filter' => [
							'=PATH' => $filterPaths,
							'=DESCENDANTS.PARENT_ID' => $modulePath->getId(),
						],
						'select' => ['ID', 'PATH'],
					]);
					while ($path = $pathEntryRes->fetchObject())
					{
						Index\Internals\PathIndexTable::bulkUpdate(
							['ASSIGNMENT' => $assignmentId],
							['=DESCENDANTS.PARENT_ID' => $path->getId()]
						);
					}
				}
			}
		}

		return $this;
	}

}
