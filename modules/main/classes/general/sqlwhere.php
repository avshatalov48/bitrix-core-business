<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\ORM\Query\Filter;

/**
 * array("LOGIC"=>"AND",
 * 	"="."K1" => value,
 * 	"="."K2" => value,
 * 	array("LOGIC"=>"OR",
 * 	"="."K3" => value,
 * 	"="."K3" => value,
 * ),
 * array("LOGIC"=>"OR",
 * 	"="."K4" => value,
 * 	"="."K4" => value,
 * ),
 * )
 * K1=value and K2=value and (k3=value or k3=value) and (k4=value or k4=value)
 */
class CAllSQLWhere
{
	const FT_MIN_TOKEN_SIZE = 3;

	var $fields = array(
	/*
		"ID" => array(
			"FIELD_NAME" => "UF.ID",
		),
	*/
	);
	var $c_joins = array();
	var $l_joins = array();
	var $bDistinctReqired = false;

	static $operations = array(
		"!><" => "NB", //not between
		"!=%" => "NM", //not Identical by like
		"!%=" => "NM", //not Identical by like
		"!==" => "SN", // strong negation for boolean and null
		"!=" => "NI", //not Identical
		"!%" => "NS", //not substring
		"><" => "B",  //between
		">=" => "GE", //greater or equal
		"<=" => "LE", //less or equal
		"=%" => "M", //Identical by like
		"%=" => "M", //Identical by like
		"!@" => "NIN", //not in
		"==" => "SE",  // strong equality (not is null)
		"=" => "I", //Identical
		"%" => "S", //substring
		"?" => "?", //logical
		">" => "G", //greater
		"<" => "L", //less
		"!" => "N", // not field LIKE val
		"@" => "IN", // IN (new SqlExpression)
		"*" => "FT", // partial full text match
		"*=" => "FTI", // identical full text match
		"*%" => "FTL", // partial full text match based on LIKE
	);

	public function _Upper($field)
	{
		return "UPPER(".$field.")";
	}

	public function _Empty($field)
	{
		return "(".$field." IS NULL OR ".$field." = '')";
	}

	public function _NotEmpty($field)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		return "(".$field." IS NOT NULL AND " . $helper->getLengthFunction($field) . " > 0)";
	}

	public function _StringEQ($field, $sql_value)
	{
		return $field." = '".$sql_value."'";
	}

	public function _StringNotEQ($field, $sql_value)
	{
		return "(".$field." IS NULL OR ".$field." <> '".$sql_value."')";
	}

	public function _StringIN($field, $sql_values)
	{
		return $field." in ('".implode("', '", $sql_values)."')";
	}

	public function _StringNotIN($field, $sql_values)
	{
		return "(".$field." IS NULL OR ".$field." not in ('".implode("', '", $sql_values)."'))";
	}

	public function _ExprEQ($field, $val)
	{
		return $field." = ".$val->compile();
	}

	public function _ExprNotEQ($field, $val)
	{
		return "(".$field." IS NULL OR ".$field." <> ".$val->compile().")";
	}

	public function _NumberIN($field, $sql_values)
	{
		$result = $field." in (".implode(", ", $sql_values).")";
		if (in_array(0, $sql_values, true))
			$result .= " or ".$field." IS NULL";
		return $result;
	}

	public function _NumberNotIN($field, $sql_values)
	{
		$result = $field." not in (".implode(", ", $sql_values).")";
		if (in_array(0, $sql_values, true))
			$result .= " and ".$field." IS NOT NULL";
		return $result;
	}

	/**
	 * @deprecated Use \Bitrix\Main\ORM\Query\Filter\Helper::splitWords()
	 * @param string $string
	 * @return array
	 */
	public static function splitWords($string)
	{
		return Filter\Helper::splitWords($string);
	}

	/**
	 * @deprecated Use \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize()
	 * @return int
	 */
	public static function GetMinTokenSize()
	{
		return Filter\Helper::getMinTokenSize();
	}

	public function match($field, $fieldValue, $wildcard)
	{
		if (!is_array($fieldValue))
		{
			$fieldValue = array($fieldValue);
		}
		$orValues = array();
		$wildcard = ($wildcard? "*" : "");

		foreach ($fieldValue as $value)
		{
			$match = Filter\Helper::matchAgainstWildcard($value, $wildcard);
			if ($match <> '')
			{
				$orValues[] = $match;
			}
		}

		if(!empty($orValues))
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
			$value = $helper->getMatchOrExpression($orValues);

			return $helper->getMatchFunction($field, "'" . $helper->forSql($value) . "'");
		}

		return '';
	}

	public function matchLike($field, $fieldValue)
	{
		if(!is_array($fieldValue))
		{
			$fieldValue = array($fieldValue);
		}
		$orValues = array();

		foreach($fieldValue as $value)
		{
			//split to words by any non-word symbols
			$andValues = Filter\Helper::splitWords($value);
			if(!empty($andValues))
			{
				$andValues = array_map(
					function($val)
					{
						return CSQLWhere::ForLIKE(mb_strtoupper($val));
					},
					$andValues
				);

				$orValues[] = "(".$this->_Upper($field)." like '%".implode("%' ESCAPE '!' AND ".$this->_Upper($field)." like '%", $andValues)."%' ESCAPE '!')";
			}
		}
		if(!empty($orValues))
		{
			return "(".implode("\n OR ", $orValues).")";
		}

		return '';
	}

	public function AddFields($arFields)
	{
		if(is_array($arFields))
		{
			foreach($arFields as $key=>$arField)
			{
				$key = strtoupper($key);
				if(!isset($this->fields[$key]) && is_array($arField) && $arField["FIELD_NAME"] <> '')
				{
					$ar = array();
					$ar["TABLE_ALIAS"] = $arField["TABLE_ALIAS"] ?? '';
					$ar["FIELD_NAME"] = $arField["FIELD_NAME"];
					$ar["FIELD_TYPE"] = $arField["FIELD_TYPE"] ?? '';
					$ar["USER_TYPE_ID"] = $arField["USER_TYPE_ID"] ?? '';
					$ar["MULTIPLE"] = $arField["MULTIPLE"] ?? "N";
					$ar["JOIN"] = $arField["JOIN"] ?? '';
					if(isset($arField["LEFT_JOIN"]))
						$ar["LEFT_JOIN"] = $arField["LEFT_JOIN"];
					if(isset($arField["CALLBACK"]))
						$ar["CALLBACK"] = $arField["CALLBACK"];
					$this->fields[$key] = $ar;
				}
			}
		}
	}

	public function SetFields($arFields)
	{
		$this->fields = array();
		$this->AddFields($arFields);
	}

	public function MakeOperation($key)
	{
		if(isset(self::$operations[$op = mb_substr($key, 0, 3)]))
		{
			return array("FIELD"=> mb_substr($key, 3), "OPERATION"=>self::$operations[$op]);
		}
		elseif(isset(self::$operations[$op = mb_substr($key, 0, 2)]))
		{
			return array("FIELD"=> mb_substr($key, 2), "OPERATION"=>self::$operations[$op]);
		}
		elseif(isset(self::$operations[$op = mb_substr($key, 0, 1)]))
		{
			return array("FIELD"=> mb_substr($key, 1), "OPERATION"=>self::$operations[$op]);
		}
		else
		{
			return array("FIELD"=>$key, "OPERATION"=>"E"); // field LIKE val
		}
	}

	public static function getOperationByCode($code)
	{
		$all_operations = array_flip(self::$operations);

		return $all_operations[$code] ?? null;
	}

	public function GetQuery($arFilter)
	{
		$this->l_joins = array();
		$this->c_joins = array();
		foreach($this->fields as $key=>$field)
		{
			$this->l_joins[$field["TABLE_ALIAS"]] = isset($field['LEFT_JOIN']);
			$this->c_joins[$key] = 0;
		}
		return $this->GetQueryEx($arFilter, $this->l_joins);
	}

	public function GetQueryEx($arFilter, &$arJoins, $level=0)
	{
		if(!is_array($arFilter))
			return "";

		$logic = false;
		if(isset($arFilter['LOGIC']))
		{
			$logic = $arFilter["LOGIC"];
			unset($arFilter["LOGIC"]);
		}

		$inverted = false;
		if($logic == 'NOT')
		{
			$inverted = true;
			$logic = 'AND';
		}

		if($logic !== "OR")
			$logic = "AND";

		$result = array();
		foreach($arFilter as $key=>$value)
		{
			if(is_numeric($key))
			{
				$arRecursiveJoins = $arJoins;
				$value = $this->GetQueryEx($value, $arRecursiveJoins, $level+1);
				if($value <> '')
					$result[] = "(".$value."\n".str_repeat("\t", $level).")";

				foreach($arRecursiveJoins as $TABLE_ALIAS=>$bLeftJoin)
				{
					if($bLeftJoin)
					{
						if($logic == "OR")
							$arJoins[$TABLE_ALIAS] |= true;
						else
							$arJoins[$TABLE_ALIAS] &= true;
					}
					else
					{
						if($logic == "OR")
							$arJoins[$TABLE_ALIAS] |= false;
						else
							$arJoins[$TABLE_ALIAS] &= false;
					}
				}
			}
			else
			{
				$operation = $this->MakeOperation($key);
				$key = mb_strtoupper($operation["FIELD"]);
				$operation = $operation["OPERATION"];

				if(isset($this->fields[$key]))
				{
					$FIELD_NAME = $this->fields[$key]["FIELD_NAME"];
					$FIELD_TYPE = $this->fields[$key]["FIELD_TYPE"];
					//Handle joins logic
					if (!isset($this->c_joins[$key]))
					{
						$this->c_joins[$key] = 0;
					}
					$this->c_joins[$key]++;

					if (!empty($this->fields[$key]["TABLE_ALIAS"]))
					{
						if(
							(
								($operation=="I" || $operation=="E" || $operation=="S" || $operation=="M")
								&& (
									is_scalar($value)
									&& (
										($FIELD_TYPE=="int" && intval($value)==0)
										|| ($FIELD_TYPE=="double" && doubleval($value)==0)
										|| $value == ''
									)
								)
							)
							||
							(
								($operation=="NI" || $operation=="N" || $operation=="NS" || $operation=="NB" || $operation=="NM")
								&& !is_object($value)
								&& (
									is_array($value)
									|| (
										($FIELD_TYPE=="int" && intval($value)!=0)
										|| ($FIELD_TYPE=="double" && doubleval($value)!=0)
										|| ($FIELD_TYPE!="int" && $FIELD_TYPE!="double" && is_scalar($value) && $value <> '')
									)
								)
							)
						)
						{
							if($logic == "OR")
								$arJoins[$this->fields[$key]["TABLE_ALIAS"]] |= true;
							else
								$arJoins[$this->fields[$key]["TABLE_ALIAS"]] &= true;
						}
						else
						{
							if($logic == "OR")
								$arJoins[$this->fields[$key]["TABLE_ALIAS"]] |= false;
							else
								$arJoins[$this->fields[$key]["TABLE_ALIAS"]] &= false;
						}
					}

					switch($FIELD_TYPE)
					{
						case "file":
						case "enum":
						case "int":
							$this->addIntFilter($result, $this->fields[$key]["MULTIPLE"] === "Y", $FIELD_NAME, $operation, $value);
							break;
						case "double":
							$this->addFloatFilter($result, $this->fields[$key]["MULTIPLE"] === "Y", $FIELD_NAME, $operation, $value);
							break;
						case "string":
							$this->addStringFilter($result, $this->fields[$key]["MULTIPLE"] === "Y", $FIELD_NAME, $operation, $value);
							break;
						case "date":
						case "datetime":
							if($FIELD_TYPE == "date" || $this->fields[$key]["USER_TYPE_ID"] == "date")
							{
								$this->addDateFilter($result, $this->fields[$key]["MULTIPLE"] === "Y", $FIELD_NAME, $operation, $value, "SHORT");
							}
							else
							{
								$this->addDateFilter($result, $this->fields[$key]["MULTIPLE"] === "Y", $FIELD_NAME, $operation, $value, "FULL");
							}
							break;
						case "callback":
							$res = call_user_func_array($this->fields[$key]["CALLBACK"], array(
								$FIELD_NAME,
								$operation,
								$value,
							));
							if($res <> '')
							{
								$result[] = $res;
							}
							break;
					}
				}
			}
		}

		if(!empty($result))
			return "\n".str_repeat("\t", $level).($inverted ? 'NOT (' : '').implode("\n".str_repeat("\t", $level).$logic." ", $result).($inverted ? ')' : '');
		else
			return "";
	}

	public function GetJoins()
	{
		$result = array();

		foreach($this->c_joins as $key => $counter)
		{
			if($counter > 0)
			{
				$TABLE_ALIAS = $this->fields[$key]["TABLE_ALIAS"];
				if($this->l_joins[$TABLE_ALIAS])
					$result[$TABLE_ALIAS] = $this->fields[$key]["LEFT_JOIN"];
				else
					$result[$TABLE_ALIAS] = $this->fields[$key]["JOIN"];
			}
		}
		return implode("\n", $result);
	}

	public function ForLIKE($str)
	{
		global $DB;
		static $search  = array( "!",  "_",  "%");
		static $replace = array("!!", "!_", "!%");
		return str_replace($search, $replace, $DB->ForSQL($str));
	}

	public function addIntFilter(&$result, $isMultiple, $FIELD_NAME, $operation, $value)
	{
		if (is_array($value))
			$FIELD_VALUE = array_map("intval", $value);
		elseif (is_object($value))
			$FIELD_VALUE = $value;
		else
			$FIELD_VALUE = intval($value);

		switch ($operation)
		{
		case "I":
		case "E":
		case "S":
		case "M":
		case "SE":
			if (is_array($FIELD_VALUE))
			{
				if (!empty($FIELD_VALUE))
					$result[] = "(".$this->_NumberIN($FIELD_NAME, $FIELD_VALUE).")";
				else
					$result[] = "1=0";

				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." = ".$FIELD_VALUE->compile();
			elseif ($FIELD_VALUE == 0 && $operation !== "SE")
				$result[] = "(".$FIELD_NAME." IS NULL OR ".$FIELD_NAME." = 0)";
			else
				$result[] = $FIELD_NAME." = ".$FIELD_VALUE;
			break;
		case "NI":
		case "N":
		case "NS":
		case "NM":
			if (is_array($FIELD_VALUE))
			{
				if (!empty($FIELD_VALUE))
					$result[] = "(".$this->_NumberNotIN($FIELD_NAME, $FIELD_VALUE).")";
				else
					$result[] = "1=1";
			}
			elseif ($FIELD_VALUE instanceof \Bitrix\Main\DB\SqlExpression)
			{
				$result[] = $FIELD_NAME." <> ".$FIELD_VALUE->compile();
			}
			elseif ($FIELD_VALUE == 0)
				$result[] = "(".$FIELD_NAME." IS NOT NULL AND ".$FIELD_NAME." <> 0)";
			else
				$result[] = $FIELD_NAME." <> ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "G":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." > ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif ($FIELD_VALUE instanceof \Bitrix\Main\DB\SqlExpression)
			{
				$result[] = $FIELD_NAME." > ".$FIELD_VALUE->compile();
			}
			else
				$result[] = $FIELD_NAME." > ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "L":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." < ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif ($FIELD_VALUE instanceof \Bitrix\Main\DB\SqlExpression)
			{
				$result[] = $FIELD_NAME." < ".$FIELD_VALUE->compile();
			}
			else
				$result[] = $FIELD_NAME." < ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "GE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." >= ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif ($FIELD_VALUE instanceof \Bitrix\Main\DB\SqlExpression)
			{
				$result[] = $FIELD_NAME." >= ".$FIELD_VALUE->compile();
			}
			else
				$result[] = $FIELD_NAME." >= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "LE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." <= ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif ($FIELD_VALUE instanceof \Bitrix\Main\DB\SqlExpression)
			{
				$result[] = $FIELD_NAME." <= ".$FIELD_VALUE->compile();
			}
			else
				$result[] = $FIELD_NAME." <= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "B":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NB":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." not between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "IN":
			if(is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." IN (".$FIELD_VALUE->compile().")";
			elseif(is_array($FIELD_VALUE))
				$result[] = $FIELD_NAME." IN (".implode(",", $FIELD_VALUE).")";
			else
				$result[] = $FIELD_NAME." IN (".$FIELD_VALUE.")";
			break;
		case "NIN":
			if(is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." NOT IN (".$FIELD_VALUE->compile().")";
			elseif(is_array($FIELD_VALUE))
				$result[] = $FIELD_NAME." NOT IN (".implode(",", $FIELD_VALUE).")";
			else
				$result[] = $FIELD_NAME." NOT IN (".$FIELD_VALUE.")";
			break;
		}
	}

	public function addFloatFilter(&$result, $isMultiple, $FIELD_NAME, $operation, $value)
	{
		if (is_array($value))
		{
			$FIELD_VALUE = [];
			foreach($value as $i => $val)
			{
				$FIELD_VALUE[$i] = doubleval($val);
				if(!is_finite($FIELD_VALUE[$i]))
				{
					$FIELD_VALUE[$i] = 0;
				}
			}
		}
		elseif (is_object($value))
		{
			$FIELD_VALUE = $value;
		}
		else
		{
			$FIELD_VALUE = doubleval($value);
			if(!is_finite($FIELD_VALUE))
			{
				$FIELD_VALUE = 0;
			}
		}

		switch ($operation)
		{
		case "I":
		case "E":
		case "S":
		case "M":
			if (is_array($FIELD_VALUE))
			{
				if (!empty($FIELD_VALUE))
					$result[] = "(".$this->_NumberIN($FIELD_NAME, $FIELD_VALUE).")";
				else
					$result[] = "1=0";

				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." = ".$FIELD_VALUE->compile();
			elseif ($FIELD_VALUE == 0)
				$result[] = "(".$FIELD_NAME." IS NULL OR ".$FIELD_NAME." = 0)";
			else
				$result[] = $FIELD_NAME." = ".$FIELD_VALUE;
			break;
		case "NI":
		case "N":
		case "NS":
		case "NM":
			if (is_array($FIELD_VALUE))
			{
				if (!empty($FIELD_VALUE))
					$result[] = "(".$this->_NumberNotIN($FIELD_NAME, $FIELD_VALUE).")";
				else
					$result[] = "1=1";
			}
			elseif ($FIELD_VALUE == 0)
				$result[] = "(".$FIELD_NAME." IS NOT NULL AND ".$FIELD_NAME." <> 0)";
			else
				$result[] = $FIELD_NAME." <> ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "G":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." > ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." > ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "L":
			if (is_array($FIELD_VALUE))
			{
				if (is_array($FIELD_VALUE))
				{
					if (isset($FIELD_VALUE[0]))
					{
						$result[] = $FIELD_NAME." < ".$FIELD_VALUE[0];
					}
					else
					{
						$result[] = "1=0";
					}
				}
			}
			else
				$result[] = $FIELD_NAME." < ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "GE":
			if (is_array($FIELD_VALUE))
			{
				if (is_array($FIELD_VALUE))
				{
					if (isset($FIELD_VALUE[0]))
					{
						$result[] = $FIELD_NAME." >= ".$FIELD_VALUE[0];
					}
					else
					{
						$result[] = "1=0";
					}
				}
			}
			else
				$result[] = $FIELD_NAME." >= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "LE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." <= ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." <= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "B":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE)>1)
				$result[] = $FIELD_NAME." between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NB":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE)>1)
				$result[] = $FIELD_NAME." not between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "IN":
			$result[] = $FIELD_NAME." IN (".$FIELD_VALUE->compile().")";
			break;
		case "NIN":
			$result[] = $FIELD_NAME." NOT IN (".$FIELD_VALUE->compile().")";
			break;
		}
	}

	public function addStringFilter(&$result, $isMultiple, $FIELD_NAME, $operation, $value)
	{
		global $DB;

		if (is_array($value))
		{
			$FIELD_VALUE = array();
			if ($operation=="S" || $operation=="NS")
			{
				foreach ($value as $val)
					$FIELD_VALUE[] = $this->ForLIKE(mb_strtoupper((string)$val));
			}
			else
			{
				foreach ($value as $val)
					$FIELD_VALUE[] = $DB->ForSQL((string)$val);
			}
		}
		elseif (is_object($value))
		{
			$FIELD_VALUE = $value;
		}
		else
		{
			if ($operation=="S" || $operation=="NS")
				$FIELD_VALUE = $this->ForLIKE(mb_strtoupper((string)$value));
			else
				$FIELD_VALUE = $DB->ForSQL((string)$value);
		}

		switch ($operation)
		{
		case "I":
			if (is_array($FIELD_VALUE))
			{
				$result[] = $this->_StringIN($FIELD_NAME, $FIELD_VALUE);
				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			elseif (is_object($FIELD_VALUE))
			{
				$result[] = $this->_ExprEQ($FIELD_NAME, $FIELD_VALUE);
			}
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_Empty($FIELD_NAME);
			else
				$result[] = $this->_StringEQ($FIELD_NAME, $FIELD_VALUE);
			break;
		case "E":
			if (is_array($FIELD_VALUE))
				$result[] = "(".$this->_Upper($FIELD_NAME)." like upper('".implode("') OR ".$this->_Upper($FIELD_NAME)." like upper('", $FIELD_VALUE)."'))";
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_ExprEQ($FIELD_NAME, $FIELD_VALUE);
			elseif($FIELD_VALUE == '')
				$result[] = $this->_Empty($FIELD_NAME);
			else
			{
				//kinda optimization for digits only
				if (preg_match("/[^0-9]/", $FIELD_VALUE))
					$result[] = $this->_Upper($FIELD_NAME)." like upper('".$FIELD_VALUE."')";
				else
					$result[] = $this->_StringEQ($FIELD_NAME, $FIELD_VALUE);
			}

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "S":
			if (is_array($FIELD_VALUE))
				$result[] = "(".$this->_Upper($FIELD_NAME)." like '%".implode("%' ESCAPE '!' OR ".$this->_Upper($FIELD_NAME)." like '%", $FIELD_VALUE)."%' ESCAPE '!')";
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_Upper($FIELD_NAME)." like ".$FIELD_VALUE->compile()." ESCAPE '!'";
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_Empty($FIELD_NAME);
			else
				$result[] = $this->_Upper($FIELD_NAME)." like '%".$FIELD_VALUE."%' ESCAPE '!'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "M":
			if (is_array($FIELD_VALUE))
				$result[] = "(".$FIELD_NAME." like '".implode("' OR ".$FIELD_NAME." like '", $FIELD_VALUE)."')";
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_ExprEQ($FIELD_NAME, $FIELD_VALUE);
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_Empty($FIELD_NAME);
			else
			{
				//kinda optimization for digits only
				if (preg_match("/[^0-9]/", $FIELD_VALUE))
					$result[] = $FIELD_NAME." like '".$FIELD_VALUE."'";
				else
					$result[] = $this->_StringEQ($FIELD_NAME, $FIELD_VALUE);
			}

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NI":
			if (is_array($FIELD_VALUE))
				$result[] = $this->_StringNotIN($FIELD_NAME, $FIELD_VALUE);
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_ExprNotEQ($FIELD_NAME, $FIELD_VALUE);
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_NotEmpty($FIELD_NAME);
			else
				$result[] = $this->_StringNotEQ($FIELD_NAME, $FIELD_VALUE);

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "N":
			if (is_array($FIELD_VALUE))
				$result[] = "(".$this->_Upper($FIELD_NAME)." not like upper('".implode("') AND ".$this->_Upper($FIELD_NAME)." not like upper('", $FIELD_VALUE)."'))";
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_Upper($FIELD_NAME)." not like ".$FIELD_VALUE->compile();
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_NotEmpty($FIELD_NAME);
			else
				$result[] = $this->_Upper($FIELD_NAME)." not like upper('".$FIELD_VALUE."')";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NS":
			if (is_array($FIELD_VALUE))
				$result[] = "(".$this->_Upper($FIELD_NAME)." not like '%".implode("%' ESCAPE '!' AND ".$this->_Upper($FIELD_NAME)." not like '%", $FIELD_VALUE)."%' ESCAPE '!')";
			elseif (is_object($FIELD_VALUE))
				$result[] = $this->_Upper($FIELD_NAME)." not like ".$FIELD_VALUE->compile();
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_NotEmpty($FIELD_NAME);
			else
				$result[] = $this->_Upper($FIELD_NAME)." not like '%".$FIELD_VALUE."%' ESCAPE '!'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NM":
			if(is_array($FIELD_VALUE))
				$result[] = "(".$FIELD_NAME." not like '".implode("' AND ".$FIELD_NAME." not like '", $FIELD_VALUE)."')";
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." not like ".$FIELD_VALUE->compile();
			elseif ($FIELD_VALUE == '')
				$result[] = $this->_NotEmpty($FIELD_NAME);
			else
				$result[] = $FIELD_NAME." not like '".$FIELD_VALUE."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "G":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." > '".$FIELD_VALUE[0]."'";
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." > ".$FIELD_VALUE->compile();
			else
				$result[] = $FIELD_NAME." > '".$FIELD_VALUE."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "L":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." < '".$FIELD_VALUE[0]."'";
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." < ".$FIELD_VALUE->compile();
			else
				$result[] = $FIELD_NAME." < '".$FIELD_VALUE."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "GE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." >= '".$FIELD_VALUE[0]."'";
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." >= ".$FIELD_VALUE->compile();
			else
				$result[] = $FIELD_NAME." >= '".$FIELD_VALUE."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "LE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." <= '".$FIELD_VALUE[0]."'";
				}
				else
				{
					$result[] = "1=0";
				}
			}
			elseif (is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." <= ".$FIELD_VALUE->compile();
			else
				$result[] = $FIELD_NAME." <= '".$FIELD_VALUE."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "B":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." between '".$FIELD_VALUE[0]."' AND '".$FIELD_VALUE[1]."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NB":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." not between '".$FIELD_VALUE[0]."' AND '".$FIELD_VALUE[1]."'";

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "?":
			if (is_scalar($FIELD_VALUE) && mb_strlen($FIELD_VALUE))
			{
				$q = GetFilterQuery($FIELD_NAME, $FIELD_VALUE);
				// Check if error ("0" was returned)
				if ($q !== '0')
					$result[] = $q;
			}
			break;
		case "IN":
			if(is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." IN (".$FIELD_VALUE->compile().")";
			elseif(is_array($FIELD_VALUE))
				$result[] = $FIELD_NAME." IN ('".implode("', '", $FIELD_VALUE)."')";
			else
				$result[] = $FIELD_NAME." IN ('".$FIELD_VALUE."')";
			break;
		case "NIN":
			if(is_object($FIELD_VALUE))
				$result[] = $FIELD_NAME." NOT IN (".$FIELD_VALUE->compile().")";
			elseif(is_array($FIELD_VALUE))
				$result[] = $FIELD_NAME." NOT IN ('".implode("', '", $FIELD_VALUE)."')";
			else
				$result[] = $FIELD_NAME." NOT IN ('".$FIELD_VALUE."')";
			break;
		case "FT":
		case "FTI":
			$part = $this->match($FIELD_NAME, $value, ($operation == "FT"));
			if($part <> '')
			{
				$result[] = $part;

				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			break;
		case "FTL":
			$part = $this->matchLike($FIELD_NAME, $value);
			if($part <> '')
			{
				$result[] = $part;

				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			break;
		}
	}

	public function addDateFilter(&$result, $isMultiple, $FIELD_NAME, $operation, $value, $format)
	{
		global $DB;

		if (is_array($value))
		{
			$FIELD_VALUE = array();
			foreach ($value as $val)
			{
				if ($val instanceof \Bitrix\Main\Type\Date)
				{
					$FIELD_VALUE[] = $DB->CharToDateFunction((string)$val, $format);
				}
				elseif (is_object($val))
				{
					$FIELD_VALUE[] = $val->compile();
				}
				elseif($val <> '')
				{
					$FIELD_VALUE[] = $DB->CharToDateFunction($val, $format);
				}
				else
				{
					$FIELD_VALUE[] = 'NULL';
				}
			}
		}
		elseif ($value instanceof \Bitrix\Main\Type\Date)
		{
			$FIELD_VALUE = $DB->CharToDateFunction((string)$value, $format);
		}
		elseif (is_object($value))
		{
			$FIELD_VALUE = $value->compile();
		}
		elseif($value <> '')
		{
			$FIELD_VALUE = $DB->CharToDateFunction($value, $format);
		}
		else
		{
			$FIELD_VALUE = 'NULL';
		}

		switch($operation)
		{
		case "I":
		case "E":
		case "S":
		case "M":
			if (is_array($FIELD_VALUE))
			{
				$result[] = $FIELD_NAME." in (".implode(", ", $FIELD_VALUE).")";
				if ($isMultiple)
					$this->bDistinctReqired = true;
			}
			elseif ($value == '')
				$result[] = "(".$FIELD_NAME." IS NULL)";
			else
				$result[] = $FIELD_NAME." = ".$FIELD_VALUE;
			break;
		case "NI":
		case "N":
		case "NS":
		case "NM":
			if (is_array($FIELD_VALUE))
				$result[] = $FIELD_NAME." not in (".implode(", ", $FIELD_VALUE).")";
			elseif ($value == '')
				$result[] = "(".$FIELD_NAME." IS NOT NULL)";
			else
				$result[] = $FIELD_NAME." <> ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "G":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." > ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." > ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "L":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." < ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." < ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "GE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." >= ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." >= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "LE":
			if (is_array($FIELD_VALUE))
			{
				if (isset($FIELD_VALUE[0]))
				{
					$result[] = $FIELD_NAME." <= ".$FIELD_VALUE[0];
				}
				else
				{
					$result[] = "1=0";
				}
			}
			else
				$result[] = $FIELD_NAME." <= ".$FIELD_VALUE;

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "B":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "NB":
			if (is_array($FIELD_VALUE) && count($FIELD_VALUE) > 1)
				$result[] = $FIELD_NAME." not between ".$FIELD_VALUE[0]." AND ".$FIELD_VALUE[1];

			if ($isMultiple)
				$this->bDistinctReqired = true;
			break;
		case "IN":
			$result[] = $FIELD_NAME." IN (".$FIELD_VALUE->compile().")";
			break;
		}
	}
}

/**
 * Class CSQLWhereExpression
 * @deprecated  use \Bitrix\Main\DB\SqlExpression instead
 * @see \Bitrix\Main\DB\SqlExpression
 */
class CSQLWhereExpression
{
	protected
		$expression,
		$args;

	protected
		$i;

	protected
		$DB;

	public function __construct($expression, $args = null)
	{
		$this->expression = $expression;

		if (!is_null($args))
		{
			$this->args =  is_array($args) ? $args : array($args);
		}

		global $DB;
		$this->DB = $DB;
	}

	public function compile()
	{
		$this->i = -1;

		// string (default), integer (i), float (f), numeric (n), date (d), time (t)
		$value = preg_replace_callback('/(?:[^\\\\]|^)(\?[#sifv]?)/', array($this, 'execPlaceholders'), $this->expression);
		$value = str_replace('\?', '?', $value);

		return $value;
	}

	protected function execPlaceholders($matches)
	{
		$this->i++;

		$id = $matches[1];

		if (isset($this->args[$this->i]))
		{
			$value = $this->args[$this->i];

			if ($id == '?' || $id == '?s')
			{
				return "'" . $this->DB->ForSql($value) . "'";
			}
			elseif ($id == '?#')
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();

				return $helper->quote($value);
			}
			elseif ($id == '?v')
			{
				$connection = \Bitrix\Main\Application::getConnection();
				$helper = $connection->getSqlHelper();

				return $helper->values($value);
			}
			elseif ($id == '?i')
			{
				return (int) $value;
			}
			elseif ($id == '?f')
			{
				return (float) $value;
			}
		}

		return $id;
	}
}

class CSQLWhere extends CAllSQLWhere
{
}
