<?php

class CSearchTitle extends CDBResult
{
	var $_arPhrase = [];
	var $_arStemFunc;
	var $minLength = 1;

	function __construct($res = null)
	{
		$this->_arStemFunc = stemming_init(LANGUAGE_ID);
		parent::__construct($res);
	}

	function Search($phrase = '', $nTopCount = 5, $arParams = [], $bNotFilter = false, $order = '')
	{
		$DB = CDatabase::GetModuleConnection('search');
		$this->_arPhrase = stemming_split($phrase, LANGUAGE_ID);
		if (!empty($this->_arPhrase))
		{
			$nTopCount = intval($nTopCount);
			if ($nTopCount <= 0)
			{
				$nTopCount = 5;
			}

			$arId = CSearchFullText::getInstance()->searchTitle($phrase, $this->_arPhrase, $nTopCount, $arParams, $bNotFilter, $order);
			if (!is_array($arId))
			{
				return $this->searchTitle($phrase, $nTopCount, $arParams, $bNotFilter, $order);
			}
			elseif (!empty($arId))
			{
				$strSql = '
					SELECT
						sc.ID
						,sc.MODULE_ID
						,sc.ITEM_ID
						,sc.TITLE
						,sc.PARAM1
						,sc.PARAM2
						,sc.DATE_CHANGE
						,sc.URL as URL
						,scsite.URL as SITE_URL
						,scsite.SITE_ID
						,case when position(\'' . $DB->ForSQL(mb_strtoupper($phrase)) . '\' in upper(sc.TITLE)) > 0 then 1 else 0 end RANK1
						,position(\'' . $DB->ForSQL(mb_strtoupper($phrase)) . '\' in upper(sc.TITLE)) RANK2
					FROM
						b_search_content sc
						INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID
					WHERE
						sc.ID in (' . implode(',', $arId) . ")
						and scsite.SITE_ID = '" . SITE_ID . "'
					ORDER BY " . (
						$order == 'rank' ?
						'RANK1 DESC, RANK2, TITLE' :
						'DATE_CHANGE DESC, RANK1 DESC, RANK2, TITLE' ) . '
				';

				$r = $DB->Query($DB->TopSql($strSql, $nTopCount + 1));
				parent::__construct($r);
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	function setMinWordLength($minLength)
	{
		$minLength = intval($minLength);
		if ($minLength > 0)
		{
			$this->minLength = $minLength;
		}
	}

	function Fetch()
	{
		static $arSite = [];

		$r = parent::Fetch();

		if ($r)
		{
			$site_id = $r['SITE_ID'];
			if (!isset($arSite[$site_id]))
			{
				$rsSite = CSite::GetList('', '', ['ID' => $site_id]);
				$arSite[$site_id] = $rsSite->Fetch();
			}
			$r['DIR'] = $arSite[$site_id]['DIR'];
			$r['SERVER_NAME'] = $arSite[$site_id]['SERVER_NAME'];

			if ($r['SITE_URL'] <> '')
			{
				$r['URL'] = $r['SITE_URL'];
			}

			if (mb_substr($r['URL'], 0, 1) == '=')
			{
				foreach (GetModuleEvents('search', 'OnSearchGetURL', true) as $arEvent)
				{
					$newUrl = ExecuteModuleEventEx($arEvent, [$r]);
					if (isset($newUrl))
					{
						$r['URL'] = $newUrl;
					}
				}
			}

			$r['URL'] = str_replace(
				['#LANG#', '#SITE_DIR#', '#SERVER_NAME#'],
				[$r['DIR'], $r['DIR'], $r['SERVER_NAME']],
				$r['URL']
			);
			$r['URL'] = preg_replace("'(?<!:)/+'s", '/', $r['URL']);

			$r['NAME'] = htmlspecialcharsEx($r['TITLE']);

			$preg_template = '/(^|[^' . $this->_arStemFunc['pcre_letters'] . '])(' . str_replace('/', '\\/', implode('|', array_map('preg_quote', array_keys($this->_arPhrase)))) . ')/iu';
			if (preg_match_all($preg_template, mb_strtoupper($r['NAME']), $arMatches, PREG_OFFSET_CAPTURE))
			{
				$c = count($arMatches[2]);
				for ($j = $c - 1; $j >= 0; $j--)
				{
					$prefix = substr($r['NAME'], 0, $arMatches[2][$j][1]);
					$instr = substr($r['NAME'], $arMatches[2][$j][1], strlen($arMatches[2][$j][0]));
					$suffix = substr($r['NAME'], $arMatches[2][$j][1] + strlen($arMatches[2][$j][0]), strlen($r['NAME']));
					$r['NAME'] = $prefix . '<b>' . $instr . '</b>' . $suffix;
				}
			}
		}

		return $r;
	}

	public static function MakeFilterUrl($prefix, $arFilter)
	{
		if (!is_array($arFilter))
		{
			return '&' . urlencode($prefix) . '=' . urlencode($arFilter);
		}
		else
		{
			$url = '';
			foreach ($arFilter as $key => $value)
			{
				$url .= CSearchTitle::MakeFilterUrl($prefix . '[' . $key . ']', $value);
			}
			return $url;
		}
	}

	function searchTitle($phrase = '', $nTopCount = 5, $arParams = [], $bNotFilter = false, $order = '')
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bOrderByRank = ($order == 'rank');

		$sqlHaving = [];
		$sqlWords = [];
		if (!empty($this->_arPhrase))
		{
			$last = true;
			foreach (array_reverse($this->_arPhrase, true) as $word => $pos)
			{
				if ($last && !preg_match("/[\\n\\r \\t]$/", $phrase))
				{
					$last = false;
					if (mb_strlen($word) >= $this->minLength)
					{
						$s = $sqlWords[] = "ct.WORD like '" . $DB->ForSQL($word) . "%'";
					}
					else
					{
						$s = '';
					}
				}
				else
				{
					$s = $sqlWords[] = "ct.WORD = '" . $DB->ForSQL($word) . "'";
				}

				if ($s)
				{
					$sqlHaving[] = '(sum(case when ' . $s . ' then 1 else 0 end) > 0)';
				}
			}
		}

		if (!empty($sqlWords))
		{
			$bIncSites = false;
			$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
			if ($bNotFilter)
			{
				if (!empty($strSqlWhere))
				{
					$strSqlWhere = 'NOT (' . $strSqlWhere . ')';
				}
				else
				{
					$strSqlWhere = '1=0';
				}
			}

			$strSql = "
				SELECT
					sc.ID
					,sc.MODULE_ID
					,sc.ITEM_ID
					,sc.TITLE
					,sc.PARAM1
					,sc.PARAM2
					,sc.DATE_CHANGE
					,sc.URL as URL
					,scsite.URL as SITE_URL
					,scsite.SITE_ID
					,case when position('" . $DB->ForSQL(mb_strtoupper($phrase)) . "' in upper(sc.TITLE)) > 0 then 1 else 0 end RANK1
					,count(1) RANK2
					,min(ct.POS) RANK3
				FROM
					b_search_content_title ct
					inner join b_search_content sc on sc.ID = ct.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID and ct.SITE_ID = scsite.SITE_ID
				WHERE
					" . CSearch::CheckPermissions('sc.ID') . "
					AND ct.SITE_ID = '" . SITE_ID . "'
					AND (" . implode(' OR ', $sqlWords) . ')
					' . (!empty($strSqlWhere) ? 'AND ' . $strSqlWhere : '') . '
				GROUP BY
					sc.ID, sc.MODULE_ID, sc.ITEM_ID, sc.TITLE, sc.PARAM1, sc.PARAM2, sc.DATE_CHANGE, sc.URL, scsite.URL, scsite.SITE_ID
				' . (count($sqlHaving) > 1 ? 'HAVING ' . implode(' AND ', $sqlHaving) : '') . '
				ORDER BY ' . (
				$bOrderByRank ?
					'RANK1 DESC, RANK2 DESC, RANK3 ASC, TITLE' :
					'DATE_CHANGE DESC, RANK1 DESC, RANK2 DESC, RANK3 ASC, TITLE'
				) . '
				LIMIT ' . ($nTopCount + 1) . '
			';

			$r = $DB->Query($strSql);
			parent::__construct($r);
			return true;
		}
		else
		{
			return false;
		}
	}
}
