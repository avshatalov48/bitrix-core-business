<?php

class CPerfomanceTableList extends CDBResult
{
	protected $dbName = '';
	public static function GetList($bFull = true, $connection = null)
	{
		$connection ??= \Bitrix\Main\Application::getConnection();

		return \Bitrix\Perfmon\BaseDatabase::createFromConnection($connection)->getTables($bFull);
	}
}

class CPerfomanceTable
{
	public $TABLE_NAME = '';
	protected $connection = null;
	protected $database = null;

	public function Init($TABLE_NAME, $connection = null)
	{
		$this->connection = $connection ?? \Bitrix\Main\Application::getConnection();
		$this->database = \Bitrix\Perfmon\BaseDatabase::createFromConnection($this->connection);
		$this->TABLE_NAME = trim($TABLE_NAME, '`');
	}

	public function IsExists($TABLE_NAME = false)
	{
		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');
		if ($TABLE_NAME === '')
		{
			return false;
		}


		return $this->connection->isTableExists($TABLE_NAME);
	}

	public function GetIndexes($TABLE_NAME = false)
	{
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');
		if ($TABLE_NAME === '')
		{
			return [];
		}

		if (!array_key_exists($TABLE_NAME, $cache))
		{
			$cache[$TABLE_NAME] = $this->database->getIndexes($TABLE_NAME);
		}

		return $cache[$TABLE_NAME];
	}

	public function GetUniqueIndexes($TABLE_NAME = false)
	{
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');
		if ($TABLE_NAME === '')
		{
			return [];
		}


		if (!array_key_exists($TABLE_NAME, $cache))
		{
			$cache[$TABLE_NAME] = $this->database->getUniqueIndexes($TABLE_NAME);
		}

		return $cache[$TABLE_NAME];
	}

	public function GetList($arSelect, $arFilter, $arOrder = [], $arNavParams = false)
	{
		$context = \Bitrix\Main\Context::getCurrent();
		$culture = $context->getCulture();
		$sqlHelper = $this->connection->getSqlHelper();

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
					$arQueryOrder[$strColumn] = $sqlHelper->quote('TMP_' . $strColumn) . ' ' . $strDirection;
				}
				else
				{
					$arQueryOrder[$strColumn] = $sqlHelper->quote($strColumn) . ' ' . $strDirection;
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
					$arQuerySelect['TMP_' . $strColumn] = 't.' . $sqlHelper->quote($strColumn) . ' TMP_' . $strColumn;
					$arQuerySelect[$strColumn] = $sqlHelper->formatDate($culture->getFormatDate(), 't.' . $sqlHelper->quote($strColumn)) . ' ' . $sqlHelper->quote($strColumn);
					$arQuerySelect['FULL_' . $strColumn] = $sqlHelper->formatDate($culture->getFormatDatetime(), 't.' . $sqlHelper->quote($strColumn)) . ' FULL_' . $strColumn;
					$arQuerySelect['SHORT_' . $strColumn] = $sqlHelper->formatDate($culture->getFormatDate(), 't.' . $sqlHelper->quote($strColumn)) . ' SHORT_' . $strColumn;
				}
				else
				{
					$arQuerySelect[$strColumn] = 't.' . $sqlHelper->quote($strColumn);
				}
			}
		}

		foreach ($arFields as $FIELD_NAME => $FIELD_TYPE)
		{
			$arFields[$FIELD_NAME] = [
				'TABLE_ALIAS' => 't',
				'FIELD_NAME' => 't.' . $sqlHelper->quote($FIELD_NAME),
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

		$strSql = 'FROM ' /*. $sqlHelper->quote($this->connection->getDatabase()) . '.'*/ . $sqlHelper->quote($this->TABLE_NAME) . " t\n";
		$strQueryWhere = $obQueryWhere->GetQuery($arFilter);
		if ($strQueryWhere)
		{
			$strSql .= 'WHERE ' . $strQueryWhere . "\n";
		}
		$strOrder = $arQueryOrder ? 'ORDER BY ' . implode(', ', $arQueryOrder) : '';

		if (!is_array($arNavParams))
		{
			$dbr = $this->connection->query($strSelect . $strSql . $strOrder);
		}
		elseif (isset($arNavParams['bOnlyCount']) && $arNavParams['bOnlyCount'] === true)
		{
			$res_cnt = $this->connection->query("SELECT count('x') CNT " . $strSql);
			$ar_cnt = $res_cnt->fetch();

			return $ar_cnt['CNT'];
		}
		elseif ($arNavParams['nTopCount'] > 0)
		{
			$strSql = $sqlHelper->getTopSql($strSelect . $strSql . $strOrder, $arNavParams['nTopCount'], $arNavParams['nOffset']);
			$dbr = $this->connection->query($strSql);
		}
		else
		{
			$dbr = $this->connection->query($strSelect . $strSql . $strOrder);
		}

		$dbr->is_filtered = ($strQueryWhere !== '');

		return $dbr;
	}

	public function GetTableFields($TABLE_NAME = false, $bExtended = false)
	{
		static $cache = [];

		if ($TABLE_NAME === false)
		{
			$TABLE_NAME = $this->TABLE_NAME;
		}

		$TABLE_NAME = trim($TABLE_NAME, '`');
		if ($TABLE_NAME === '')
		{
			return false;
		}


		if (!array_key_exists($TABLE_NAME, $cache))
		{
			$cache[$TABLE_NAME] = $this->database->getTableFields($TABLE_NAME);
		}

		if ($bExtended)
		{
			return $cache[$TABLE_NAME][1];
		}
		else
		{
			return $cache[$TABLE_NAME][0];
		}
	}

	public function getCreateIndexDDL($TABLE_NAME, $INDEX_NAME, $INDEX_COLUMNS)
	{
		$tableFields = $this->GetTableFields($TABLE_NAME, true);
		foreach ($INDEX_COLUMNS as $i => $field)
		{
			if ($tableFields[trim($field, '`[]"')]['orm_type'] === 'text')
			{
				$INDEX_COLUMNS[$i] = $field . '(100)';
			}
		}

		return 'CREATE INDEX ' . $INDEX_NAME . ' ON ' . $TABLE_NAME . ' (' . implode(', ', $INDEX_COLUMNS) . ')';
	}
}
