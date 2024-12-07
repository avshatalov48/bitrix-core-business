<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;

class FileIndexSearch
{
	/**
	 * Performs search query and returns result.
	 *
	 * @param array $params Orm type params for the query.
	 * @return Main\ORM\Query\Query
	 */
	public static function query($params = [])
	{
		[$select, $runtime, $filter] = self::processParams($params);

		/** @var \Bitrix\Main\ORM\Entity $entity */
		$entity = Index\Internals\PathIndexTable::getEntity();
		foreach ($runtime as $field)
		{
			$entity->addField($field);
		}

		return new Main\ORM\Query\Query($entity);
	}


	/**
	 * Counts rows in search result.
	 *
	 * @param array $filterIn Filter params.
	 * @return int
	 */
	public static function getCount($filterIn): int
	{
		[$select, $runtime, $filter] = self::processParams(['filter' => $filterIn]);

		/** @var \Bitrix\Main\ORM\Entity $entity */
		$entity = Index\Internals\PathIndexTable::getEntity();
		foreach ($runtime as $field)
		{
			$entity->addField($field);
		}

		$query = new Main\ORM\Query\Query($entity);

		$query
			->addSelect(new Main\ORM\Fields\ExpressionField('CNT', 'COUNT(1)'))
			->setFilter($filter);

		$result = $query->exec()->fetch();

		return (int)$result['CNT'];
	}


	/**
	 * Searches phrase by index.
	 *
	 * @param array $params Orm type params for the query.
	 * @return Main\ORM\Query\Result
	 */
	public static function getList($params): Main\ORM\Query\Result
	{
		[$select, $runtime, $filter] = self::processParams($params);

		$executeParams = [
			'select' => \array_merge(
				[
					'PATH_ID' => 'ID',
					'PATH' => 'PATH',
					'IS_LANG' => 'IS_LANG',
					'IS_DIR' => 'IS_DIR',
					'TITLE' => 'NAME',
				],
				$select
			),
			'runtime' => $runtime,
			'filter' => $filter,
		];

		if (isset($params['order']))
		{
			$executeParams['order'] = $params['order'];
		}
		if (isset($params['offset']))
		{
			$executeParams['offset'] = $params['offset'];
		}
		if (isset($params['limit']))
		{
			$executeParams['limit'] = $params['limit'];
		}
		if (isset($params['count_total']))
		{
			$executeParams['count_total'] = true;
		}

		return Index\Internals\PathIndexTable::getList($executeParams);
	}


	/**
	 * Processes select and filter params to convert them into orm type.
	 *
	 * @param array $params Orm type params for the query.
	 * @return array
	 */
	private static function processParams($params): array
	{
		$select = $runtime = $filterIn = $filterOut = [];
		if (isset($params['filter']))
		{
			if (\is_object($params['filter']))
			{
				$filterIn = clone $params['filter'];
			}
			else
			{
				$filterIn = $params['filter'];
			}
		}

		$enabledLanguages = Translate\Config::getEnabledLanguages();
		$languageUpperKeys = \array_combine($enabledLanguages, \array_map('mb_strtoupper', $enabledLanguages));

		$selectedLanguages = [];
		foreach ($languageUpperKeys as $langId => $langUpper)
		{
			$alias = "{$langUpper}_LANG";
			if (isset($params['select']) && \in_array($alias, $params['select']))
			{
				$selectedLanguages[] = $langId;
			}
			elseif (isset($params['order'], $params['order'][$alias]))
			{
				$selectedLanguages[] = $langId;
			}
			elseif (isset($filterIn['LANGUAGE_ID']) && $filterIn['LANGUAGE_ID'] == $langId)
			{
				$selectedLanguages[] = $langId;
			}
		}
		if (empty($selectedLanguages))
		{
			$selectedLanguages = $enabledLanguages;
		}

		// top folder
		if (!empty($filterIn['PATH']))
		{
			$topIndexPath = Index\PathIndex::loadByPath($filterIn['PATH']);
			if ($topIndexPath instanceof Index\PathIndex)
			{
				$filterOut['=DESCENDANTS.PARENT_ID'] = $topIndexPath->getId();//ancestor
			}
		}
		unset($filterIn['PATH']);

		foreach ($languageUpperKeys as $langId => $langUpper)
		{
			if (
				!\in_array($langId, $selectedLanguages) &&
				!Main\Localization\Translation::isDefaultTranslationLang($langId)
			)
			{
				continue;
			}

			$alias = "{$langUpper}_LANG";
			$tblAlias = "File{$alias}";

			$searchByLang = false;
			if (!empty($filterIn['LANGUAGE_ID']) && $langId == $filterIn['LANGUAGE_ID'])
			{
				$searchByLang = true;
			}

			$runtime[] = new Main\ORM\Fields\Relations\Reference(
				$tblAlias,
				Index\Internals\FileIndexTable::class,
				Main\ORM\Query\Join::on('ref.PATH_ID', '=', 'this.ID')->where('ref.LANG_ID', '=', $langId),
				['join_type' => $searchByLang ? 'INNER' : 'LEFT']
			);

			$select[$alias] = "{$tblAlias}.PHRASE_COUNT";
		}
		unset($filterIn['LANGUAGE_ID']);

		// is any file exists in main rep
		if (Main\Localization\Translation::useTranslationRepository())
		{
			$statement = '';
			$fields = [];
			foreach ($languageUpperKeys as $langId => $langUpper)
			{
				if (Main\Localization\Translation::isDefaultTranslationLang($langId))
				{
					$alias = "{$langUpper}_LANG";
					$fields[] = "File{$alias}.ID";
					$statement .= ' WHEN %s IS NOT NULL THEN 1 ';
				}
			}
			unset($langId, $langUpper, $alias, $tblAlias, $fieldAlias);

			$runtime[] =
				new Main\ORM\Fields\ExpressionField(
					'IS_EXIST',
					"CASE {$statement} ELSE 0 END",
					$fields
				);
			$select[] = 'IS_EXIST';
			unset($statement, $fields);
		}

		// folder name
		/*
		if (!empty($filterIn['FOLDER_NAME']))
		{
			$filterOut['!=NAME'] = '#LANG_ID#';
			if (empty($filterIn['FILE_NAME']))
			{
				$filterOut['%NAME'] = $filterIn['FOLDER_NAME'];
				$filterOut['=IS_DIR'] = 'Y';
			}
			else
			{
				$filterOut['%=PATH'] = '%/'. $filterIn['FOLDER_NAME']. '/%';
			}
		}
		*/

		$filterOut['=IS_DIR'] = 'N';
		if (!empty($filterIn['FOLDER_NAME']))
		{
			$filterOut['%=PATH'] = '%/'. $filterIn['FOLDER_NAME']. '/%';
		}

		// only files
		if (!empty($filterIn['FILE_NAME']))
		{
			$filterOut['%NAME'] = $filterIn['FILE_NAME'];
		}
		unset($filterIn['FILE_NAME'], $filterIn['FOLDER_NAME']);

		$replaceLangId = static function(&$val)
		{
			$val = Translate\IO\Path::replaceLangId($val, '#LANG_ID#');
		};
		$trimSlash = static function(&$val)
		{
			if (Translate\IO\Path::isPhpFile($val))
			{
				$val = '/'. \trim($val, '/');
			}
			else
			{
				$val = '/'. \trim($val, '/'). '/%';
			}
		};

		if (!empty($filterIn['INCLUDE_PATHS']))
		{
			$pathIncludes = \preg_split("/[\r\n\t,; ]+/u", $filterIn['INCLUDE_PATHS']);
			$pathIncludes = \array_filter($pathIncludes);
			if (\count($pathIncludes) > 0)
			{
				$pathPathIncludes = [];
				$pathNameIncludes = [];
				foreach ($pathIncludes as $testPath)
				{
					if (!empty($testPath) && \trim($testPath) !== '')
					{
						if (\mb_strpos($testPath, '/') === false)
						{
							$pathNameIncludes[] = $testPath;
						}
						else
						{
							$pathPathIncludes[] = $testPath;
						}
					}
				}
				if (\count($pathNameIncludes) > 0 && \count($pathPathIncludes) > 0)
				{
					\array_walk($pathNameIncludes, $replaceLangId);
					\array_walk($pathPathIncludes, $replaceLangId);
					\array_walk($pathPathIncludes, $trimSlash);
					$filterOut[] = [
						'LOGIC' => 'OR',
						'%=NAME' => $pathNameIncludes,
						'%=PATH' => $pathPathIncludes,
					];
				}
				elseif (\count($pathNameIncludes) > 0)
				{
					\array_walk($pathNameIncludes, $replaceLangId);
					$filterOut[] = [
						'LOGIC' => 'OR',
						'%=NAME' => $pathNameIncludes,
						'%=PATH' => $pathNameIncludes,
					];
				}
				elseif (\count($pathPathIncludes) > 0)
				{
					\array_walk($pathPathIncludes, $replaceLangId);
					\array_walk($pathPathIncludes, $trimSlash);
					$filterOut['%=PATH'] = $pathPathIncludes;
				}
			}
			unset($testPath, $pathIncludes, $pathNameIncludes, $pathPathIncludes);
		}
		if (!empty($filterIn['EXCLUDE_PATHS']))
		{
			$pathExcludes = \preg_split("/[\r\n\t,; ]+/u", $filterIn['EXCLUDE_PATHS']);
			$pathExcludes = \array_filter($pathExcludes);
			if (\count($pathExcludes) > 0)
			{
				$pathPathExcludes = [];
				$pathNameExcludes = [];
				foreach ($pathExcludes as $testPath)
				{
					if (!empty($testPath) && \trim($testPath) !== '')
					{
						if (\mb_strpos($testPath, '/') === false)
						{
							$pathNameExcludes[] = $testPath;
						}
						else
						{
							$pathPathExcludes[] = $testPath;
						}
					}
				}
				if (\count($pathNameExcludes) > 0 && \count($pathPathExcludes) > 0)
				{
					\array_walk($pathNameExcludes, $replaceLangId);
					\array_walk($pathPathExcludes, $replaceLangId);
					\array_walk($pathPathExcludes, $trimSlash);
					$filterOut[] = [
						'LOGIC' => 'AND',
						'!=%NAME' => $pathNameExcludes,
						'!=%PATH' => $pathPathExcludes,
					];
				}
				elseif (\count($pathNameExcludes) > 0)
				{
					\array_walk($pathNameExcludes, $replaceLangId);
					$filterOut[] = [
						'LOGIC' => 'AND',
						'!=%NAME' => $pathNameExcludes,
						'!=%PATH' => $pathNameExcludes,
					];
				}
				elseif (\count($pathPathExcludes) > 0)
				{
					\array_walk($pathPathExcludes, $replaceLangId);
					\array_walk($pathPathExcludes, $trimSlash);
					$filterOut["!=%PATH"] = $pathPathExcludes;
				}
			}
			unset($testPath, $pathExcludes, $pathPathExcludes, $pathNameExcludes);
		}
		unset($filterIn['INCLUDE_PATHS'], $filterIn['EXCLUDE_PATHS']);

		/*
		todo: Revert type assignment

		if (!empty($filterIn['ASSIGNMENT']))
		{
			$filterOut['=ASSIGNMENT'] = $filterIn['ASSIGNMENT'];
		}
		*/

		/*
		todo: Revert module assignment

		if (!empty($filterIn['MODULE_ID']))
		{
			$filterOut['=MODULE_ID'] = $filterIn['MODULE_ID'];
		}
		*/

		foreach ($filterIn as $key => $value)
		{
			if (\in_array($key, ['tabId', 'FILTER_ID', 'PRESET_ID', 'FILTER_APPLIED', 'FIND']))
			{
				continue;
			}
			$filterOut[$key] = $value;
		}

		return [$select, $runtime, $filterOut];
	}
}
