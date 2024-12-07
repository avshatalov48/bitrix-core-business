<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Translate;
use Bitrix\Translate\Index;

class PhraseIndexSearch
{
	public const SEARCH_METHOD_EXACT = 'exact';
	public const SEARCH_METHOD_EQUAL = 'equal';
	public const SEARCH_METHOD_CASE_SENSITIVE = 'case_sensitive';
	public const SEARCH_METHOD_ENTRY_WORD = 'entry_word';
	public const SEARCH_METHOD_START_WITH = 'start_with';
	public const SEARCH_METHOD_END_WITH = 'end_with';

	// Lang code by IETF BCP 47
	private const NON_FTS = ['th', 'zh-cn', 'zh-tw', 'ja', 'ko'];

	/**
	 * Performs search query and returns result.
	 *
	 * @param array $params Orm type params for the query.
	 * @return Main\ORM\Query\Query
	 */
	public static function query(array $params = []): Main\ORM\Query\Query
	{
		[, $runtime, ] = self::processParams($params);

		$entity = self::getPathCodeEntity();
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
	public static function getCount(array $filterIn): int
	{
		[, $runtime, $filter] = self::processParams(['filter' => $filterIn]);

		$entity = self::getPathCodeEntity();
		foreach ($runtime as $field)
		{
			$entity->addField($field);
		}

		$query = new Main\ORM\Query\Query($entity);

		$query
			->addSelect(new ExpressionField('CNT', 'COUNT(1)'))
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
	public static function getList(array $params): Main\ORM\Query\Result
	{
		[$select, $runtime, $filter] = self::processParams($params);

		$executeParams = [
			'select' => \array_merge(
				[
					'PATH_ID' => 'PATH_ID',
					'PHRASE_CODE' => 'CODE',
					'FILE_PATH' => 'PATH.PATH',
					'TITLE' => 'PATH.NAME',
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

		$entityClass = self::getPathCodeEntityClass();

		return $entityClass::getList($executeParams);
	}



	/**
	 * @return DataManager|string
	 * @throws ArgumentException
	 */
	public static function getPathCodeEntityClass(): string
	{
		static $class;
		if ($class === null)
		{
			$entity = self::getPathCodeEntity();
			$class = $entity->getDataClass();
		}

		return $class;
	}

	/**
	 * @return Main\ORM\Entity
	 * @throws ArgumentException
	 */
	public static function getPathCodeEntity(): Main\ORM\Entity
	{
		static $entity;
		if ($entity === null)
		{
			$subQuery = (new Main\ORM\Query\Query(Index\Internals\PhraseIndexTable::getEntity()))
				->setSelect(['PATH_ID', 'CODE'])
				->setGroup(['PATH_ID', 'CODE']);

			$entity = Main\ORM\Entity::compileEntity(
				'PathPhraseIndexReference',
				[
					'PATH_ID' => ['data_type' => 'string'],
					'CODE' => ['data_type' => 'string'],
				],
				[
					'table_name' => '('.$subQuery->getQuery().')',
					'namespace' => __NAMESPACE__. '\\Internals',
				]
			);
		}

		return $entity;
	}

	/**
	 * Processes select and filter params to convert them into orm type.
	 *
	 * @param array $params Orm type params for the query.
	 * @return array
	 */
	private static function processParams(array $params): array
	{
		$select = [];
		$runtime = [];
		$filterIn = [];
		$filterOut = [];
		$runtimeInx = [];

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

		foreach ($languageUpperKeys as $langId => $langUpper)
		{
			$tbl = "{$langUpper}_LNG";
			$alias = "{$langUpper}_LANG";

			if (
				!empty($params['select']) && in_array($alias, $params['select'])
				|| isset($params['order'], $params['order'][$alias])
			)
			{
				$i = count($runtime);
				$runtimeInx[$tbl] = $i;
				$runtime[$i] = new Main\ORM\Fields\Relations\Reference(
					$tbl,
					Index\Internals\PhraseFts::getFtsEntityClass($langId),
					Main\ORM\Query\Join::on('ref.PATH_ID', '=', 'this.PATH_ID')
						->whereColumn('ref.CODE', '=', 'this.CODE'),
					['join_type' => 'LEFT']
				);

				$runtimeInx[$alias] = $i++;
				$runtime[$i] = new ExpressionField($alias, '%s', "{$tbl}.PHRASE");
				$select[] = $alias;
			}
		}

		if (!isset($filterIn['PHRASE_ENTRY']))
		{
			$filterIn['PHRASE_ENTRY'] = [];
		}
		if (!isset($filterIn['CODE_ENTRY']))
		{
			$filterIn['CODE_ENTRY'] = [];
		}

		// top folder
		if (!empty($filterIn['PATH']))
		{
			$topIndexPath = Index\PathIndex::loadByPath($filterIn['PATH']);
			if ($topIndexPath instanceof Index\PathIndex)
			{
				$filterOut['=PATH.DESCENDANTS.PARENT_ID'] = $topIndexPath->getId();//ancestor
			}
			unset($filterIn['PATH']);
		}

		// search by code
		if (!empty($filterIn['INCLUDE_PHRASE_CODES']))
		{
			$codes = \preg_split("/[\r\n\t,; ]+/u", $filterIn['INCLUDE_PHRASE_CODES']);
			$codes = \array_filter($codes);
			if (\count($codes) > 0)
			{
				$useLike = false;
				foreach ($codes as $code)
				{
					if (\mb_strpos($code, '%') !== false)
					{
						$useLike = true;
						break;
					}
				}
				if ($useLike)
				{
					$filterOut['=%CODE'] = $codes;
				}
				else
				{
					$filterOut['=CODE'] = $codes;
				}
			}
			unset($filterIn['INCLUDE_PHRASE_CODES']);
		}
		if (!empty($filterIn['EXCLUDE_PHRASE_CODES']))
		{
			$codes = \preg_split("/[\r\n\t,; ]+/u", $filterIn['EXCLUDE_PHRASE_CODES']);
			$codes = \array_filter($codes);
			if (\count($codes) > 0)
			{
				$useLike = false;
				foreach ($codes as $code)
				{
					if (\mb_strpos($code, '%') !== false)
					{
						$useLike = true;
						break;
					}
				}
				if ($useLike)
				{
					$filterOut["!=%CODE"] = $codes;
				}
				else
				{
					$filterOut["!=CODE"] = $codes;
				}
			}
			unset($filterIn['EXCLUDE_PHRASE_CODES']);
		}

		if (!empty($filterIn['PHRASE_CODE']))
		{
			if (\in_array(self::SEARCH_METHOD_CASE_SENSITIVE, $filterIn['CODE_ENTRY']))
			{
				if (\in_array(self::SEARCH_METHOD_EQUAL, $filterIn['CODE_ENTRY']))
				{
					$filterOut["=CODE"] = $filterIn['PHRASE_CODE'];
				}
				elseif (\in_array(self::SEARCH_METHOD_START_WITH, $filterIn['CODE_ENTRY']))
				{
					$filterOut["=%CODE"] = $filterIn['PHRASE_CODE'].'%';
				}
				elseif (\in_array(self::SEARCH_METHOD_END_WITH, $filterIn['CODE_ENTRY']))
				{
					$filterOut["=%CODE"] = '%'.$filterIn['PHRASE_CODE'];
				}
				else
				{
					$filterOut["=%CODE"] = '%'.$filterIn['PHRASE_CODE'].'%';
				}
			}
			else
			{
				$runtime[] = new ExpressionField('CODE_UPPER', 'UPPER(CONVERT(%s USING latin1))', 'CODE');
				if (\in_array(self::SEARCH_METHOD_EQUAL, $filterIn['CODE_ENTRY']))
				{
					$filterOut['=CODE_UPPER'] = \mb_strtoupper($filterIn['PHRASE_CODE']);
				}
				elseif (\in_array(self::SEARCH_METHOD_START_WITH, $filterIn['CODE_ENTRY']))
				{
					$filterOut['=%CODE_UPPER'] = \mb_strtoupper($filterIn['PHRASE_CODE']).'%';
				}
				elseif (\in_array(self::SEARCH_METHOD_END_WITH, $filterIn['CODE_ENTRY']))
				{
					$filterOut['=%CODE_UPPER'] = '%'.\mb_strtoupper($filterIn['PHRASE_CODE']);
				}
				else
				{
					$filterOut['=%CODE_UPPER'] = '%'.\mb_strtoupper($filterIn['PHRASE_CODE']).'%';
				}
			}
		}
		unset($filterIn['PHRASE_CODE'], $filterIn['CODE_ENTRY']);

		$runtime[] = new Main\ORM\Fields\Relations\Reference(
			'PATH',
			Index\Internals\PathIndexTable::class,
			Main\ORM\Query\Join::on('ref.ID', '=', 'this.PATH_ID'),
			['join_type' => 'INNER']
		);

		$filterOut['=PATH.IS_DIR'] = 'N';

		$replaceLangId = function(&$val)
		{
			$val = Translate\IO\Path::replaceLangId($val, '#LANG_ID#');
		};
		$trimSlash = function(&$val)
		{
			if (\mb_strpos($val, '%') === false)
			{
				if (Translate\IO\Path::isPhpFile($val))
				{
					$val = '/'. \trim($val, '/');
				}
				else
				{
					$val = '/'. \trim($val, '/'). '/%';
				}
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
						'%=PATH.NAME' => $pathNameIncludes,
						'%=PATH.PATH' => $pathPathIncludes,
					];
				}
				elseif (\count($pathNameIncludes) > 0)
				{
					\array_walk($pathNameIncludes, $replaceLangId);
					$filterOut[] = [
						'LOGIC' => 'OR',
						'%=PATH.NAME' => $pathNameIncludes,
						'%=PATH.PATH' => $pathNameIncludes,
					];
				}
				elseif (\count($pathPathIncludes) > 0)
				{
					\array_walk($pathPathIncludes, $replaceLangId);
					\array_walk($pathPathIncludes, $trimSlash);
					$filterOut['%=PATH.PATH'] = $pathPathIncludes;
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
						'!=%PATH.NAME' => $pathNameExcludes,
						'!=%PATH.PATH' => $pathPathExcludes,
					];
				}
				elseif (\count($pathNameExcludes) > 0)
				{
					\array_walk($pathNameExcludes, $replaceLangId);
					$filterOut[] = [
						'LOGIC' => 'AND',
						'!=%PATH.NAME' => $pathNameExcludes,
						'!=%PATH.PATH' => $pathNameExcludes,
					];
				}
				elseif (\count($pathPathExcludes) > 0)
				{
					\array_walk($pathPathExcludes, $replaceLangId);
					\array_walk($pathPathExcludes, $trimSlash);
					$filterOut["!=%PATH.PATH"] = $pathPathExcludes;
				}
			}
			unset($testPath, $pathExcludes, $pathPathExcludes, $pathNameExcludes);
		}
		unset($filterIn['INCLUDE_PATHS'], $filterIn['EXCLUDE_PATHS']);

		// search by phrase
		if (!empty($filterIn['PHRASE_TEXT']))
		{
			$langId = !empty($filterIn['LANGUAGE_ID']) ? $filterIn['LANGUAGE_ID'] : Loc::getCurrentLang();

			$langUpper = $languageUpperKeys[$langId];
			$tbl = "{$langUpper}_LNG";
			$alias = "{$langUpper}_LANG";
			$fieldAlias = "{$tbl}.PHRASE";

			/*
			$runtime[] = new Main\ORM\Fields\Relations\Reference(
				$tbl,
				Index\Internals\PhraseIndexTable::class,
				Main\ORM\Query\Join::on('ref.PATH_ID', '=', 'this.PATH_ID')
					->whereColumn('ref.CODE', '=', 'this.CODE')
					->where('ref.LANG_ID', '=', $langId),
				['join_type' => 'INNER']
			);
			*/

			$i = isset($runtimeInx[$tbl]) ? $runtimeInx[$tbl] : count($runtime);

			$runtime[$i] = new Main\ORM\Fields\Relations\Reference(
				$tbl,
				Index\Internals\PhraseFts::getFtsEntityClass($langId),
				Main\ORM\Query\Join::on('ref.PATH_ID', '=', 'this.PATH_ID')
					->whereColumn('ref.CODE', '=', 'this.CODE'),
				['join_type' => 'INNER']
			);

			if (!isset($runtimeInx[$alias]))
			{
				$select[$alias] = $fieldAlias;
			}
			$select["{$langUpper}_FILE_ID"] = "{$tbl}.FILE_ID";

			$exact = \in_array(self::SEARCH_METHOD_EXACT, $filterIn['PHRASE_ENTRY']);
			$entry = \in_array(self::SEARCH_METHOD_ENTRY_WORD, $filterIn['PHRASE_ENTRY']);
			$case = \in_array(self::SEARCH_METHOD_CASE_SENSITIVE, $filterIn['PHRASE_ENTRY']);
			$start = \in_array(self::SEARCH_METHOD_START_WITH, $filterIn['PHRASE_ENTRY']);
			$end = \in_array(self::SEARCH_METHOD_END_WITH, $filterIn['PHRASE_ENTRY']);
			$equal = \in_array(self::SEARCH_METHOD_EQUAL, $filterIn['PHRASE_ENTRY']);

			if ($exact)
			{
				$phraseSearch = ["={$fieldAlias}" => $filterIn['PHRASE_TEXT']];
			}
			else
			{
				$sqlHelper = Main\Application::getConnection()->getSqlHelper();
				$str = $sqlHelper->forSql($filterIn['PHRASE_TEXT']);

				$phraseSearch = [
					'LOGIC' => 'AND'
				];

				// use fulltext index to help like operator
				// todo: preg_replace has bug when replaces Thai unicode Non-spacing mark
				if (!self::disallowFtsIndex($langId))
				{
					$minLengthFulltextWorld = self::getFullTextMinLength();
					$fulltextIndexSearchStr = self::prepareTextForFulltextSearch($filterIn['PHRASE_TEXT']);
					if (\mb_strlen($fulltextIndexSearchStr) > $minLengthFulltextWorld)
					{
						/*
						if ($entry)
						{
							// identical full text match
							// MATCH(PHRASE) AGAINST ('+smth' IN BOOLEAN MODE)
							//$phraseSearch["*={$fieldAlias}"] = $fulltextIndexSearchStr;
							$fulltextIndexSearchStr = "+" . \preg_replace("/\s+/iu", " +", $fulltextIndexSearchStr);
							$runtime[] = (new ExpressionField(
								'PHRASE_FTS',
								"MATCH (%s) AGAINST ('({$fulltextIndexSearchStr})')",
								"{$fieldAlias}"
							))->configureValueType(FloatField::class);
						}
						else
						{
							// use fulltext index to help like operator
							// partial full text match
							// MATCH(PHRASE) AGAINST ('+smth*' IN BOOLEAN MODE)
							//$phraseSearch["*{$fieldAlias}"] = $fulltextIndexSearchStr;
							$fulltextIndexSearchStr = "+" . \preg_replace("/\s+/iu", "* +", $fulltextIndexSearchStr) . "*";
							$runtime[] = (new ExpressionField(
								'PHRASE_FTS',
								"MATCH (%s) AGAINST ('({$fulltextIndexSearchStr})')",
								"{$fieldAlias}"
							))->configureValueType(FloatField::class);
						}
						$phraseSearch[">PHRASE_FTS"] = 0;
						*/

						if ($entry)
						{
							// identical full text match
							// MATCH(PHRASE) AGAINST ('+smth' IN BOOLEAN MODE)
							$phraseSearch["*={$fieldAlias}"] = $fulltextIndexSearchStr;
						}
						else
						{
							// use fulltext index to help like operator
							// partial full text match
							// MATCH(PHRASE) AGAINST ('+smth*' IN BOOLEAN MODE)
							$phraseSearch["*{$fieldAlias}"] = $fulltextIndexSearchStr;
						}
					}
				}

				if ($equal)
				{
					$likeStr = "{$str}";
				}
				elseif ($start)
				{
					$likeStr = "{$str}%%";
				}
				elseif ($end)
				{
					$likeStr = "%%{$str}";
				}
				elseif ($entry)
				{
					$likeStr = "%%{$str}%%";
				}
				elseif (self::disallowFtsIndex($langId))
				{
					//todo: preg_replace has bug when replaces Thai unicode Non-spacing mark
					$likeStr = "%%" . \preg_replace("/\s+/iu", "%%", $str) . "%%";
				}
				else
				{
					$likeStr = "%%" . \preg_replace("/\W+/iu", "%%", $str) . "%%";
				}
				$likeStr = str_replace('%%%%', '%%', $likeStr);

				if (self::allowICURegularExpression())
				{
					$regStr = \preg_replace("/\s+/iu", '[[:blank:]]+', $str);
				}
				else
				{
					if ($case)
					{
						$regStr = \preg_replace("/\s+/iu", '[[:blank:]]+', $str);
					}
					else
					{
						$regStr = '';
						$regChars = ['?', '*', '|', '[', ']', '(', ')', '-', '+', '.'];
						for ($p = 0, $len = Translate\Text\StringHelper::getLength($str); $p < $len; $p++)
						{
							$c0 = Translate\Text\StringHelper::getSubstring($str, $p, 1);
							if (\in_array($c0, $regChars))
							{
								$regStr .= "\\\\" . $c0;
								continue;
							}
							$c1 = Translate\Text\StringHelper::changeCaseToLower($c0);
							$c2 = Translate\Text\StringHelper::changeCaseToUpper($c0);
							if ($c0 != $c1)
							{
								$regStr .= '(' . $c0 . '|' . $c1 . '){1}';
							}
							elseif ($c0 != $c2)
							{
								$regStr .= '(' . $c0 . '|' . $c2 . '){1}';
							}
							else
							{
								$regStr .= $c0;
							}
						}
						$regStr = \preg_replace("/\s+/iu", '[[:blank:]]+', $regStr);
					}
				}
				$regStr = str_replace('%', '%%', $regStr);

				$regExpStart = '';
				$regExpEnd = '';
				if (\preg_match("/^[[:alnum:]]+/iu", $str))
				{
					if (self::allowICURegularExpression())
					{
						$regExpStart = '\\\\b';
					}
					else
					{
						$regExpStart = '[[:<:]]';
					}
				}
				if (\preg_match("/[[:alnum:]]+$/iu", $str))
				{
					if (self::allowICURegularExpression())
					{
						$regExpEnd = '\\\\b';
					}
					else
					{
						$regExpEnd = '[[:>:]]';
					}
				}

				// Exact word match
				if ($equal)
				{
					$regStr = "[[:blank:]]*{$regExpStart}({$regStr}){$regExpEnd}[[:blank:]]*";
				}
				elseif ($start)
				{
					$regStr = "[[:blank:]]*{$regExpStart}({$regStr}){$regExpEnd}";
				}
				elseif ($end)
				{
					$regStr = "{$regExpStart}({$regStr}){$regExpEnd}[[:blank:]]*";
				}
				elseif ($entry)
				{
					$regStr = "[[:blank:]]*{$regExpStart}({$regStr}){$regExpEnd}[[:blank:]]*";
				}

				// regexp binary mode works not exactly we want using like binary to fix it
				$binarySensitive = $case ? 'BINARY' : '';
				$runtime[] = (new ExpressionField(
					'PHRASE_LIKE',
					"CASE WHEN %s LIKE {$binarySensitive} '{$likeStr}' THEN 1 ELSE 0 END",
					"{$fieldAlias}"
				))->configureValueType(IntegerField::class);
				$phraseSearch["=PHRASE_LIKE"] = 1;

				if (self::allowICURegularExpression())
				{
					// c meaning case-sensitive matching
					// i meaning case-insensitive matching
					$regCaseSensitive = $case ? 'c' : 'i';
					$runtime[] = (new ExpressionField(
						'PHRASE_REGEXP',
						"REGEXP_LIKE(%s, '{$regStr}', '{$regCaseSensitive}')",
						"{$fieldAlias}"
					))->configureValueType(IntegerField::class);
				}
				else
				{
					$runtime[] = (new ExpressionField(
						'PHRASE_REGEXP',
						"CASE WHEN %s REGEXP '{$regStr}' THEN 1 ELSE 0 END",
						"{$fieldAlias}"
					))->configureValueType(IntegerField::class);
				}
				$phraseSearch["=PHRASE_REGEXP"] = 1;
			}

			$filterOut[] = $phraseSearch;
		}
		unset($filterIn['PHRASE_ENTRY'], $filterIn['PHRASE_TEXT'], $filterIn['LANGUAGE_ID']);


		if (!empty($filterIn['FILE_NAME']))
		{
			$filterOut["=%PATH.NAME"] = '%'. $filterIn['FILE_NAME']. '%';
			unset($filterIn['FILE_NAME']);
		}
		if (!empty($filterIn['FOLDER_NAME']))
		{
			$filterOut['=%PATH.PATH'] = '%/'. $filterIn['FOLDER_NAME']. '/%';
			unset($filterIn['FOLDER_NAME']);
		}

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

	/**
	 * Allow use fulltext index for phrase searching.
	 * todo: preg_replace has bug when replaces Thai unicode Non-spacing mark
	 *
	 * @return bool
	 */
	public static function disallowFtsIndex(string $langId): bool
	{
		static $cache;
		if (empty($cache))
		{
			$cache = [];

			$iterator = Main\Localization\LanguageTable::getList([
				'select' => ['ID', 'CODE'],
				'filter' => ['=ACTIVE' => 'Y'],
			]);
			while ($row = $iterator->fetch())
			{
				if (!empty($row['CODE']))
				{
					$cache[mb_strtolower($row['ID'])] = trim(mb_strtolower($row['CODE']));
				}
			}
		}

		return isset($cache[$langId]) && in_array($cache[$langId], self::NON_FTS);
	}

	/**
	 * MySQL8 implements regular expression support using International Components for Unicode (ICU)
	 * against MySQL5 with Henry Spencer's implementation of regular expressions.
	 *
	 * @return bool
	 */
	protected static function allowICURegularExpression(): bool
	{
		static $allowICURE;
		if ($allowICURE === null)
		{
			$majorVersion = \mb_substr(Application::getConnection()->getVersion()[0], 0, 1);
			$allowICURE = (int)$majorVersion >= 8;
		}

		return $allowICURE;
	}

	/**
	 * Prepares searching text to use with fulltext index to help like operator.
	 * todo: preg_replace has bug when replaces Thai unicode Non-spacing mark
	 *
	 * @param string $text
	 * @return string
	 */
	public static function prepareTextForFulltextSearch(string $text): string
	{
		$minLengthFulltextWorld = self::getFullTextMinLength();

		$text = \preg_replace("/\b\w{1,{$minLengthFulltextWorld}}\b/iu", '', $text);

		$stopWorlds = self::getFullTextStopWords();
		foreach ($stopWorlds as $stopWorld)
		{
			$text = \preg_replace("/\b{$stopWorld}\b/iu", '', $text);
		}

		$text = \preg_replace("/^\W+/iu", '', $text);
		$text = \preg_replace("/\W+$/iu", '', $text);
		$text = \preg_replace("/\W+/iu", ' ', $text);

		return $text;
	}

	/**
	 * Detects Innodb engine type.
	 * @return bool
	 */
	protected static function isInnodbEngine(): bool
	{
		static $available;
		if ($available === null)
		{
			$available = false;
			$cache = Cache::createInstance();
			if ($cache->initCache(3600, 'translate::isInnodbEngine'))
			{
				$available = (bool)$cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				try
				{
					$check = Application::getConnection()->query(
						"SHOW TABLE STATUS WHERE Name = 'b_translate_phrase' AND Engine = 'InnoDB'"
					);
					if ($check->fetch())
					{
						$available = true;
					}
				}
				catch (SqlQueryException $exception)
				{}
				$cache->endDataCache((int)$available);
			}
		}

		return $available;
	}


	/**
	 * Extracts Innodb fulltext search stop worlds.
	 * @see https://dev.mysql.com/doc/refman/8.0/en/fulltext-stopwords.html#fulltext-stopwords-stopwords-for-innodb-search-indexes
	 * @return string[]
	 */
	protected static function getFullTextStopWords(): array
	{
		static $worldList;
		if ($worldList === null)
		{
			$minLengthFulltextWorld = self::getFullTextMinLength();
			$worldList = [];
			$cache = Cache::createInstance();
			if ($cache->initCache(3600, 'translate::FullTextStopWords'))
			{
				$worldList = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				try
				{
					if (self::isInnodbEngine())
					{
						$res = Application::getConnection()->query(
							"SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD"
						);
						while ($row = $res->fetch())
						{
							if (mb_strlen($row['value']) > $minLengthFulltextWorld)
							{
								$worldList[] = $row['value'];
							}
						}
					}
				}
				catch (SqlQueryException $exception)
				{}
				$cache->endDataCache($worldList);
			}
		}

		return $worldList;
	}

	/**
	 * The minimum and maximum lengths of words to be indexed are defined by the innodb_ft_min_token_size
	 * and innodb_ft_max_token_size for InnoDB search indexes, and ft_min_word_len and ft_max_word_len for MyISAM ones.
	 * https://dev.mysql.com/doc/refman/8.0/en/fulltext-fine-tuning.html#fulltext-word-length
	 *
	 * @return int
	 */
	public static function getFullTextMinLength(): int
	{
		static $fullTextMinLength;
		if ($fullTextMinLength === null)
		{
			$fullTextMinLength = 4;
			$cache = Cache::createInstance();
			if ($cache->initCache(3600, 'translate::FullTextMinLength'))
			{
				$fullTextMinLength = $cache->getVars();
			}
			elseif ($cache->startDataCache())
			{
				if (self::isInnodbEngine())
				{
					$var = 'innodb_ft_min_token_size';
				}
				else
				{
					$var = 'ft_min_word_len';
				}
				try
				{
					$res = Application::getConnection()->query("SHOW VARIABLES LIKE '{$var}'");
					if ($row = $res->fetch())
					{
						$fullTextMinLength = (int)$row['Value'];
					}
				}
				catch (SqlQueryException $exception)
				{}
				$cache->endDataCache($fullTextMinLength);
			}
		}

		return $fullTextMinLength;
	}
}
