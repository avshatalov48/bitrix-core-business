<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

use Bitrix\Main\UserFieldTable;
use Bitrix\Main\UserFieldLangTable;

IncludeModuleLangFile(__FILE__);

/**
 * This class is used to manage metadata of user properties.
 *
 * <p>Selections, Deletion, Addition, and Update of metadata in the b_user_field table.</p>
 * create table b_user_field (
 * ID    int(11) not null auto_increment,
 * ENTITY_ID  varchar(50),
 * FIELD_NAME  varchar(50),
 * USER_TYPE_ID  varchar(50),
 * XML_ID    varchar(255),
 * SORT    int,
 * MULTIPLE  char(1) not null default 'N',
 * MANDATORY  char(1) not null default 'N',
 * SHOW_FILTER  char(1) not null default 'N',
 * SHOW_IN_LIST  char(1) not null default 'Y',
 * EDIT_IN_LIST  char(1) not null default 'Y',
 * IS_SEARCHABLE  char(1) not null default 'N',
 * SETTINGS  text,
 * PRIMARY KEY (ID),
 * UNIQUE ux_user_type_entity(ENTITY_ID, FIELD_NAME)
 * )
 * ------------------
 * ID
 * ENTITY_ID (example: IBLOCK_SECTION, USER ....)
 * FIELD_NAME (example: UF_EMAIL, UF_SOME_COUNTER ....)
 * SORT -- used to do check in the specified order
 * BASE_TYPE - String, Number, Integer, Enumeration, File, DateTime
 * USER_TYPE_ID
 * SETTINGS (blob) -- to store some settings which may be useful for an field instance
 * [some base settings comon to all types: mandatory or no, etc.]
 * <p>b_user_field</p>
 * <ul>
 * <li><b>ID</b> int(11) not null auto_increment
 * <li>ENTITY_ID varchar(50)
 * <li>FIELD_NAME varchar(20)
 * <li>USER_TYPE_ID varchar(50)
 * <li>XML_ID varchar(255)
 * <li>SORT int
 * <li>MULTIPLE char(1) not null default 'N'
 * <li>MANDATORY char(1) not null default 'N'
 * <li>SHOW_FILTER char(1) not null default 'N'
 * <li>SHOW_IN_LIST char(1) not null default 'Y'
 * <li>EDIT_IN_LIST char(1) not null default 'Y'
 * <li>IS_SEARCHABLE char(1) not null default 'N'
 * <li>SETTINGS text
 * <li>PRIMARY KEY (ID),
 * <li>UNIQUE ux_user_type_entity(ENTITY_ID, FIELD_NAME)
 * </ul>
 * create table b_user_field_lang (
 * USER_FIELD_ID int(11) REFERENCES b_user_field(ID),
 * LANGUAGE_ID char(2),
 * EDIT_FORM_LABEL varchar(255),
 * LIST_COLUMN_LABEL varchar(255),
 * LIST_FILTER_LABEL varchar(255),
 * ERROR_MESSAGE varchar(255),
 * HELP_MESSAGE varchar(255),
 * PRIMARY KEY (USER_FIELD_ID, LANGUAGE_ID)
 * )
 * <p>b_user_field_lang</p>
 * <ul>
 * <li><b>USER_FIELD_ID</b> int(11) REFERENCES b_user_field(ID)
 * <li><b>LANGUAGE_ID</b> char(2)
 * <li>EDIT_FORM_LABEL varchar(255)
 * <li>LIST_COLUMN_LABEL varchar(255)
 * <li>LIST_FILTER_LABEL varchar(255)
 * <li>ERROR_MESSAGE varchar(255)
 * <li>HELP_MESSAGE varchar(255)
 * <li>PRIMARY KEY (USER_FIELD_ID, LANGUAGE_ID)
 * </ul>
 * @package usertype
 * @subpackage classes
 */
class CAllUserTypeEntity extends CDBResult
{
	function CreatePropertyTables($entity_id)
	{
		$connection = \Bitrix\Main\Application::getConnection();

		if (!$connection->isTableExists("b_utm_" . strtolower($entity_id)))
		{
			$connection->createTable("b_utm_" . strtolower($entity_id), [
				'ID' => new \Bitrix\Main\ORM\Fields\IntegerField('ID'),
				'VALUE_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_ID'),
				'FIELD_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('FIELD_ID'),
				'VALUE' => new \Bitrix\Main\ORM\Fields\TextField('VALUE', ['nullable' => true]),
				'VALUE_INT' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_INT', ['nullable' => true]),
				'VALUE_DOUBLE' => new \Bitrix\Main\ORM\Fields\FloatField('VALUE_DOUBLE', ['nullable' => true]),
				'VALUE_DATE' => new \Bitrix\Main\ORM\Fields\DatetimeField('VALUE_DATE', ['nullable' => true]),
			], ['ID'], ['ID']);

			$connection->createIndex("b_utm_" . strtolower($entity_id), "ix_utm_" . $entity_id . "_2", ["VALUE_ID"]);
			$connection->createIndex("b_utm_" . strtolower($entity_id), "ix_utm_" . $entity_id . "_4", ["FIELD_ID", "VALUE_ID", "VALUE_INT"]);
		}

		if (!$connection->isTableExists("b_uts_" . strtolower($entity_id)))
		{
			$connection->createTable("b_uts_" . strtolower($entity_id), [
				'VALUE_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_ID'),
			], ['VALUE_ID']);
		}

		return true;
	}

	/**
	 * Function to fetch metadata of a user property.
	 *
	 * <p>Returns an associative array of metadata that can be passed to Update.</p>
	 * @param integer $ID Property identifier
	 * @return array If the property is not found, false is returned
	 * @static
	 */
	public static function GetByID($ID)
	{
		global $DB;
		static $arLabels = ["EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE"];
		static $cache = [];

		if (!array_key_exists($ID, $cache))
		{
			$rsUserField = CUserTypeEntity::GetList([], ["ID" => intval($ID)]);
			if ($arUserField = $rsUserField->Fetch())
			{
				$rs = $DB->Query("SELECT * FROM b_user_field_lang WHERE USER_FIELD_ID = " . intval($ID));
				while ($ar = $rs->Fetch())
				{
					foreach ($arLabels as $label)
					{
						$arUserField[$label][$ar["LANGUAGE_ID"]] = $ar[$label];
					}
				}
				$cache[$ID] = $arUserField;
			}
			else
			{
				$cache[$ID] = false;
			}
		}
		return $cache[$ID];
	}

	/**
	 * Function to fetch metadata of user properties.
	 *
	 * <p>Returns CDBResult - a selection based on filter and sorting.</p>
	 * <p>The aSort parameter defaults to array("SORT"=>"ASC", "ID"=>"ASC").</p>
	 * <p>If LANG is passed in aFilter, language messages are additionally selected.</p>
	 * @param array $aSort Associative array for sorting (ID, ENTITY_ID, FIELD_NAME, SORT, USER_TYPE_ID)
	 * @param array $aFilter Associative array for filtering with strict matching (<b>equals</b>) (ID, ENTITY_ID, FIELD_NAME, USER_TYPE_ID, SORT, MULTIPLE, MANDATORY, SHOW_FILTER)
	 * @return CDBResult
	 * @static
	 */
	public static function GetList($aSort = [], $aFilter = [])
	{
		global $DB, $CACHE_MANAGER;

		if (CACHED_b_user_field !== false)
		{
			$cacheId = "b_user_type" . md5(serialize($aSort) . "." . serialize($aFilter));
			if ($CACHE_MANAGER->Read(CACHED_b_user_field, $cacheId, "b_user_field"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				$res = new CUserTypeEntity($res);
				return $res;
			}
		}

		$bLangJoin = false;
		$arFilter = [];
		foreach ($aFilter as $key => $val)
		{
			if (is_array($val) || (string)$val == '')
			{
				continue;
			}

			$key = strtoupper($key);
			$val = $DB->ForSql($val);

			switch ($key)
			{
				case "ID":
				case "ENTITY_ID":
				case "FIELD_NAME":
				case "USER_TYPE_ID":
				case "XML_ID":
				case "SORT":
				case "MULTIPLE":
				case "MANDATORY":
				case "SHOW_FILTER":
				case "SHOW_IN_LIST":
				case "EDIT_IN_LIST":
				case "IS_SEARCHABLE":
					$arFilter[] = "UF." . $key . " = '" . $val . "'";
					break;
				case "LANG":
					$bLangJoin = $val;
					break;
			}
		}

		$arOrder = [];
		foreach ($aSort as $key => $val)
		{
			$key = strtoupper($key);
			$ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			switch ($key)
			{
				case "ID":
				case "ENTITY_ID":
				case "FIELD_NAME":
				case "USER_TYPE_ID":
				case "XML_ID":
				case "SORT":
					$arOrder[] = "UF." . $key . " " . $ord;
					break;
			}
		}
		if (empty($arOrder))
		{
			$arOrder[] = "UF.SORT asc";
			$arOrder[] = "UF.ID asc";
		}
		DelDuplicateSort($arOrder);
		$sOrder = "\nORDER BY " . implode(", ", $arOrder);

		if (empty($arFilter))
		{
			$sFilter = "";
		}
		else
		{
			$sFilter = "\nWHERE " . implode("\nAND ", $arFilter);
		}

		$strSql = "
			SELECT
				UF.ID
				,UF.ENTITY_ID
				,UF.FIELD_NAME
				,UF.USER_TYPE_ID
				,UF.XML_ID
				,UF.SORT
				,UF.MULTIPLE
				,UF.MANDATORY
				,UF.SHOW_FILTER
				,UF.SHOW_IN_LIST
				,UF.EDIT_IN_LIST
				,UF.IS_SEARCHABLE
				,UF.SETTINGS
				" . ($bLangJoin ? "
					,UFL.EDIT_FORM_LABEL
					,UFL.LIST_COLUMN_LABEL
					,UFL.LIST_FILTER_LABEL
					,UFL.ERROR_MESSAGE
					,UFL.HELP_MESSAGE
				" : "") . "
			FROM
				b_user_field UF
				" . ($bLangJoin ? "LEFT JOIN b_user_field_lang UFL on UFL.LANGUAGE_ID = '" . $bLangJoin . "' AND UFL.USER_FIELD_ID = UF.ID" : "") . "
			" . $sFilter . $sOrder;

		if (CACHED_b_user_field === false)
		{
			$res = $DB->Query($strSql);
		}
		else
		{
			$arResult = [];
			$res = $DB->Query($strSql);
			while ($ar = $res->Fetch())
			{
				$arResult[] = $ar;
			}

			/** @noinspection PhpUndefinedVariableInspection */
			$CACHE_MANAGER->Set($cacheId, $arResult);

			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}

		return new CUserTypeEntity($res);
	}

	/**
	 * Function to validate metadata values of user properties.
	 *
	 * <p>Called in Add and Update methods to check the correctness of the entered values.</p>
	 * <p>Validations:</p>
	 * <ul>
	 * <li>ENTITY_ID - required
	 * <li>ENTITY_ID - no more than 50 characters
	 * <li>ENTITY_ID - must not contain any characters other than 0-9, A-Z, and _
	 * <li>FIELD_NAME - required
	 * <li>FIELD_NAME - at least 4 characters
	 * <li>FIELD_NAME - no more than 50 characters
	 * <li>FIELD_NAME - must not contain any characters other than 0-9, A-Z, and _
	 * <li>FIELD_NAME - must start with UF_
	 * <li>USER_TYPE_ID - required
	 * <li>USER_TYPE_ID - must be registered
	 * </ul>
	 * <p>In case of an error, catch the application exception!</p>
	 * @param integer $ID - property identifier. 0 - for new.
	 * @param array $arFields Property metadata
	 * @param bool $bCheckUserType
	 * @return boolean false - if any validation fails.
	 */
	public function CheckFields($ID, $arFields, $bCheckUserType = true)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $APPLICATION, $USER_FIELD_MANAGER;
		$aMsg = [];
		$ID = intval($ID);

		if (($ID <= 0 || array_key_exists("ENTITY_ID", $arFields)) && $arFields["ENTITY_ID"] == '')
		{
			$aMsg[] = ["id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_MISSING")];
		}
		if (array_key_exists("ENTITY_ID", $arFields))
		{
			if (mb_strlen($arFields["ENTITY_ID"]) > 50)
			{
				$aMsg[] = ["id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_TOO_LONG1")];
			}
			if (!preg_match('/^[0-9A-Z_]+$/', $arFields["ENTITY_ID"]))
			{
				$aMsg[] = ["id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_INVALID")];
			}
		}

		if (($ID <= 0 || array_key_exists("FIELD_NAME", $arFields)) && $arFields["FIELD_NAME"] == '')
		{
			$aMsg[] = ["id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_MISSING")];
		}
		if (array_key_exists("FIELD_NAME", $arFields))
		{
			if (mb_strlen($arFields["FIELD_NAME"]) < 4)
			{
				$aMsg[] = ["id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_TOO_SHORT")];
			}
			if (mb_strlen($arFields["FIELD_NAME"]) > 50)
			{
				$aMsg[] = ["id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_TOO_LONG1")];
			}
			if (strncmp($arFields["FIELD_NAME"], "UF_", 3) !== 0)
			{
				$aMsg[] = ["id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_NOT_UF")];
			}
			if (!preg_match('/^[0-9A-Z_]+$/', $arFields["FIELD_NAME"]))
			{
				$aMsg[] = ["id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_INVALID")];
			}
		}

		if (($ID <= 0 || array_key_exists("USER_TYPE_ID", $arFields)) && $arFields["USER_TYPE_ID"] == '')
		{
			$aMsg[] = ["id" => "USER_TYPE_ID", "text" => GetMessage("USER_TYPE_USER_TYPE_ID_MISSING")];
		}
		if (
			$bCheckUserType
			&& array_key_exists("USER_TYPE_ID", $arFields)
			&& !$USER_FIELD_MANAGER->GetUserType($arFields["USER_TYPE_ID"])
		)
		{
			$aMsg[] = ["id" => "USER_TYPE_ID", "text" => GetMessage("USER_TYPE_USER_TYPE_ID_INVALID")];
		}

		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	/**
	 * Function to add a user property.
	 *
	 * <p>First, the instance method CheckFields is called (i.e., $this->CheckFields($arFields) ).</p>
	 * <p>If the validation is successful, a check is performed to see if such a field already exists for the given entity.</p>
	 * <p>Then, if necessary, tables of the form <b>b_uts_[ENTITY_ID]</b> and <b>b_utm_[ENTITY_ID]</b> are created.</p>
	 * <p>After that, the metadata is saved in the database.</p>
	 * <p>Only after this, <b>the structure of the table b_uts_[ENTITY_ID] is modified</b>.</p>
	 * <p>Array arFields:</p>
	 * <ul>
	 * <li>ENTITY_ID - entity
	 * <li>FIELD_NAME - the actual column name in the database where the property values will be stored.
	 * <li>USER_TYPE_ID - property type
	 * <li>XML_ID - identifier for use in import/export
	 * <li>SORT - sort order (default 100)
	 * <li>MULTIPLE - multiplicity flag Y/N (default N)
	 * <li>MANDATORY - mandatory value input flag Y/N (default N)
	 * <li>SHOW_FILTER - whether to show in the admin list filter and what type to use. see below.
	 * <li>SHOW_IN_LIST - whether to show in the admin list (default Y)
	 * <li>EDIT_IN_LIST - allow editing in forms, but not in API! (default Y)
	 * <li>IS_SEARCHABLE - field participates in search (default N)
	 * <li>SETTINGS - array with property settings dependent on the property type. They are "cleaned" through the type handler PrepareSettings.
	 * <li>EDIT_FORM_LABEL - array of language messages in the form array("ru"=>"привет", "en"=>"hello")
	 * <li>LIST_COLUMN_LABEL
	 * <li>LIST_FILTER_LABEL
	 * <li>ERROR_MESSAGE
	 * <li>HELP_MESSAGE
	 * </ul>
	 * <p>In case of an error, catch the application exception!</p>
	 * <p>Values for SHOW_FILTER:</p>
	 * <ul>
	 * <li>N - do not show
	 * <li>I - exact match
	 * <li>E - mask
	 * <li>S - substring
	 * </ul>
	 * @param array $arFields Metadata of the new property
	 * @param bool $bCheckUserType
	 * @return integer - identifier of the added property, false - if the property was not added.
	 */
	public function Add($arFields, $bCheckUserType = true)
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER;

		if (!$this->CheckFields(0, $arFields, $bCheckUserType))
		{
			return false;
		}

		$rs = CUserTypeEntity::GetList([], [
			"ENTITY_ID" => $arFields["ENTITY_ID"],
			"FIELD_NAME" => $arFields["FIELD_NAME"],
		]);

		if ($rs->Fetch())
		{
			$aMsg = [];
			$aMsg[] = [
				"id" => "FIELD_NAME",
				"text" => GetMessage("USER_TYPE_ADD_ALREADY_ERROR", [
					"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
					"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
				]),
			];
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		unset($arFields["ID"]);
		if (!isset($arFields["SORT"]) || intval($arFields["SORT"]) <= 0)
		{
			$arFields["SORT"] = 100;
		}
		if (!isset($arFields["MULTIPLE"]) || $arFields["MULTIPLE"] !== "Y")
		{
			$arFields["MULTIPLE"] = "N";
		}
		if (!isset($arFields["MANDATORY"]) || $arFields["MANDATORY"] !== "Y")
		{
			$arFields["MANDATORY"] = "N";
		}
		$arFields["SHOW_FILTER"] = mb_substr($arFields["SHOW_FILTER"] ?? '', 0, 1);
		if ($arFields["SHOW_FILTER"] == '' || mb_strpos("NIES", $arFields["SHOW_FILTER"]) === false)
		{
			$arFields["SHOW_FILTER"] = "N";
		}
		if (!isset($arFields["SHOW_IN_LIST"]) || $arFields["SHOW_IN_LIST"] !== "N")
		{
			$arFields["SHOW_IN_LIST"] = "Y";
		}
		if (!isset($arFields["EDIT_IN_LIST"]) || $arFields["EDIT_IN_LIST"] !== "N")
		{
			$arFields["EDIT_IN_LIST"] = "Y";
		}
		if (!isset($arFields["IS_SEARCHABLE"]) || $arFields["IS_SEARCHABLE"] !== "Y")
		{
			$arFields["IS_SEARCHABLE"] = "N";
		}

		if (!array_key_exists("SETTINGS", $arFields))
		{
			$arFields["SETTINGS"] = [];
		}
		$arFields["SETTINGS"] = serialize($USER_FIELD_MANAGER->PrepareSettings(0, $arFields, $bCheckUserType));

		/**
		 * events
		 * PROVIDE_STORAGE - use own uf subsystem to store data (uts/utm tables)
		 */
		$commonEventResult = ['PROVIDE_STORAGE' => true];

		foreach (GetModuleEvents("main", "OnBeforeUserTypeAdd", true) as $arEvent)
		{
			$eventResult = ExecuteModuleEventEx($arEvent, [&$arFields]);

			if ($eventResult === false)
			{
				if ($APPLICATION->GetException())
				{
					return false;
				}

				$aMsg = [];
				$aMsg[] = [
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_ADD_ERROR", [
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					]),
				];

				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);

				return false;
			}
			elseif (is_array($eventResult))
			{
				$commonEventResult = array_merge($commonEventResult, $eventResult);
			}
		}

		if (is_object($USER_FIELD_MANAGER))
		{
			$USER_FIELD_MANAGER->CleanCache();
		}

		if ($commonEventResult['PROVIDE_STORAGE'])
		{
			if (!$this->CreatePropertyTables($arFields["ENTITY_ID"]))
			{
				return false;
			}

			$strType = $USER_FIELD_MANAGER->getUtsDBColumnType($arFields);

			if (!$strType)
			{
				$aMsg = [];
				$aMsg[] = [
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_ADD_ERROR", [
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					]),
				];
				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);
				return false;
			}

			$tableName = 'b_uts_' . mb_strtolower($arFields['ENTITY_ID']);
			$tableFields = $DB->GetTableFields($tableName);
			if (!array_key_exists($arFields['FIELD_NAME'], $tableFields))
			{
				$ddl = 'ALTER TABLE ' . $tableName . ' ADD ' . $arFields['FIELD_NAME'] . ' ' . $strType;
				if (!$DB->DDL($ddl, true))
				{
					$aMsg = [];
					$aMsg[] = [
						"id" => "FIELD_NAME",
						"text" => GetMessage("USER_TYPE_ADD_ERROR", [
							"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
							"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
						]),
					];
					$e = new CAdminException($aMsg);
					$APPLICATION->ThrowException($e);
					return false;
				}
			}
		}

		if ($ID = $DB->Add("b_user_field", $arFields, ["SETTINGS"], '', true))
		{
			$arLabels = ["EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE"];
			$arLangs = [];
			foreach ($arLabels as $label)
			{
				if (isset($arFields[$label]) && is_array($arFields[$label]))
				{
					foreach ($arFields[$label] as $lang => $value)
					{
						$arLangs[$lang][$label] = $value;
					}
				}
			}

			foreach ($arLangs as $lang => $arLangFields)
			{
				$arLangFields["USER_FIELD_ID"] = $ID;
				$arLangFields["LANGUAGE_ID"] = $lang;
				$arInsert = $DB->PrepareInsert("b_user_field_lang", $arLangFields);
				$DB->Query("INSERT INTO b_user_field_lang (" . $arInsert[0] . ") VALUES (" . $arInsert[1] . ")");
			}

			static::cleanCache();
		}
		else
		{
			return false;
		}

		// post event
		$arFields['ID'] = $ID;

		foreach (GetModuleEvents("main", "OnAfterUserTypeAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$arFields]);
		}

		return $ID;
	}

	/**
	 * Function to modify metadata of a user property.
	 *
	 * <p>It should be noted that for the sake of faster development, it was decided not to implement
	 * the same flexibility as in infoblocks (we will do without alters and other things for now).</p>
	 * <p>First, the instance method CheckFields is called (i.e., $this->CheckFields($arFields) ).</p>
	 * <p>After that, the metadata is saved in the database.</p>
	 * <p>Array arFields (only what can be changed):</p>
	 * <ul>
	 * <li>SORT - sort order
	 * <li>MANDATORY - mandatory value input flag Y/N
	 * <li>SHOW_FILTER - flag to show in the list filter Y/N
	 * <li>SHOW_IN_LIST - flag to show in the list Y/N
	 * <li>EDIT_IN_LIST - allow editing the field in admin forms or not Y/N
	 * <li>IS_SEARCHABLE - search flag Y/N
	 * <li>SETTINGS - array with property settings dependent on the property type. They are "cleaned" through the type handler PrepareSettings.
	 * <li>EDIT_FORM_LABEL - array of language messages in the form array("ru"=>"привет", "en"=>"hello")
	 * <li>LIST_COLUMN_LABEL
	 * <li>LIST_FILTER_LABEL
	 * <li>ERROR_MESSAGE
	 * <li>HELP_MESSAGE
	 * </ul>
	 * <p>In case of an error, catch the application exception!</p>
	 * @param integer $ID Property identifier
	 * @param array $arFields New property metadata
	 * @return boolean - true if the update is successful, false otherwise.
	 */
	public function Update($ID, $arFields)
	{
		global $DB, $USER_FIELD_MANAGER, $APPLICATION;
		$ID = intval($ID);

		unset($arFields["ENTITY_ID"]);
		unset($arFields["FIELD_NAME"]);
		unset($arFields["USER_TYPE_ID"]);
		unset($arFields["MULTIPLE"]);

		if (!$this->CheckFields($ID, $arFields))
		{
			return false;
		}

		if (array_key_exists("SETTINGS", $arFields))
		{
			$arFields["SETTINGS"] = serialize($USER_FIELD_MANAGER->PrepareSettings($ID, $arFields));
		}
		if (array_key_exists("MANDATORY", $arFields) && $arFields["MANDATORY"] !== "Y")
		{
			$arFields["MANDATORY"] = "N";
		}
		if (array_key_exists("SHOW_FILTER", $arFields))
		{
			$arFields["SHOW_FILTER"] = mb_substr($arFields["SHOW_FILTER"], 0, 1);
			if (mb_strpos("NIES", $arFields["SHOW_FILTER"]) === false)
			{
				$arFields["SHOW_FILTER"] = "N";
			}
		}
		if (array_key_exists("SHOW_IN_LIST", $arFields) && $arFields["SHOW_IN_LIST"] !== "N")
		{
			$arFields["SHOW_IN_LIST"] = "Y";
		}
		if (array_key_exists("EDIT_IN_LIST", $arFields) && $arFields["EDIT_IN_LIST"] !== "N")
		{
			$arFields["EDIT_IN_LIST"] = "Y";
		}
		if (array_key_exists("IS_SEARCHABLE", $arFields) && $arFields["IS_SEARCHABLE"] !== "Y")
		{
			$arFields["IS_SEARCHABLE"] = "N";
		}

		// events
		foreach (GetModuleEvents("main", "OnBeforeUserTypeUpdate", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [&$arFields]) === false)
			{
				if ($APPLICATION->GetException())
				{
					return false;
				}

				$aMsg = [];
				$aMsg[] = [
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_UPDATE_ERROR", [
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					]),
				];

				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);

				return false;
			}
		}

		if (is_object($USER_FIELD_MANAGER))
		{
			$USER_FIELD_MANAGER->CleanCache();
		}

		$strUpdate = $DB->PrepareUpdate("b_user_field", $arFields);

		static $arLabels = ["EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE"];
		$arLangs = [];
		foreach ($arLabels as $label)
		{
			if (isset($arFields[$label]) && is_array($arFields[$label]))
			{
				foreach ($arFields[$label] as $lang => $value)
				{
					$arLangs[$lang][$label] = $value;
				}
			}
		}

		if ($strUpdate <> "" || !empty($arLangs))
		{
			if ($strUpdate <> "")
			{
				$strSql = "UPDATE b_user_field SET " . $strUpdate . " WHERE ID = " . $ID;
				if (array_key_exists("SETTINGS", $arFields))
				{
					$arBinds = ["SETTINGS" => $arFields["SETTINGS"]];
				}
				else
				{
					$arBinds = [];
				}
				$DB->QueryBind($strSql, $arBinds);
			}

			if (!empty($arLangs))
			{
				$DB->StartTransaction();

				$DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $ID);

				foreach ($arLangs as $lang => $arLangFields)
				{
					$arLangFields["USER_FIELD_ID"] = $ID;
					$arLangFields["LANGUAGE_ID"] = $lang;
					$arInsert = $DB->PrepareInsert("b_user_field_lang", $arLangFields);
					$DB->Query("INSERT INTO b_user_field_lang (" . $arInsert[0] . ") VALUES (" . $arInsert[1] . ")");
				}

				$DB->Commit();
			}

			static::cleanCache();

			foreach (GetModuleEvents("main", "OnAfterUserTypeUpdate", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$arFields, $ID]);
			}
		}

		return true;
	}

	/**
	 * Function to delete a user property and all its values.
	 *
	 * <p>First, the property metadata is deleted.</p>
	 * <p>Then, all values of multiple properties are deleted from the table of the form <b>b_utm_[ENTITY_ID]</b>.</p>
	 * <p>After that, the column is dropped from the table of the form <b>b_uts_[ENTITY_ID]</b>.</p>
	 * <p>And if this was the "last" property for the entity, the tables storing the values are dropped themselves.</p>
	 * @param integer $ID Property identifier
	 * @return CDBResult | false - result of the last query executed by the function.
	 */
	public function Delete($ID)
	{
		global $DB, $USER_FIELD_MANAGER, $APPLICATION;
		$ID = intval($ID);

		$rs = $this->GetList([], ["ID" => $ID]);
		if ($arField = $rs->Fetch())
		{
			/**
			 * events
			 * PROVIDE_STORAGE - use own uf subsystem to store data (uts/utm tables)
			 */
			$commonEventResult = ['PROVIDE_STORAGE' => true];

			foreach (GetModuleEvents("main", "OnBeforeUserTypeDelete", true) as $arEvent)
			{
				$eventResult = ExecuteModuleEventEx($arEvent, [&$arField]);

				if ($eventResult === false)
				{
					if ($APPLICATION->GetException())
					{
						return false;
					}

					$aMsg = [];
					$aMsg[] = [
						"id" => "FIELD_NAME",
						"text" => GetMessage("USER_TYPE_DELETE_ERROR", [
							"#FIELD_NAME#" => htmlspecialcharsbx($arField["FIELD_NAME"]),
							"#ENTITY_ID#" => htmlspecialcharsbx($arField["ENTITY_ID"]),
						]),
					];

					$e = new CAdminException($aMsg);
					$APPLICATION->ThrowException($e);

					return false;
				}
				elseif (is_array($eventResult))
				{
					$commonEventResult = array_merge($commonEventResult, $eventResult);
				}
			}

			$entityId = strtolower($arField["ENTITY_ID"]);

			$arType = $USER_FIELD_MANAGER->GetUserType($arField["USER_TYPE_ID"]);
			//We need special handling of file type properties
			if ($arType)
			{
				if ($arType["BASE_TYPE"] == "file" && $commonEventResult['PROVIDE_STORAGE'])
				{
					// only if we store values
					if ($arField["MULTIPLE"] == "Y")
					{
						$strSql = "SELECT VALUE_INT AS VALUE FROM b_utm_" . $entityId . " WHERE FIELD_ID=" . $arField["ID"];
					}
					else
					{
						$strSql = "SELECT " . $arField["FIELD_NAME"] . " AS VALUE FROM b_uts_" . $entityId;
					}
					$rsFile = $DB->Query($strSql);
					while ($arFile = $rsFile->Fetch())
					{
						CFile::Delete($arFile["VALUE"]);
					}
				}
				elseif ($arType["BASE_TYPE"] == "enum")
				{
					$obEnum = new CUserFieldEnum;
					$obEnum->DeleteFieldEnum($arField["ID"]);
				}
			}

			$rs = $DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $ID);
			if ($rs)
			{
				$rs = $DB->Query("DELETE FROM b_user_field WHERE ID = " . $ID);
			}

			if ($rs && $commonEventResult['PROVIDE_STORAGE'])
			{
				// only if we store values
				$rs = $this->GetList([], ["ENTITY_ID" => $arField["ENTITY_ID"]]);
				if ($rs->Fetch()) // more than one
				{
					$DB->Query("ALTER TABLE b_uts_" . $entityId . " DROP " . $arField["FIELD_NAME"], true);
					$rs = $DB->Query("DELETE FROM b_utm_" . $entityId . " WHERE FIELD_ID = '" . $ID . "'");
				}
				else
				{
					$DB->Query("DROP TABLE IF EXISTS b_uts_" . $entityId);
					$rs = $DB->Query("DROP TABLE IF EXISTS b_utm_" . $entityId);
				}
			}

			static::cleanCache();

			foreach (GetModuleEvents("main", "OnAfterUserTypeDelete", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$arField, $ID]);
			}
		}
		return $rs;
	}

	/**
	 * Function to delete ALL user properties of an entity.
	 *
	 * <p>First, the property metadata is deleted.</p>
	 * <p>Can be called, for example, when deleting an infoblock.</p>
	 * <p>Then, the tables of the form <b>b_utm_[ENTITY_ID]</b> and <b>b_uts_[ENTITY_ID]</b> are dropped.</p>
	 * @param string $entity_id Entity identifier
	 * @return CDBResult - result of the last query executed by the function.
	 */
	public function DropEntity($entity_id)
	{
		global $DB, $USER_FIELD_MANAGER;

		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);

		$rs = true;
		$rsFields = $this->GetList([], ["ENTITY_ID" => $entity_id]);
		//We need special handling of file and enum type properties
		while ($arField = $rsFields->Fetch())
		{
			$arType = $USER_FIELD_MANAGER->GetUserType($arField["USER_TYPE_ID"]);
			if ($arType && ($arType["BASE_TYPE"] == "file" || $arType["BASE_TYPE"] == "enum"))
			{
				$this->Delete($arField["ID"]);
			}
		}

		$bDropTable = false;
		$rsFields = $this->GetList([], ["ENTITY_ID" => $entity_id]);
		while ($arField = $rsFields->Fetch())
		{
			$bDropTable = true;
			$DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $arField["ID"]);
			$rs = $DB->Query("DELETE FROM b_user_field WHERE ID = " . $arField["ID"]);

			foreach (GetModuleEvents("main", "OnAfterUserTypeDelete", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$arField, $arField["ID"]]);
			}
		}

		if ($bDropTable)
		{
			$DB->Query("DROP TABLE IF EXISTS b_uts_" . strtolower($entity_id), true);
			$rs = $DB->Query("DROP TABLE IF EXISTS b_utm_" . strtolower($entity_id), true);
		}

		static::cleanCache();

		return $rs;
	}

	protected static function cleanCache(): void
	{
		global $CACHE_MANAGER, $USER_FIELD_MANAGER;

		if (CACHED_b_user_field !== false)
		{
			$CACHE_MANAGER->CleanDir("b_user_field");
		}
		UserFieldTable::cleanCache();
		UserFieldLangTable::cleanCache();
		$USER_FIELD_MANAGER->CleanCache();
	}

	/**
	 * Fetch function.
	 *
	 * <p>Deserializes the SETTINGS field.</p>
	 * @return array Returns false in case of the last record in the selection.
	 */
	function Fetch()
	{
		$res = parent::Fetch();
		if ($res && $res["SETTINGS"] <> '')
		{
			$res["SETTINGS"] = unserialize($res["SETTINGS"], ['allowed_classes' => false]);
		}
		return $res;
	}
}

class CUserTypeEntity extends CAllUserTypeEntity
{
}
