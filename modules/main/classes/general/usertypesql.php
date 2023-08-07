<?php

class CUserTypeSQL
{
	var $table_alias = "BUF";
	var $entity_id = false;
	var $user_fields = array();

	var $select = array();
	var $filter = array();
	var $order = array();

	/** @var CSQLWhere */
	var $obWhere = false;

	function SetEntity($entity_id, $ID)
	{
		global $USER_FIELD_MANAGER;

		$this->user_fields = $USER_FIELD_MANAGER->GetUserFields($entity_id);
		$this->entity_id = mb_strtolower(preg_replace("/[^0-9A-Z_]+/", "", $entity_id));
		$this->select = array();
		$this->filter = array();
		$this->order = array();

		$this->obWhere = new CSQLWhere;
		$num = 0;
		$arFields = array();
		foreach($this->user_fields as $FIELD_NAME => $arField)
		{
			if($arField["MULTIPLE"] == "Y")
				$num++;
			$table_alias = $arField["MULTIPLE"] == "N" ? $this->table_alias : $this->table_alias . $num;
			$arType = $this->user_fields[$FIELD_NAME]["USER_TYPE"];

			if($arField["MULTIPLE"] == "N")
				$TABLE_FIELD_NAME = $table_alias . "." . $FIELD_NAME;
			elseif($arType["BASE_TYPE"] == "int")
				$TABLE_FIELD_NAME = $table_alias . ".VALUE_INT";
			elseif($arType["BASE_TYPE"] == "file")
				$TABLE_FIELD_NAME = $table_alias . ".VALUE_INT";
			elseif($arType["BASE_TYPE"] == "enum")
				$TABLE_FIELD_NAME = $table_alias . ".VALUE_INT";
			elseif($arType["BASE_TYPE"] == "double")
				$TABLE_FIELD_NAME = $table_alias . ".VALUE_DOUBLE";
			elseif($arType["BASE_TYPE"] == "datetime")
				$TABLE_FIELD_NAME = $table_alias . ".VALUE_DATE";
			else
				$TABLE_FIELD_NAME = $table_alias . ".VALUE";

			$arFields[$FIELD_NAME] = array(
				"TABLE_ALIAS" => $table_alias,
				"FIELD_NAME" => $TABLE_FIELD_NAME,
				"FIELD_TYPE" => $arType["BASE_TYPE"],
				"USER_TYPE_ID" => $arType["USER_TYPE_ID"],
				"MULTIPLE" => $arField["MULTIPLE"],
				"JOIN" => $arField["MULTIPLE"] == "N" ?
					"INNER JOIN b_uts_" . $this->entity_id . " " . $table_alias . " ON " . $table_alias . ".VALUE_ID = " . $ID :
					"INNER JOIN b_utm_" . $this->entity_id . " " . $table_alias . " ON " . $table_alias . ".FIELD_ID = " . $arField["ID"] . " AND " . $table_alias . ".VALUE_ID = " . $ID,
				"LEFT_JOIN" => $arField["MULTIPLE"] == "N" ?
					"LEFT JOIN b_uts_" . $this->entity_id . " " . $table_alias . " ON " . $table_alias . ".VALUE_ID = " . $ID :
					"LEFT JOIN b_utm_" . $this->entity_id . " " . $table_alias . " ON " . $table_alias . ".FIELD_ID = " . $arField["ID"] . " AND " . $table_alias . ".VALUE_ID = " . $ID,
			);

			if($arType["BASE_TYPE"] == "enum")
			{
				$arFields[$FIELD_NAME . "_VALUE"] = array(
					"TABLE_ALIAS" => $table_alias . "EN",
					"FIELD_NAME" => $table_alias . "EN.VALUE",
					"FIELD_TYPE" => "string",
					"MULTIPLE" => $arField["MULTIPLE"],
					"JOIN" => $arField["MULTIPLE"] == "N" ?
						"INNER JOIN b_uts_" . $this->entity_id . " " . $table_alias . "E ON " . $table_alias . "E.VALUE_ID = " . $ID . "
						INNER JOIN b_user_field_enum " . $table_alias . "EN ON " . $table_alias . "EN.ID = " . $table_alias . "E." . $FIELD_NAME :
						"INNER JOIN b_utm_" . $this->entity_id . " " . $table_alias . "E ON " . $table_alias . "E.FIELD_ID = " . $arField["ID"] . " AND " . $table_alias . "E.VALUE_ID = " . $ID . "
						INNER JOIN b_user_field_enum " . $table_alias . "EN ON " . $table_alias . "EN.ID = " . $table_alias . "E.VALUE_INT",
					"LEFT_JOIN" => $arField["MULTIPLE"] == "N" ?
						"LEFT JOIN b_uts_" . $this->entity_id . " " . $table_alias . "E ON " . $table_alias . "E.VALUE_ID = " . $ID . "
						LEFT JOIN b_user_field_enum " . $table_alias . "EN ON " . $table_alias . "EN.ID = " . $table_alias . "E." . $FIELD_NAME :
						"LEFT JOIN b_utm_" . $this->entity_id . " " . $table_alias . "E ON " . $table_alias . "E.FIELD_ID = " . $arField["ID"] . " AND " . $table_alias . "E.VALUE_ID = " . $ID . "
						LEFT JOIN b_user_field_enum " . $table_alias . "EN ON " . $table_alias . "EN.ID = " . $table_alias . "E.VALUE_INT",
				);
			}
		}
		$this->obWhere->SetFields($arFields);
	}

	function SetSelect($arSelect)
	{
		$this->obWhere->bDistinctReqired = false;
		$this->select = array();
		if(is_array($arSelect))
		{
			if(in_array("UF_*", $arSelect))
			{
				foreach($this->user_fields as $FIELD_NAME => $arField)
				{
					$this->select[$FIELD_NAME] = true;
				}
			}
			else
			{
				foreach($arSelect as $field)
				{
					if(array_key_exists($field, $this->user_fields))
					{
						$this->select[$field] = true;
					}
				}
			}
		}
	}

	function GetDistinct()
	{
		return $this->obWhere->bDistinctReqired;
	}

	function GetSelect()
	{
		$result = "";
		foreach($this->select as $key => $value)
		{
			$simpleFormat = true;
			if($this->user_fields[$key]["MULTIPLE"] == "N")
			{
				if($arType = $this->user_fields[$key]["USER_TYPE"])
				{
					if(is_callable(array($arType["CLASS_NAME"], "FormatField")))
					{
						$result .= ", " . call_user_func_array(array($arType["CLASS_NAME"], "FormatField"), array($this->user_fields[$key], $this->table_alias . "." . $key)) . " " . $key;
						$simpleFormat = false;
					}
				}
			}
			if($simpleFormat)
			{
				$result .= ", " . $this->table_alias . "." . $key;
			}
		}
		return $result;
	}

	function GetJoin($ID)
	{
		$result = $this->obWhere->GetJoins();
		$table = " b_uts_" . $this->entity_id . " " . $this->table_alias . " ";
		if((!empty($this->select) || !empty($this->order)) && strpos($result, $table) === false)
			$result .= "\nLEFT JOIN" . $table . "ON " . $this->table_alias . ".VALUE_ID = " . $ID;
		return $result;
	}

	function SetOrder($arOrder)
	{
		if(is_array($arOrder))
		{
			$this->order = array();
			foreach($arOrder as $field => $order)
			{
				if(array_key_exists($field, $this->user_fields))
					$this->order[$field] = $order != "ASC" ? "DESC" : "ASC";
			}
		}
	}

	function GetOrder($field)
	{
		$field = mb_strtoupper($field);
		if(isset($this->order[$field]))
			$result = $this->table_alias . "." . $field;
		else
			$result = "";
		return $result;
	}

	function SetFilter($arFilter)
	{
		if(is_array($arFilter))
			$this->filter = $arFilter;
	}

	function GetFilter()
	{
		return $this->obWhere->GetQuery($this->filter);
	}
}
