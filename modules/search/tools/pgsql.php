<?php
IncludeModuleLangFile(__FILE__);

class CSearchPgsql extends CSearchFullText
{
	protected $error = '';
	protected $errorno = 0;

	public function connect($connectionString = '')
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		if (!$DB->IndexExists('b_search_content_text', ['SEARCHABLE_CONTENT']))
		{
			$r = $DB->Query('CREATE INDEX tx_b_search_content_text_searchable_content ON b_search_content_text USING GIN (to_tsvector(\'english\', searchable_content))', true);
			if (!$r)
			{
				$APPLICATION->ThrowException(GetMessage('SEARCH_PGSQL_INDEX_CREATE_ERROR', ['#ERRSTR#' => $DB->db_Error]));
				return false;
			}
		}

		return true;
	}

	public function truncate()
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query('TRUNCATE TABLE b_search_content_text');
	}

	public function deleteById($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query('DELETE FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ' . $ID);
	}

	public function replace($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if (array_key_exists('SEARCHABLE_CONTENT', $arFields))
		{
			$text_md5 = md5($arFields['SEARCHABLE_CONTENT']);
			$rsText = $DB->Query('SELECT SEARCH_CONTENT_MD5 FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ' . $ID);
			$arText = $rsText->Fetch();
			if (!$arText || $arText['SEARCH_CONTENT_MD5'] !== $text_md5)
			{
				$DB->Query('
					INSERT INTO b_search_content_text
					(SEARCH_CONTENT_ID, SEARCH_CONTENT_MD5, SEARCHABLE_CONTENT)
					values
					(' . $ID . ", '" . $DB->ForSql($text_md5) . "', '" . $DB->ForSql($arFields['SEARCHABLE_CONTENT']) . "')
					ON CONFLICT (SEARCH_CONTENT_ID)
					DO UPDATE SET SEARCH_CONTENT_MD5 = excluded.SEARCH_CONTENT_MD5
						,SEARCHABLE_CONTENT = excluded.SEARCHABLE_CONTENT
				");
			}
		}
	}

	public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$helper = $DB->getConnection()->getSqlHelper();

		$queryObject = $aParamsEx['QUERY_OBJECT'];
		if ($queryObject->m_parsed_query == '( )' || $queryObject->m_parsed_query == '')
		{
			$this->error = GetMessage('SEARCH_ERROR3');
			$this->errorno = 3;
			return [];
		}

		$strQuery = $this->PrepareQuery($queryObject, $queryObject->m_parsed_query);
		if ($strQuery == '(())' || $queryObject->m_parsed_query == '')
		{
			$this->error = GetMessage('SEARCH_ERROR3');
			$this->errorno = 3;
			return [];
		}

		$strTags = '';
		if (array_key_exists('TAGS', $arParams))
		{
			$this->strTagsText = $arParams['TAGS'];
			$arTags = explode(',', $arParams['TAGS']);
			foreach ($arTags as $i => $strTag)
			{
				$strTag = str_replace(['"', "'"], ' ', $strTag);
				$strTag = trim($strTag);
				$strTag = "('" . preg_replace('/\s+/', "' <-> '", $strTag) . "')";
				$arTags[$i] = $strTag;
			}

			if ($arTags)
			{
				$strTags = '& (' . implode(' & ', $arTags) . ')';
			}
		}

		if (($strQuery == '') && ($strTags <> ''))
		{
			$strQuery = $strTags;
			$bTagsSearch = true;
		}
		else
		{
			$strQuery = preg_replace_callback('/&#(\\d+);/', 'chr', $strQuery);
			$bTagsSearch = false;
		}

		$query = $helper->getMatchFunction('sct.SEARCHABLE_CONTENT', "'" . $DB->ForSql($strQuery) . "'");

		$arSqlWhere = [];
		if (is_array($aParamsEx) && !empty($aParamsEx))
		{
			foreach ($aParamsEx as $aParamEx)
			{
				$strSqlWhere = CSearch::__PrepareFilter($aParamEx, $bIncSites);
				if ($strSqlWhere != '')
				{
					$arSqlWhere[] = $strSqlWhere;
				}
			}
		}
		if (!empty($arSqlWhere))
		{
			$arSqlWhere = [
				"\n\t\t\t\t(" . implode(")\n\t\t\t\t\tOR(", $arSqlWhere) . "\n\t\t\t\t)",
			];
		}

		$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
		if ($strSqlWhere != '')
		{
			array_unshift($arSqlWhere, $strSqlWhere);
		}

		$strSqlOrder = $this->__PrepareSort($aSort, 'sc.', $bTagsCloud);

		if (!empty($arSqlWhere))
		{
			$strSqlWhere = "\n\t\t\t\tAND (\n\t\t\t\t\t(" . implode(")\n\t\t\t\t\tAND(", $arSqlWhere) . ")\n\t\t\t\t)";
		}

		if ($bTagsCloud)
		{
			$strSql = '
				SELECT
					stags.NAME
					,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
					,MAX(sc.DATE_CHANGE) DC_TMP
					,' . $DB->DateToCharFunction('MAX(sc.DATE_CHANGE)') . ' as FULL_DATE_CHANGE
					,' . $DB->DateToCharFunction('MAX(sc.DATE_CHANGE)', 'SHORT') . ' as DATE_CHANGE
				FROM b_search_tags stags
					INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
					INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID
					INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				WHERE
					' . CSearch::CheckPermissions('sc.ID') . '
					AND (' . $query . ')
					AND stags.SITE_ID = scsite.SITE_ID
					' . $strSqlWhere . '
				GROUP BY
					stags.NAME
				' . $strSqlOrder . '
			';
		}
		else
		{
			$strSql = '
				SELECT
					sct.SEARCH_CONTENT_ID
					,scsite.SITE_ID
					,' . $query . ' RANK
				FROM
					b_search_content_text sct
					INNER JOIN b_search_content sc ON sc.ID = sct.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID
				WHERE
					' . CSearch::CheckPermissions('sc.ID') . '
					AND (' . $query . ')
					' . $strSqlWhere . '
			' . $strSqlOrder;
		}

		$r = $DB->Query($strSql);
		$result = [];
		while ($a = $r->Fetch())
		{
			$result[] = $a;
		}

		return $result;
	}

	function searchTitle($phrase = '', $arPhrase = [], $nTopCount = 5, $arParams = [], $bNotFilter = false, $order = '')
	{
		return false;
	}

	public function getErrorText()
	{
		return $this->error;
	}

	public function getErrorNumber()
	{
		return $this->errorno;
	}

	function getRowFormatter()
	{
		return new CSearchPgSqlFormatter();
	}

	function __PrepareSort($aSort = [], $strSearchContentAlias = 'sc.', $bTagsCloud = false)
	{
		$arOrder = [];
		if (!is_array($aSort))
		{
			$aSort = [$aSort => 'ASC'];
		}

		if ($bTagsCloud)
		{
			foreach ($aSort as $key => $ord)
			{
				$ord = mb_strtoupper($ord) <> 'ASC' ? 'DESC' : 'ASC';
				$key = mb_strtoupper($key);
				switch ($key)
				{
				case 'DATE_CHANGE':
					$arOrder[] = 'DC_TMP ' . $ord;
					break;
				case 'NAME':
				case 'CNT':
					$arOrder[] = $key . ' ' . $ord;
					break;
				}
			}
			if (!$arOrder)
			{
				$arOrder[] = 'NAME ASC';
			}
		}
		else
		{
			$this->flagsUseRatingSort = 0;
			foreach ($aSort as $key => $ord)
			{
				$ord = mb_strtoupper($ord) <> 'ASC' ? 'DESC' : 'ASC';
				$key = mb_strtoupper($key);
				switch ($key)
				{
				case 'DATE_CHANGE':
					if (!($this->flagsUseRatingSort & 0x01))
					{
						$this->flagsUseRatingSort = 0x02;
					}
					$arOrder[] = $strSearchContentAlias . $key . ' ' . $ord;
					break;
				case 'RANK':
					if (!($this->flagsUseRatingSort & 0x02))
					{
						$this->flagsUseRatingSort = 0x01;
					}
					$arOrder[] = 'RANK ' . $ord;
					break;
				case 'TITLE_RANK':
					$arOrder[] = 'RANK ' . $ord;
					break;
				case 'CUSTOM_RANK':
					$arOrder[] = $strSearchContentAlias . $key . ' ' . $ord;
					break;
				case 'ID':
				case 'MODULE_ID':
				case 'ITEM_ID':
				case 'TITLE':
				case 'PARAM1':
				case 'PARAM2':
				case 'UPD':
				case 'DATE_FROM':
				case 'DATE_TO':
				case 'URL':
					if (!($this->flagsUseRatingSort & 0x01))
					{
						$this->flagsUseRatingSort = 0x02;
					}
					$arOrder[] = $strSearchContentAlias . $key . ' ' . $ord;
					break;
				}
			}

			if (!$arOrder)
			{
				$arOrder[] = 'CUSTOM_RANK DESC';
				$arOrder[] = 'RANK DESC';
				$arOrder[] = $strSearchContentAlias . 'DATE_CHANGE DESC';
				$this->flagsUseRatingSort = 0x01;
			}
		}

		return ' ORDER BY ' . implode(', ', $arOrder);
	}

	function PrepareQuery($queryObject, $q)
	{
		$state = 0;
		$qu = [];
		$n = 0;
		$this->error = '';
		$this->errorno = 0;

		foreach (preg_split('/ +/', $q) as $t)
		{
			if ($state === 0)
			{
				if (($t === '||') || ($t === '&&') || ($t === ')'))
				{
					$this->error = GetMessage('SEARCH_ERROR2') . ' ' . $t;
					$this->errorno = 2;
					break;
				}
				elseif ($t === '!')
				{
					$qu[] = ' !';
				}
				elseif ($t === '(')
				{
					$n++;
					$qu[] = '(';
				}
				else
				{
					$state = 1;
					if (isset($queryObject->m_kav[$t]))
					{
						$kav = str_replace(['"', "'"], ' ', $queryObject->m_kav[$t]);
						$kav = trim($kav);
						$kav = "('" . preg_replace('/\s+/', "' <-> '", $kav) . "')";
						$t = $kav;
					}
					else
					{
						if (preg_match('/[^\w\d]/u', $t))
						{
							$t = preg_replace('/[^\w\d]+$/u', '', $t);
							$t = preg_replace('/^[^\w\d]+/u', '', $t);
							if ($t === '')
							{
								continue;
							}
							$t = '(' . preg_replace('/[^\w\d]+/u', ' & ', $t) . ($queryObject->bStemming ? ':*' : '') . ')';
						}
						elseif ($queryObject->bStemming)
						{
							$t .= ':*';
						}
					}
					$qu[] = $t;
				}
			}
			else
			{
				if (($t === '||') || ($t === '&&'))
				{
					$state = 0;
					if ($t === '||')
					{
						$qu[] = ' | ';
					}
					else
					{
						$qu[] = ' & ';
					}
				}
				elseif ($t === ')')
				{
					$n--;
					$qu[] = ')';
				}
				else
				{
					$this->error = GetMessage('SEARCH_ERROR2') . ' ' . $t;
					$this->errorno = 2;
					break;
				}
			}
		}

		if (($this->error === '') && ($n !== 0))
		{
			$this->error = GetMessage('SEARCH_ERROR1');
			$this->errorno = 1;
		}

		if ($this->error != '')
		{
			return '';
		}

		return implode($qu);
	}
}

class CSearchPgSqlFormatter extends CSearchFormatter
{
	function format($r)
	{
		if ($r)
		{
			if (array_key_exists('CNT', $r))
			{
				return $r;
			}
			elseif (array_key_exists('SEARCH_CONTENT_ID', $r))
			{
				return $this->formatRow($r);
			}
		}
	}

	function formatRow($r)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$rs = $DB->Query('
			select
				sc.ID
				,sc.MODULE_ID
				,sc.ITEM_ID
				,sc.TITLE
				,sc.TAGS
				,sc.BODY
				,sc.PARAM1
				,sc.PARAM2
				,sc.UPD
				,sc.DATE_FROM
				,sc.DATE_TO
				,sc.URL
				,sc.CUSTOM_RANK
				,' . $DB->DateToCharFunction('sc.DATE_CHANGE') . ' as FULL_DATE_CHANGE
				,' . $DB->DateToCharFunction('sc.DATE_CHANGE', 'SHORT') . ' as DATE_CHANGE
				,scsite.SITE_ID
				,scsite.URL SITE_URL
				,sc.USER_ID
			from b_search_content sc
			INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
			where ID = ' . $r['SEARCH_CONTENT_ID'] . "
			and scsite.SITE_ID = '" . $r['SITE_ID'] . "'
		");
		$r = $rs->Fetch();

		return $r;
	}
}
