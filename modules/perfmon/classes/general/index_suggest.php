<?php

class CPerfomanceIndexSuggest
{
	public static function GetList($arSelect, $arFilter, $arOrder)
	{
		global $DB;

		if (!is_array($arSelect))
		{
			$arSelect = [];
		}
		if (count($arSelect) < 1)
		{
			$arSelect = [
				'ID',
			];
		}

		if (!is_array($arOrder))
		{
			$arOrder = [];
		}
		if (count($arOrder) < 1)
		{
			$arOrder = [
				'TABLE_NAME' => 'ASC',
			];
		}

		$arQueryOrder = [];
		foreach ($arOrder as $strColumn => $strDirection)
		{
			$strColumn = mb_strtoupper($strColumn);
			$strDirection = mb_strtoupper($strDirection) === 'ASC' ? 'ASC' : 'DESC';
			switch ($strColumn)
			{
			case 'ID':
			case 'TABLE_NAME':
			case 'SQL_COUNT':
			case 'SQL_TIME':
				$arSelect[] = $strColumn;
				$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
				break;
			}
		}

		$bJoin = false;
		$arQuerySelect = [];
		foreach ($arSelect as $strColumn)
		{
			$strColumn = mb_strtoupper($strColumn);
			switch ($strColumn)
			{
			case 'ID':
			case 'TABLE_NAME':
			case 'TABLE_ALIAS':
			case 'COLUMN_NAMES':
			case 'SQL_MD5':
			case 'SQL_TEXT':
			case 'SQL_COUNT':
			case 'SQL_TIME':
			case 'SQL_EXPLAIN':
				$arQuerySelect[$strColumn] = 's.' . $strColumn;
				break;
			case 'BANNED':
				$arQuerySelect[$strColumn] = 'c.' . $strColumn;
				$bJoin = true;
				break;
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields([
			'ID' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 'ID',
				'FIELD_TYPE' => 'int', //int, double, file, enum, int, string, date, datetime
				'JOIN' => false,
				//"LEFT_JOIN" => "lt",
			],
			'SQL_MD5' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.SQL_MD5',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'TABLE_NAME' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.TABLE_NAME',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'COLUMN_NAMES' => [
				'TABLE_ALIAS' => 's',
				'FIELD_NAME' => 's.COLUMN_NAMES',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'BANNED' => [
				'TABLE_ALIAS' => 'c1',
				'FIELD_NAME' => 'c1.BANNED',
				'FIELD_TYPE' => 'string',
				'JOIN' => 'LEFT JOIN b_perf_index_complete c1 on c1.TABLE_NAME = s.TABLE_NAME and c1.COLUMN_NAMES = s.COLUMN_NAMES',
			],
		]);

		if (count($arQuerySelect) < 1)
		{
			$arQuerySelect = ['ID' => 's.ID'];
		}

		if (!is_array($arFilter))
		{
			$arFilter = [];
		}
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);

		$strSql = '
			SELECT ' . implode(', ', $arQuerySelect) . '
			FROM b_perf_index_suggest s
			' . $obQueryWhere->GetJoins() . '
			' . ($bJoin ? 'LEFT JOIN b_perf_index_complete c on c.TABLE_NAME = s.TABLE_NAME and c.COLUMN_NAMES = s.COLUMN_NAMES' : '') . '
			' . ($strQueryWhere ? 'WHERE ' . $strQueryWhere : '') . '
			' . (count($arQueryOrder) ? 'ORDER BY ' . implode(', ', $arQueryOrder) : '') . '
		';
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function Add($arFields)
	{
		global $DB;
		$ID = $DB->Add('b_perf_index_suggest', $arFields);
		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$DB->Query('DELETE FROM b_perf_index_suggest_sql WHERE SUGGEST_ID = ' . $ID);
		$DB->Query('DELETE FROM b_perf_index_suggest WHERE ID = ' . $ID);
	}

	public static function UpdateStat($sql_md5, $count, $query_time, $sql_id)
	{
		global $DB;
		$res = $DB->Query("
			INSERT INTO b_perf_index_suggest_sql (
				SUGGEST_ID, SQL_ID
			) SELECT iss.ID,s.ID
			FROM b_perf_index_suggest iss
			,b_perf_sql s
			WHERE iss.SQL_MD5 = '" . $DB->ForSql($sql_md5) . "'
			AND s.ID = " . intval($sql_id) . '
		');
		if (is_object($res))
		{
			$DB->Query('
				UPDATE b_perf_index_suggest
				SET SQL_COUNT = SQL_COUNT + ' . intval($count) . ',
				SQL_TIME = SQL_TIME + ' . floatval($query_time) . "
				WHERE SQL_MD5 = '" . $DB->ForSql($sql_md5) . "'
			");
		}
	}

	public static function Clear()
	{
		global $DB;
		$DB->Query('TRUNCATE TABLE b_perf_tab_stat');
		$DB->Query('TRUNCATE TABLE b_perf_tab_column_stat');
		$DB->Query('TRUNCATE TABLE b_perf_index_suggest');
		$DB->Query('TRUNCATE TABLE b_perf_index_suggest_sql');
	}
}
