<?php

class CPerfomanceError
{
	public static function Delete($arFilter)
	{
		global $DB;

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields([
			'HIT_ID' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'HIT_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'ERRNO' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRNO',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'ERRFILE' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRFILE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'ERRSTR' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRSTR',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'ERRLINE' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRLINE',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		]);

		$strSql = '
			DELETE FROM b_perf_error
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

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	public static function GetList($arSelect, $arFilter, $arOrder, $bGroup = false)
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
				'HIT_ID',
				'ERRNO',
				'ERRFILE',
				'ERRLINE',
				'ERRSTR',
			];
		}

		if (!is_array($arOrder))
		{
			$arOrder = [];
		}
		if (count($arOrder) < 1)
		{
			$arOrder = [
				'HIT_ID' => 'DESC',
				'ID' => 'DESC',
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
			case 'HIT_ID':
			case 'ERRNO':
			case 'ERRFILE':
			case 'ERRLINE':
			case 'ERRSTR':
				$arSelect[] = $strColumn;
				$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
				break;
			case 'COUNT':
				if ($bGroup)
				{
					$arSelect[] = $strColumn;
					$arQueryOrder[$strColumn] = $strColumn . ' ' . $strDirection;
				}
				break;
			}
		}

		$arQueryGroup = [];
		$arQuerySelect = [];
		foreach ($arSelect as $strColumn)
		{
			$strColumn = mb_strtoupper($strColumn);
			switch ($strColumn)
			{
			case 'ID':
			case 'HIT_ID':
				if (!$bGroup)
				{
					$arQuerySelect[$strColumn] = 'e.' . $strColumn;
				}
				break;
			case 'ERRNO':
			case 'ERRFILE':
			case 'ERRLINE':
			case 'ERRSTR':
				if ($bGroup)
				{
					$arQueryGroup[$strColumn] = 'e.' . $strColumn;
				}
				$arQuerySelect[$strColumn] = 'e.' . $strColumn;
				break;
			case 'COUNT':
				if ($bGroup)
				{
					$arQuerySelect[$strColumn] = 'COUNT(e.ID) ' . $strColumn;
				}
				break;
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields([
			'HIT_ID' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'HIT_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'ERRNO' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRNO',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
			'ERRFILE' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRFILE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'ERRSTR' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRSTR',
				'FIELD_TYPE' => 'string',
				'JOIN' => false,
			],
			'ERRLINE' => [
				'TABLE_ALIAS' => 'e',
				'FIELD_NAME' => 'ERRLINE',
				'FIELD_TYPE' => 'int',
				'JOIN' => false,
			],
		]);

		if (count($arQuerySelect) < 1)
		{
			$arQuerySelect = ['ID' => 'e.ID'];
		}

		$strSql = '
			SELECT
			' . implode(', ', $arQuerySelect) . '
			FROM
				b_perf_error e
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
		if ($bGroup && count($arQueryGroup) > 0)
		{
			$strSql .= '
				GROUP BY
				' . implode(', ', $arQueryGroup) . '
			';
		}
		if (count($arQueryOrder) > 0)
		{
			$strSql .= '
				ORDER BY
				' . implode(', ', $arQueryOrder) . '
			';
		}

		return $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
	}

	public static function Clear()
	{
		global $DB;
		return $DB->Query('TRUNCATE TABLE b_perf_error');
	}
}
