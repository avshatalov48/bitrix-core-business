<?php

class CPerfomanceIndexComplete
{
	public static function GetList($arFilter = [], $arOrder = [])
	{
		global $DB;

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
				$arSelect[] = $strColumn;
				$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
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
		]);

		if (!is_array($arFilter))
		{
			$arFilter = [];
		}
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);

		$strSql = '
			SELECT *
			FROM b_perf_index_complete s
			' . ($strQueryWhere ? 'WHERE ' . $strQueryWhere : '') . '
			' . (count($arQueryOrder) ? 'ORDER BY ' . implode(', ', $arQueryOrder) : '') . '
		';
		$res = $DB->Query($strSql);

		return $res;
	}

	public static function Add($arFields)
	{
		global $DB;
		$ID = $DB->Add('b_perf_index_complete', $arFields);
		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		$DB->Query('DELETE FROM b_perf_index_complete WHERE ID = ' . $ID);
	}

	public static function DeleteByTableName($table, $columns)
	{
		global $DB;
		$DB->Query("
			delete
			from b_perf_index_complete
			where TABLE_NAME = '" . $DB->ForSql($table) . "'
			AND COLUMN_NAMES = '" . $DB->ForSql($columns) . "'
		");
	}

	public static function IsBanned($table, $columns)
	{
		global $DB;
		$rs = $DB->Query("
			select *
			from b_perf_index_complete
			where TABLE_NAME = '" . $DB->ForSql($table) . "'
			AND COLUMN_NAMES = '" . $DB->ForSql($columns) . "'
			AND BANNED = 'Y'
		");
		return is_array($rs->Fetch());
	}
}
