<?php

class CPerfomanceHistory
{
	public static function GetList($arOrder, $arFilter = [])
	{
		global $DB;

		if (!is_array($arOrder))
		{
			$arOrder = [];
		}
		if (count($arOrder) < 1)
		{
			$arOrder = [
				'ID' => 'DESC',
			];
		}

		$arQueryOrder = [];
		foreach ($arOrder as $strColumn => $strDirection)
		{
			$strColumn = mb_strtoupper($strColumn);
			$strDirection = mb_strtoupper($strDirection) === 'ASC' ? 'ASC' : 'DESC';
			if ($strColumn === 'ID')
			{
				$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
			}
		}

		static $arWhereFields = [
			'ID' => [
				'TABLE_ALIAS' => 'h',
				'FIELD_NAME' => 'ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		];

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields($arWhereFields);

		$strSql = '
			SELECT
				h.*
				,' . $DB->DateToCharFunction('h.TIMESTAMP_X') . ' TIMESTAMP_X
			FROM
				b_perf_history h
		';
		if (!is_array($arFilter))
		{
			$arFilter = [];
		}
		if ($strQueryWhere = $obQueryWhere->GetQuery($arFilter))
		{
			$strSql .= '
				WHERE
				' . $strQueryWhere . '
			';
		}
		if (count($arQueryOrder) > 0)
		{
			$strSql .= '
				ORDER BY
				' . implode(', ', $arQueryOrder) . '
			';
		}

		return $DB->Query($strSql);
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		return $DB->Query('DELETE FROM b_perf_history WHERE ID = ' . $ID);
	}

	public static function Add($arFields)
	{
		global $DB;

		if ($arFields['TOTAL_MARK'] > 0)
		{
			$arFields['ACCELERATOR_ENABLED'] = $arFields['ACCELERATOR_ENABLED'] === 'Y' ? 'Y' : 'N';
			$arFields['~TIMESTAMP_X'] = $DB->CurrentTimeFunction();
			return $DB->Add('b_perf_history', $arFields);
		}
		else
		{
			return false;
		}
	}
}
