<?php

namespace Bitrix\Translate\Index;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;


class Aggregate
{
	/** @var Main\ORM\Entity[] */
	private static $entities = [];


	/**
	 * @param array $params Array of parameters:
	 * $params = [
	 *    'PARENT_ID' => (int) Top parent node Id.
	 *    'CURRENT_LANG' => (string) Current language Id.
	 *    'LANGUAGES' => (string[]) Languages Ids.
	 *    'PATH_LIST' => (string) Path list to filter.
	 * ].
	 *
	 * @return Main\ORM\Query\Query
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function buildAggregateQuery(array $params)
	{
		if (empty($params['GROUP_BY']))
		{
			throw new Main\ArgumentException('Parameter GROUP_BY has not defined');
		}

		$query = self::buildQuery($params);

		$query->addSelect($params['GROUP_BY']);
		$query->addGroup($params['GROUP_BY']);

		if (empty($params['LANGUAGES']))
		{
			$languages = Translate\Config::getEnabledLanguages();
		}
		else
		{
			$languages = $params['LANGUAGES'];
		}

		$languageUpperKeys = array_combine($languages, array_map('mb_strtoupper', $languages));

		foreach ($languageUpperKeys as $langId => $alias)
		{
			// phrase count
			$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_CNT", "SUM({$alias}_CNT)"));

			// file count
			$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_FILE_CNT", "SUM({$alias}_FILE_CNT)"));
			// file excess
			$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_FILE_EXCESS", "SUM({$alias}_FILE_EXCESS)"));
			// phrase excess
			$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_EXCESS", "SUM({$alias}_EXCESS)"));

			if ($langId != $params['CURRENT_LANG'])
			{
				// file deficiency
				$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_FILE_DEFICIENCY", "SUM({$alias}_FILE_DEFICIENCY)"));
				// phrase deficiency
				$query->addSelect(new Main\ORM\Fields\ExpressionField("{$alias}_DEFICIENCY", "SUM({$alias}_DEFICIENCY)"));
			}
		}

		return $query;
	}

	/**
	 * @param array $params Array of parameters:
	 * $params = [
	 *    'PARENT_ID' => (int) Top parent node Id.
	 *    'CURRENT_LANG' => (string) Current language Id.
	 *    'LANGUAGES' => (string[]) Languages Ids.
	 *    'PATH_LIST' => (string) Path list to filter.
	 * ].
	 *
	 * @return Main\ORM\Query\Query
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function buildQuery(array $params)
	{
		if (empty($params['PARENT_ID']))
		{
			throw new Main\ArgumentException('Parameter PARENT_ID has not defined');
		}
		$topIndexPathId = (int)$params['PARENT_ID'];

		if (empty($params['CURRENT_LANG']))
		{
			throw new Main\ArgumentException('Parameter CURRENT_LANG has not defined');
		}
		$currentLanguage = $params['CURRENT_LANG'];

		$className = "Aggregate_{$topIndexPathId}_{$currentLanguage}";

		if (empty($params['LANGUAGES']))
		{
			$languages = Translate\Config::getEnabledLanguages();
		}
		else
		{
			$languages = $params['LANGUAGES'];
		}
		$className .= "_". implode('', $languages);

		if (!empty($params['PATH_LIST']))
		{
			$className .= "_". md5(implode('', $params['PATH_LIST']));
		}

		$languageUpperKeys = array_combine($languages, array_map('mb_strtoupper', $languages));

		if (!isset(self::$entities[$className]))
		{
			$entity = Index\Internals\PathTreeTable::getEntity();
			$query = new Main\ORM\Query\Query($entity);

			$query->registerRuntimeField(new Main\ORM\Fields\Relations\Reference(
				'FOLDER_NODE',
				Translate\Index\Internals\PathIndexTable::class,
				Main\ORM\Query\Join::on('ref.ID', '=', 'this.PATH_ID')->where('ref.IS_DIR', '=', 'Y'),
				array('join_type' => 'INNER')
			));


			$query->registerRuntimeField(new Main\ORM\Fields\Relations\Reference(
				'FILE_LIST',
				Translate\Index\Internals\PathTreeTable::class,
				Main\ORM\Query\Join::on('ref.PARENT_ID', '=', 'this.PATH_ID'),
				array('join_type' => 'INNER')
			));
			$query->registerRuntimeField(new Main\ORM\Fields\Relations\Reference(
				'FILE_NODE',
				Translate\Index\Internals\PathIndexTable::class,
				Main\ORM\Query\Join::on('ref.ID', '=', 'this.FILE_LIST.PATH_ID')->where('ref.IS_DIR', '=', 'N'),
				array('join_type' => 'INNER')
			));

			//$query->addSelect('PARENT_ID');
			$query->addSelect('FOLDER_NODE.PATH', 'PARENT_PATH');
			$query->addSelect('FILE_NODE.OBLIGATORY_LANGS', 'OBLIGATORY_LANGS');
			$query->addSelect('FILE_NODE.PATH', 'FILE_PATH');


			foreach ($languageUpperKeys as $langId => $alias)
			{
				$tblAlias = "File{$alias}";

				$query->registerRuntimeField(new Main\ORM\Fields\Relations\Reference(
					$tblAlias,
					Translate\Index\Internals\FileIndexTable::class,
					Main\ORM\Query\Join::on('ref.PATH_ID', '=', 'this.FILE_NODE.ID')->where('ref.LANG_ID', '=', $langId),
					array('join_type' => 'LEFT')
				));

				$query->addSelect(new Main\ORM\Fields\ExpressionField(
					"{$alias}_OBLI",
					"@{$alias}_OBLI := case when (INSTR(IFNULL(%s, '{$langId}'), '{$langId}') > 0) then 1 else 0 end",
					'FILE_NODE.OBLIGATORY_LANGS'
				));


				if ($langId == $currentLanguage)
				{
					// phrase count
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_CNT",
						'@ETHALON_CNT := IFNULL(%s, 0)',
						"{$tblAlias}.PHRASE_COUNT"
					));
					// file count
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_FILE_CNT",
						"case when (@ETHALON_CNT > 0) then 1 else 0 end"
					));
					// file excess
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_FILE_EXCESS",
						"case when (@ETHALON_CNT > 0) and (@{$alias}_OBLI = 0) then 1 else 0 end"
					));
					// phrase excess
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_EXCESS",
						"case when (@ETHALON_CNT > 0) and (@{$alias}_OBLI = 0) then @ETHALON_CNT else 0 end"
					));
				}
				else
				{
					// phrase count
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_CNT",
						"@{$alias}_CNT := IFNULL(%s, 0)",
						["{$tblAlias}.PHRASE_COUNT"]
					));
					// phrase count diff from ethalon
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_DIFF",
						"@{$alias}_DIFF := @{$alias}_CNT - @ETHALON_CNT"
					));
					// file count
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_FILE_CNT",
						"case when (@{$alias}_CNT > 0) then 1 else 0 end"
					));
					// file deficiency
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_FILE_DEFICIENCY",
						"case when (@{$alias}_CNT = 0) and (@ETHALON_CNT > 0) AND (@{$alias}_OBLI = 1) then 1 else 0 end"
					));
					// file excess
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_FILE_EXCESS",
						"case when (@{$alias}_CNT > 0) and (@ETHALON_CNT = 0) then 1 ".
						"when (@{$alias}_CNT > 0) and (@ETHALON_CNT > 0) AND (@{$alias}_OBLI = 0) then 1 ".
						"else 0 end"
					));
					// phrase excess
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_EXCESS",
						"case when (@{$alias}_CNT > 0) and (@ETHALON_CNT = 0) then @{$alias}_CNT ".
						"when (@{$alias}_CNT > 0) and (@ETHALON_CNT > 0) AND (@{$alias}_DIFF > 0) and (@{$alias}_OBLI = 1) then @{$alias}_DIFF ".
						"when (@{$alias}_CNT > 0) and (@ETHALON_CNT > 0) and (@{$alias}_OBLI = 0) then @{$alias}_CNT ".
						"else 0 end"
					));
					// phrase deficiency
					$query->addSelect(new Main\ORM\Fields\ExpressionField(
						"{$alias}_DEFICIENCY",
						"case when (@{$alias}_CNT = 0) and (@ETHALON_CNT > 0) AND (@{$alias}_OBLI = 1) then @ETHALON_CNT ".
						"when (@{$alias}_CNT > 0) and (@ETHALON_CNT > 0) AND (@{$alias}_DIFF < 0) and (@{$alias}_OBLI = 1) then - @{$alias}_DIFF ".
						"else 0 end"
					));
				}
			}
			unset($langId, $langUpper, $alias, $tblAlias);

			$query->addFilter('=PARENT_ID', $topIndexPathId);
			$query->addFilter('=DEPTH_LEVEL', '1');

			if (!empty($params['PATH_LIST']))
			{
				$query->addFilter('=FOLDER_NODE.PATH', $params['PATH_LIST']);
			}

			$fields = [
				'PARENT_ID' => ['data_type' => 'integer'],
				'PARENT_PATH' => ['data_type' => 'string'],
				'FILE_PATH' => ['data_type' => 'string'],
				'OBLIGATORY_LANGS' => ['data_type' => 'string'],
			];

			foreach ($languageUpperKeys as $langId => $alias)
			{
				$query->addSelect("{$alias}_CNT");
				$query->addSelect("{$alias}_FILE_CNT");
				$query->addSelect("{$alias}_FILE_EXCESS");
				$query->addSelect("{$alias}_EXCESS");

				$fields["{$alias}_CNT"] = ['data_type' => 'integer'];
				$fields["{$alias}_FILE_CNT"] = ['data_type' => 'integer'];
				$fields["{$alias}_FILE_EXCESS"] = ['data_type' => 'integer'];
				$fields["{$alias}_EXCESS"] = ['data_type' => 'integer'];

				if ($langId != $currentLanguage)
				{
					$query->addSelect("{$alias}_DIFF");
					$query->addSelect("{$alias}_FILE_DEFICIENCY");
					$query->addSelect("{$alias}_DEFICIENCY");

					$fields["{$alias}_DIFF"] = ['data_type' => 'integer'];
					$fields["{$alias}_FILE_DEFICIENCY"] = ['data_type' => 'integer'];
					$fields["{$alias}_DEFICIENCY"] = ['data_type' => 'integer'];
				}
			}

			self::$entities[$className] = Main\ORM\Entity::compileEntity(
				$className,
				$fields,
				[
					'table_name' => '('.$query->getQuery().')',
					'namespace' => __NAMESPACE__,
				]
			);
		}

		return new Main\ORM\Query\Query(self::$entities[$className]);
	}
}