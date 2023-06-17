<?php
/*
 * SQL Helper
 */
class CSqlUtil
{
	public static function GetCount($tableName, $tableAlias, &$arFields, &$arFilter)
	{
		$tableName = strval($tableName);
		if($tableName === '')
		{
			return false;
		}

		global $DB;

		$sql = "SELECT COUNT(*) AS QTY FROM {$tableName}";

		if(is_array($arFilter) && !empty($arFilter))
		{
			if(!is_array($arFields))
			{
				return false;
			}

			$arJoins = array();
			$condition = self::PrepareWhere($arFields, $arFilter, $arJoins);
			if($condition !== '')
			{
				$tableAlias = strval($tableAlias);
				if($tableAlias !== '')
				{
					//ORA-00933 overwise
					$sql .= $isOracle ? " {$tableAlias}" : " AS {$tableAlias}";
				}

				$sql .= " WHERE {$condition}";
			}
		}

		$dbResult = $DB->Query($sql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
		$arResult = $dbResult ? $dbResult->Fetch() : null;
		return $arResult !== null && isset($arResult['QTY']) ? intval($arResult['QTY']) : 0;
	}

	public static function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (mb_substr($key, 0, 1) == "+")
		{
			$key = mb_substr($key, 1);
			$strOrNull = "Y";
		}

		if (mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$strOperation = ">=";
		}
		elseif (mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$strOperation = ">";
		}
		elseif (mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "<=";
		}
		elseif (mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$strOperation = "<";
		}
		elseif (mb_substr($key, 0, 1) == "@")
		{
			$key = mb_substr($key, 1);
			$strOperation = "IN";
		}
		elseif (mb_substr($key, 0, 2) == "=%")
		{
			$key = mb_substr($key, 2);
			$strOperation = "RLIKE";
		}
		elseif (mb_substr($key, 0, 2) == "%=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "LLIKE";
		}
		elseif (mb_substr($key, 0, 1) == "%")
		{
			$key = mb_substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (mb_substr($key, 0, 1) == "?")
		{
			$key = mb_substr($key, 1);
			$strOperation = "QUERY";
		}
		elseif (mb_substr($key, 0, 2) == "*=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "FTI";
		}
		elseif (mb_substr($key, 0, 2) == "*%")
		{
			$key = mb_substr($key, 2);
			$strOperation = "FTL";
		}
		elseif (mb_substr($key, 0, 1) == "*")
		{
			$key = mb_substr($key, 1);
			$strOperation = "FT";
		}
		elseif (mb_substr($key, 0, 1) == "=")
		{
			$key = mb_substr($key, 1);
			$strOperation = "=";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	private static function AddToSelect(&$fieldKey, &$arField, &$arOrder, &$strSqlSelect)
	{
		global $DB;

		if ($strSqlSelect <> '')
			$strSqlSelect .= ", ";

		// ORACLE AND MSSQL require datetime/date field in select list if it present in order list
		if ($arField["TYPE"] == "datetime")
		{
			$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"], "FULL")." as ".$fieldKey;
		}
		elseif ($arField["TYPE"] == "date")
		{
			$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"], "SHORT")." as ".$fieldKey;
		}
		else
			$strSqlSelect .= $arField["FIELD"]." as ".$fieldKey;
	}

	private static function AddToFrom(&$arField, &$arJoined, &$strSqlFrom)
	{
		if (isset($arField["FROM"])
			&& $arField["FROM"] <> ''
			&& !in_array($arField["FROM"], $arJoined))
		{
			if ($strSqlFrom <> '')
				$strSqlFrom .= " ";
			$strSqlFrom .= $arField["FROM"];
			$arJoined[] = $arField["FROM"];
		}
	}

	private static function PrepareDefaultFields(&$arFields, &$arOrder, &$arJoined, &$strSqlSelect, &$strSqlFrom)
	{
		$arFieldsKeys = array_keys($arFields);
		$qty = count($arFieldsKeys);
		for ($i = 0; $i < $qty; $i++)
		{
			if (isset($arFields[$arFieldsKeys[$i]]["WHERE_ONLY"])
				&& $arFields[$arFieldsKeys[$i]]["WHERE_ONLY"] == "Y")
			{
				continue;
			}

			if (isset($arFields[$arFieldsKeys[$i]]["DEFAULT"])
				&& $arFields[$arFieldsKeys[$i]]["DEFAULT"] == "N")
			{
				continue;
			}

			self::AddToSelect($arFieldsKeys[$i], $arFields[$arFieldsKeys[$i]], $arOrder, $strSqlSelect);
			self::AddToFrom($arFields[$arFieldsKeys[$i]], $arJoined, $strSqlFrom);
		}
	}

	public static function PrepareSql(&$arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $arOptions = array())
	{
		global $DB;

		if(!is_array($arOptions))
		{
			$arOptions = array();
		}

		$strSqlSelect = '';
		$strSqlFrom = '';
		$strSqlFromWhere = '';
		$strSqlGroupBy = '';

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = array();

		// GROUP BY -->
		if (is_array($arGroupBy) && !empty($arGroupBy))
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = mb_strtoupper($val);
				$key = mb_strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if ($strSqlGroupBy <> '')
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& $arFields[$val]["FROM"] <> ''
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if ($strSqlFrom <> '')
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && empty($arGroupBy))
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && $arSelectFields <> '' && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| empty($arSelectFields))
			{
				self::PrepareDefaultFields($arFields, $arOrder, $arAlreadyJoined, $strSqlSelect, $strSqlFrom);
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					if($val === '*')
					{
						self::PrepareDefaultFields($arFields, $arOrder, $arAlreadyJoined, $strSqlSelect, $strSqlFrom);
					}

					$val = mb_strtoupper($val);
					$key = mb_strtoupper($key);

					if (!array_key_exists($val, $arFields))
					{
						continue;
					}

					if (in_array($key, $arGroupByFunct))
					{
						if ($strSqlSelect <> '')
							$strSqlSelect .= ", ";

						$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
					}
					else
					{
						self::AddToSelect($val, $arFields[$val], $arOrder, $strSqlSelect);
					}
					self::AddToFrom($arFields[$val], $arAlreadyJoined, $strSqlFrom);
				}
			}

			if($strSqlGroupBy === '')
			{
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
			}
			elseif(!isset($arOptions['ENABLE_GROUPING_COUNT']) || $arOptions['ENABLE_GROUPING_COUNT'] === true)
			{
				if ($strSqlSelect <> '')
					$strSqlSelect .= ", ";
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
		}
		// <-- SELECT

		// WHERE -->
		$arJoins = array();
		$strSqlWhere = self::PrepareWhere($arFields, $arFilter, $arJoins);

		foreach($arJoins as $join)
		{
			if($join === '')
			{
				continue;
			}

			if ($strSqlFromWhere !== '')
			{
				$strSqlFromWhere .= ' ';
			}
			$strSqlFromWhere .= $join;

			if(!in_array($join, $arAlreadyJoined))
			{
				if ($strSqlFrom !== '')
				{
					$strSqlFrom .= ' ';
				}

				$strSqlFrom .= $join;
				$arAlreadyJoined[] = $join;
			}
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = array();
		$dbType = $DB->type;
		$nullsLast = isset($arOptions['NULLS_LAST']) ? (bool)$arOptions['NULLS_LAST'] : false;
		foreach ($arOrder as $by => $order)
		{
			$by = mb_strtoupper($by);
			$order = mb_strtoupper($order);

			if ($order != "ASC")
				$order = "DESC";

			if (array_key_exists($by, $arFields))
			{
				if(!$nullsLast)
				{
					if($dbType !== "ORACLE")
					{
						$arSqlOrder[] = $arFields[$by]["FIELD"]." ".$order;
					}
					else
					{
						if($order === 'ASC')
							$arSqlOrder[] = $arFields[$by]["FIELD"]." ".$order." NULLS FIRST";
						else
							$arSqlOrder[] = $arFields[$by]["FIELD"]." ".$order." NULLS LAST";
					}
				}
				else
				{
					if($dbType === "MYSQL")
					{
						//Use MySql feature for sort in 'NULLS_LAST' mode
						if($order === 'ASC')
							$arSqlOrder[] = "-".$arFields[$by]["FIELD"]." DESC";
						else
							$arSqlOrder[] = $arFields[$by]["FIELD"]." ".$order;
					}
				}

				if (isset($arFields[$by]["FROM"])
					&& $arFields[$by]["FROM"] <> ''
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if ($strSqlFrom <> '')
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
		}

		$strSqlOrderBy = '';
		DelDuplicateSort($arSqlOrder);
		$sqlOrderQty = count($arSqlOrder);
		for ($i = 0; $i < $sqlOrderQty; $i++)
		{
			if ($strSqlOrderBy <> '')
				$strSqlOrderBy .= ", ";

			$strSqlOrderBy .= $arSqlOrder[$i];
		}
		// <-- ORDER BY

		return array(
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"FROM_WHERE" => $strSqlFromWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy
		);
	}

	public static function PrepareWhere(&$arFields, &$arFilter, &$arJoins)
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$keyQty = count($filter_keys);
		for ($i = 0; $i < $keyQty; $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$filterKey = $filter_keys[$i];
			if(mb_strpos($filterKey, '__INNER_FILTER') === 0)
			{
				$innerFilterSql = self::PrepareWhere($arFields, $vals, $arJoins);
				if(is_string($innerFilterSql) && $innerFilterSql !== '')
				{
					$arSqlSearch[] = '('.$innerFilterSql.')';
				}
				continue;
			}

			$key_res = self::GetFilterOperation($filterKey);
			$key = $key_res["FIELD"];
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			$strOrNull = $key_res["OR_NULL"];

			if (array_key_exists($key, $arFields))
			{
				$arSqlSearch_tmp = array();

				if (!empty($vals))
				{
					if ($strOperation == "IN")
					{
						if (isset($arFields[$key]["WHERE"]))
						{
							$arSqlSearch_tmp1 = call_user_func_array(
								$arFields[$key]["WHERE"],
								array($vals, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], &$arFields, &$arFilter)
							);
							if ($arSqlSearch_tmp1 !== false)
								$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
						}
						else
						{
							if ($arFields[$key]["TYPE"] == "int")
							{
								array_walk(
									$vals,
									function (&$item) {
										$item = (int)$item;
									}
								);
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (empty($vals))
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." IN (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "double")
							{
								array_walk(
									$vals,
									function (&$item) {
										$item = (float)$item;
									}
								);
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (empty($vals))
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
							{
								array_walk(
									$vals,
									function (&$item) {
										$item = "'".$GLOBALS["DB"]->ForSql($item)."'";
									}
								);
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (empty($vals))
									$arSqlSearch_tmp[] = "(1 = 2)";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "datetime")
							{
								array_walk(
									$vals,
									function (&$item) {
										$item = $GLOBALS["DB"]->CharToDateFunction($item, "FULL");
									}
								);
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (empty($vals))
									$arSqlSearch_tmp[] = "1 = 2";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
							elseif ($arFields[$key]["TYPE"] == "date")
							{
								array_walk(
									$vals,
									function (&$item) {
										$item = $GLOBALS["DB"]->CharToDateFunction($item, "SHORT");
									}
								);
								$vals = array_unique($vals);
								$val = implode(",", $vals);

								if (empty($vals))
									$arSqlSearch_tmp[] = "1 = 2";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." (".$val."))";
							}
						}
					}
					else
					{
						foreach ($vals as $val)
						{
							if (isset($arFields[$key]["WHERE"]))
							{
								$arSqlSearch_tmp1 = call_user_func_array(
									$arFields[$key]["WHERE"],
									array($val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], &$arFields, &$arFilter)
								);
								if ($arSqlSearch_tmp1 !== false)
									$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
							}
							else
							{
								$fieldType = $arFields[$key]["TYPE"];
								$fieldName = $arFields[$key]["FIELD"];
								if ($strOperation === "QUERY" && $fieldType !== "string" && $fieldType !== "char")
								{
									// Ignore QUERY operation for not character types - QUERY is supported only for character types.
									$strOperation = '=';
								}

								if (($strOperation === "LIKE" || $strOperation === "RLIKE" || $strOperation === "LLIKE")
									&& ($fieldType === "int" || $fieldType === "double"))
								{
									// Ignore LIKE operation for numeric types.
									$strOperation = '=';
								}

								if ($fieldType === "int")
								{
									if ((intval($val) === 0) && (strpos($strOperation, "=") !== false))
										$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
									else
									{
										$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".intval($val)." )";
									}
								}
								elseif ($fieldType === "double")
								{
									$val = str_replace(",", ".", $val);

									if ((doubleval($val) === 0) && (strpos($strOperation, "=") !== false))
										$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
									else
										$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".DoubleVal($val)." )";
								}
								elseif ($fieldType === "string" || $fieldType === "char")
								{
									if ($strOperation === "QUERY")
									{
										$arSqlSearch_tmp[] = GetFilterQuery($fieldName, $val, "Y");
									}
									else
									{
										if (($val == '') && (strpos($strOperation, "=") !== false))
											$arSqlSearch_tmp[] = "(".$fieldName." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$DB->Length($fieldName)." <= 0) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$fieldName." ".$strOperation." '".$DB->ForSql($val)."' )";
										else
										{
											if($strOperation === "LIKE")
											{
												if(is_array($val))
													$arSqlSearch_tmp[] = "(".$fieldName." LIKE '%".implode("%' ESCAPE '!' OR ".$fieldName." LIKE '%", self::ForLike($val))."%' ESCAPE '!')";
												elseif($val == '')
													$arSqlSearch_tmp[] = $fieldName;
												else
													$arSqlSearch_tmp[] = $fieldName." LIKE '%".self::ForLike($val)."%' ESCAPE '!'";

											}
											elseif($strOperation === "RLIKE" || $strOperation === "LLIKE")
											{
												if(is_array($val))
													$arSqlSearch_tmp[] = "(".$fieldName." LIKE '".implode("' OR ". $fieldName." LIKE '", $DB->ForSql($val))."')";
												elseif($val == '')
													$arSqlSearch_tmp[] = $fieldName;
												else
													$arSqlSearch_tmp[] = $fieldName." LIKE '".$DB->ForSql($val)."'";
											}
											elseif($strOperation === "FT" || $strOperation === "FTI" || $strOperation === "FTL")
											{
												$queryWhere = new CSQLWhere();
												$queryWhere->SetFields(
													array(
														$key => array(
															'FIELD_NAME' => $fieldName,
															'FIELD_TYPE' => 'string',
															'JOIN' => false
														)
													)
												);

												$query = $queryWhere->GetQuery(array($filterKey => $val));
												if($query !== '')
												{
													$arSqlSearch_tmp[] = $query;
												}
											}
											else
												$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$fieldName." IS NULL OR NOT " : "")."(".$fieldName." ".$strOperation." '".$DB->ForSql($val)."' )";
										}
									}
								}
								elseif ($fieldType === "datetime")
								{
									if(!in_array($strOperation, array('=', '<', '>', '<=', '>='), true))
									{
										$strOperation = '=';
									}

									if ($val == '')
										$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
									elseif (mb_strtoupper($val) === "NOW")
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->GetNowFunction().")";
									else
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
								}
								elseif ($fieldType === "date")
								{
									if(!in_array($strOperation, array('=', '<', '>', '<=', '>='), true))
									{
										$strOperation = '=';
									}

									if ($val == '')
										$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
									else
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
								}
							}
						}
					}
				}

				if (isset($arFields[$key]["FROM"])
					&& $arFields[$key]["FROM"] <> ''
					&& !in_array($arFields[$key]["FROM"], $arJoins))
				{
					$arJoins[] = $arFields[$key]["FROM"];
				}

				$strSqlSearch_tmp = "";
				$sqlSearchQty = count($arSqlSearch_tmp);
				for ($j = 0; $j < $sqlSearchQty; $j++)
				{
					if ($j > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= self::AddBrackets($arSqlSearch_tmp[$j]);
				}
				if ($strOrNull == "Y")
				{
					if ($strSqlSearch_tmp <> '')
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." IS ".($strNegative=="Y" ? "NOT " : "")."NULL)";

					if ($arFields[$key]["TYPE"] == "int" || $arFields[$key]["TYPE"] == "double")
					{
						if ($strSqlSearch_tmp <> '')
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." 0)";
					}
					elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
					{
						if ($strSqlSearch_tmp <> '')
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." '')";
					}
				}

				if ($strSqlSearch_tmp != "")
				{
					$arSqlSearch[] = $strSqlSearch_tmp;
				}
			}
		}

		$logic = 'AND';
		if(isset($arFilter['LOGIC']) && $arFilter['LOGIC'] !== '')
		{
			$logic = mb_strtoupper($arFilter['LOGIC']);
			if($logic !== 'AND' && $logic !== 'OR')
			{
				$logic = 'AND';
			}
		}

		$strSqlWhere = '';
		$logic = " $logic ";
		$sqlSearchQty = count($arSqlSearch);
		for ($i = 0; $i < $sqlSearchQty; $i++)
		{
			$searchItem = $arSqlSearch[$i];

			if($searchItem === '')
			{
				continue;
			}

			if ($strSqlWhere !== '')
				$strSqlWhere .= $logic;

			$strSqlWhere .= "($searchItem)";
		}

		return $strSqlWhere;
	}

	private static function AddBrackets($str)
	{
		return preg_match('/^\(.*\)$/s', $str) > 0 ? $str : "($str)";
	}

	public static function GetRowCount(&$arSql, $tableName, $tableAlias = '', $dbType = '')
	{
		global $DB;

		$tableName = strval($tableName);
		$tableAlias = strval($tableAlias);

		/*$dbType = strval($dbType);
		if($dbType === '')
		{
			$dbType = 'MYSQL';
		}
		else
		{
			$dbType = strtoupper($dbType);
		}*/

		$query = 'SELECT COUNT(\'x\') as CNT FROM '.$tableName;

		if($tableAlias !== '')
		{
			$query .= ' '.$tableAlias;
		}

		if (isset($arSql['FROM'][0]))
		{
			$query .= ' '.$arSql['FROM'];
		}

		if (isset($arSql['WHERE'][0]))
		{
			$query .= ' WHERE '.$arSql['WHERE'];
		}

		if (isset($arSql['GROUPBY'][0]))
		{
			$query .= ' GROUP BY '.$arSql['GROUPBY'];
		}

		$rs = $DB->Query($query, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
		//MYSQL, MSSQL, ORACLE
		$result = 0;
		while($ary = $rs->Fetch())
		{
			$result += intval($ary['CNT']);
		}

		return $result;
	}

	public static function PrepareSelectTop(&$sql, $top)
	{
		$sql .= ' LIMIT '.$top;
	}

	private static function ForLike($str)
	{
		global $DB;
		static $search  = array( "!",  "_",  "%");
		static $replace = array("!!", "!_", "!%");
		return str_replace($search, $replace, $DB->ForSQL($str));
	}
}
