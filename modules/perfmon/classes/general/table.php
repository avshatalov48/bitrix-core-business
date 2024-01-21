<?php

class CAllPerfomanceTable
{
	public $TABLE_NAME = '';

	public function GetList($arSelect, $arFilter, $arOrder = [], $arNavParams = false)
	{
		global $DB;

		$arFields = $this->GetTableFields();

		if (!is_array($arSelect))
		{
			$arSelect = [];
		}
		if (count($arSelect) < 1)
		{
			$arSelect = array_keys($arFields);
		}

		if (!is_array($arOrder))
		{
			$arOrder = [];
		}

		$arQueryOrder = [];
		foreach ($arOrder as $strColumn => $strDirection)
		{
			$strDirection = mb_strtoupper($strDirection) === 'ASC' ? 'ASC' : 'DESC';
			if (array_key_exists($strColumn, $arFields))
			{
				$arSelect[] = $strColumn;
				if ($arFields[$strColumn] === 'datetime' || $arFields[$strColumn] === 'date')
				{
					$arQueryOrder[$strColumn] = static::escapeColumn('TMP_' . $strColumn) . ' ' . $strDirection;
				}
				else
				{
					$arQueryOrder[$strColumn] = static::escapeColumn($strColumn) . ' ' . $strDirection;
				}
			}
		}

		$arQuerySelect = [];
		foreach ($arSelect as $strColumn)
		{
			if (array_key_exists($strColumn, $arFields))
			{
				if ($arFields[$strColumn] === 'datetime' || $arFields[$strColumn] === 'date')
				{
					$arQuerySelect['TMP_' . $strColumn] = 't.' . static::escapeColumn($strColumn) . ' TMP_' . $strColumn;
					$arQuerySelect[$strColumn] = $DB->DateToCharFunction('t.' . static::escapeColumn($strColumn), 'SHORT') . ' ' . static::escapeColumn($strColumn);
					$arQuerySelect['FULL_' . $strColumn] = $DB->DateToCharFunction('t.' . static::escapeColumn($strColumn), 'FULL') . ' FULL_' . $strColumn;
					$arQuerySelect['SHORT_' . $strColumn] = $DB->DateToCharFunction('t.' . static::escapeColumn($strColumn), 'SHORT') . ' SHORT_' . $strColumn;
				}
				else
				{
					$arQuerySelect[$strColumn] = 't.' . static::escapeColumn($strColumn);
				}
			}
		}

		foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
		{
			$arFields[$FIELD_NAME] = [
				'TABLE_ALIAS' => 't',
				'FIELD_NAME' => 't.' . static::escapeColumn($FIELD_NAME),
				'FIELD_TYPE' => $FIELD_TYPE,
				'JOIN' => false,
				//"LEFT_JOIN" => "lt",
			];
		}
		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields($arFields);

		if (count($arQuerySelect) < 1)
		{
			$arQuerySelect = ['*' => 't.*'];
		}

		$strSelect = 'SELECT ' . implode(', ', $arQuerySelect) . "\n";

		$strSql = 'FROM ' . static::escapeTable($this->TABLE_NAME) . " t\n";
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);
		if ($strQueryWhere)
		{
			$strSql .= 'WHERE ' . $strQueryWhere . "\n";
		}
		$strOrder = $arQueryOrder ? 'ORDER BY ' . implode(', ', $arQueryOrder) : '';

		if (!is_array($arNavParams))
		{
			$dbr = $DB->Query($strSelect . $strSql . $strOrder, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		}
		elseif ($arNavParams['nTopCount'] > 0)
		{
			$strSql = $strSelect . $strSql . $strOrder . "\nLIMIT " . intval($arNavParams['nTopCount']);
			if ($arNavParams['nOffset'] > 0)
			{
				$strSql .= ' OFFSET ' . intval($arNavParams['nOffset']);
			}
			$dbr = $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		}
		else
		{
			$res_cnt = $DB->Query("SELECT count('x') CNT " . $strSql);
			$ar_cnt = $res_cnt->Fetch();
			if (isset($arNavParams['bOnlyCount']) && $arNavParams['bOnlyCount'] === true)
			{
				return $ar_cnt['CNT'];
			}

			$dbr = new CDBResult();
			$dbr->NavQuery($strSelect . $strSql . $strOrder, $ar_cnt['CNT'], $arNavParams);
		}

		$dbr->is_filtered = ($strQueryWhere !== '');

		return $dbr;
	}

	public static function escapeColumn($column)
	{
		return $column;
	}

	public static function escapeTable($tableName)
	{
		return $tableName;
	}

	public function GetTableFields($TABLE_NAME = false, $bExtended = false)
	{
		if ($TABLE_NAME && $bExtended)
		{
			return [];
		}

		return [];
	}

	public function getCreateIndexDDL($TABLE_NAME, $INDEX_NAME, $INDEX_COLUMNS)
	{
		return 'CREATE INDEX ' . $INDEX_NAME . ' ON ' . $TABLE_NAME . ' (' . implode(', ', $INDEX_COLUMNS) . ')';
	}
}
