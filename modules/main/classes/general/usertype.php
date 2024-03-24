<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

/**
 * usertype.php, Пользовательские свойства
 *
 * Содержит классы для поддержки пользовательских свойств.
 * @author Bitrix <support@bitrixsoft.com>
 * @version 1.0
 * @package usertype
 * @todo Добавить подсказку
 */

IncludeModuleLangFile(__FILE__);

/**
 * Данный класс используется для управления метаданными пользовательских свойств.
 *
 * <p>Выборки, Удаление Добавление и обновление метаданных таблицы b_user_field.</p>
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

		if (!$connection->isTableExists("b_utm_".strtolower($entity_id)))
		{
			$connection->createTable("b_utm_".strtolower($entity_id), [
				'ID' => new \Bitrix\Main\ORM\Fields\IntegerField('ID'),
				'VALUE_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_ID'),
				'FIELD_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('FIELD_ID'),
				'VALUE' => new \Bitrix\Main\ORM\Fields\TextField('VALUE', ['nullable' => true]),
				'VALUE_INT' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_INT', ['nullable' => true]),
				'VALUE_DOUBLE' => new \Bitrix\Main\ORM\Fields\FloatField('VALUE_DOUBLE', ['nullable' => true]),
				'VALUE_DATE' => new \Bitrix\Main\ORM\Fields\DatetimeField('VALUE_DATE', ['nullable' => true]),
			], ['ID'] ,['ID']);

			$connection->createIndex("b_utm_".strtolower($entity_id), "ix_utm_".$entity_id."_2", ["VALUE_ID"]);
			$connection->createIndex("b_utm_".strtolower($entity_id), "ix_utm_".$entity_id."_4", ["FIELD_ID", "VALUE_ID", "VALUE_INT"]);
		}

		if (!$connection->isTableExists("b_uts_".strtolower($entity_id)))
		{
			$connection->createTable("b_uts_".strtolower($entity_id), [
				'VALUE_ID' => new \Bitrix\Main\ORM\Fields\IntegerField('VALUE_ID'),
			], ['VALUE_ID']);
		}

		return true;
	}

	function DropColumnSQL($strTable, $arColumns)
	{
		return array("ALTER TABLE ".$strTable." DROP ".implode(", DROP ", $arColumns));
	}

	/**
	 * Функция для выборки метаданных пользовательского свойства.
	 *
	 * <p>Возвращает ассоциативный массив метаданных который можно передать в Update.</p>
	 * @param integer $ID идентификатор свойства
	 * @return array Если свойство не найдено, то возвращается false
	 * @static
	 */
	public static function GetByID($ID)
	{
		global $DB;
		static $arLabels = array("EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE");
		static $cache = array();

		if(!array_key_exists($ID, $cache))
		{
			$rsUserField = CUserTypeEntity::GetList(array(), array("ID" => intval($ID)));
			if($arUserField = $rsUserField->Fetch())
			{
				$rs = $DB->Query("SELECT * FROM b_user_field_lang WHERE USER_FIELD_ID = " . intval($ID));
				while($ar = $rs->Fetch())
				{
					foreach($arLabels as $label)
						$arUserField[$label][$ar["LANGUAGE_ID"]] = $ar[$label];
				}
				$cache[$ID] = $arUserField;
			}
			else
				$cache[$ID] = false;
		}
		return $cache[$ID];
	}

	/**
	 * Функция для выборки метаданных пользовательских свойств.
	 *
	 * <p>Возвращает CDBResult - выборку в зависимости от фильтра и сортировки.</p>
	 * <p>Параметр aSort по умолчанию имеет вид array("SORT"=>"ASC", "ID"=>"ASC").</p>
	 * <p>Если в aFilter передается LANG, то дополнительно выбираются языковые сообщения.</p>
	 * @param array $aSort ассоциативный массив сортировки (ID, ENTITY_ID, FIELD_NAME, SORT, USER_TYPE_ID)
	 * @param array $aFilter ассоциативный массив фильтра со строгим сообветствием (<b>равно</b>) (ID, ENTITY_ID, FIELD_NAME, USER_TYPE_ID, SORT, MULTIPLE, MANDATORY, SHOW_FILTER)
	 * @return CDBResult
	 * @static
	 */
	public static function GetList($aSort = array(), $aFilter = array())
	{
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_user_field !== false)
		{
			$cacheId = "b_user_type" . md5(serialize($aSort) . "." . serialize($aFilter));
			if($CACHE_MANAGER->Read(CACHED_b_user_field, $cacheId, "b_user_field"))
			{
				$arResult = $CACHE_MANAGER->Get($cacheId);
				$res = new CDBResult;
				$res->InitFromArray($arResult);
				$res = new CUserTypeEntity($res);
				return $res;
			}
		}

		$bLangJoin = false;
		$arFilter = array();
		foreach($aFilter as $key => $val)
		{
			if(is_array($val) || (string)$val == '')
				continue;

			$key = mb_strtoupper($key);
			$val = $DB->ForSql($val);

			switch($key)
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

		$arOrder = array();
		foreach($aSort as $key => $val)
		{
			$key = mb_strtoupper($key);
			$ord = (mb_strtoupper($val) <> "ASC" ? "DESC" : "ASC");
			switch($key)
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
		if(empty($arOrder))
		{
			$arOrder[] = "UF.SORT asc";
			$arOrder[] = "UF.ID asc";
		}
		DelDuplicateSort($arOrder);
		$sOrder = "\nORDER BY " . implode(", ", $arOrder);

		if(empty($arFilter))
			$sFilter = "";
		else
			$sFilter = "\nWHERE " . implode("\nAND ", $arFilter);

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

		if(CACHED_b_user_field === false)
		{
			$res = $DB->Query($strSql);
		}
		else
		{
			$arResult = array();
			$res = $DB->Query($strSql);
			while($ar = $res->Fetch())
				$arResult[] = $ar;

			/** @noinspection PhpUndefinedVariableInspection */
			$CACHE_MANAGER->Set($cacheId, $arResult);

			$res = new CDBResult;
			$res->InitFromArray($arResult);
		}

		return new CUserTypeEntity($res);
	}

	/**
	 * Функция проверки корректности значений метаданных пользовательских свойств.
	 *
	 * <p>Вызывается в методах Add и Update для проверки правильности введенных значений.</p>
	 * <p>Проверки:</p>
	 * <ul>
	 * <li>ENTITY_ID - обязательное
	 * <li>ENTITY_ID - не более 50-ти символов
	 * <li>ENTITY_ID - не должно содержать никаких символов кроме 0-9 A-Z и _
	 * <li>FIELD_NAME - обязательное
	 * <li>FIELD_NAME - не менее 4-х символов
	 * <li>FIELD_NAME - не более 50-ти символов
	 * <li>FIELD_NAME - не должно содержать никаких символов кроме 0-9 A-Z и _
	 * <li>FIELD_NAME - должно начинаться на UF_
	 * <li>USER_TYPE_ID - обязательное
	 * <li>USER_TYPE_ID - должен быть зарегистрирован
	 * </ul>
	 * <p>В случае ошибки ловите исключение приложения!</p>
	 * @param integer $ID - идентификатор свойства. 0 - для нового.
	 * @param array $arFields метаданные свойства
	 * @param bool $bCheckUserType
	 * @return boolean false - если хоть одна проверка не прошла.
	 */
	function CheckFields($ID, $arFields, $bCheckUserType = true)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $APPLICATION, $USER_FIELD_MANAGER;
		$aMsg = array();
		$ID = intval($ID);

		if(($ID <= 0 || array_key_exists("ENTITY_ID", $arFields)) && $arFields["ENTITY_ID"] == '')
			$aMsg[] = array("id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_MISSING"));
		if(array_key_exists("ENTITY_ID", $arFields))
		{
			if(mb_strlen($arFields["ENTITY_ID"]) > 50)
				$aMsg[] = array("id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_TOO_LONG1"));
			if(!preg_match('/^[0-9A-Z_]+$/', $arFields["ENTITY_ID"]))
				$aMsg[] = array("id" => "ENTITY_ID", "text" => GetMessage("USER_TYPE_ENTITY_ID_INVALID"));
		}

		if(($ID <= 0 || array_key_exists("FIELD_NAME", $arFields)) && $arFields["FIELD_NAME"] == '')
			$aMsg[] = array("id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_MISSING"));
		if(array_key_exists("FIELD_NAME", $arFields))
		{
			if(mb_strlen($arFields["FIELD_NAME"]) < 4)
				$aMsg[] = array("id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_TOO_SHORT"));
			if(mb_strlen($arFields["FIELD_NAME"]) > 50)
				$aMsg[] = array("id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_TOO_LONG1"));
			if(strncmp($arFields["FIELD_NAME"], "UF_", 3) !== 0)
				$aMsg[] = array("id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_NOT_UF"));
			if(!preg_match('/^[0-9A-Z_]+$/', $arFields["FIELD_NAME"]))
				$aMsg[] = array("id" => "FIELD_NAME", "text" => GetMessage("USER_TYPE_FIELD_NAME_INVALID"));
		}

		if(($ID <= 0 || array_key_exists("USER_TYPE_ID", $arFields)) && $arFields["USER_TYPE_ID"] == '')
			$aMsg[] = array("id" => "USER_TYPE_ID", "text" => GetMessage("USER_TYPE_USER_TYPE_ID_MISSING"));
		if(
			$bCheckUserType
			&& array_key_exists("USER_TYPE_ID", $arFields)
			&& !$USER_FIELD_MANAGER->GetUserType($arFields["USER_TYPE_ID"])
		)
			$aMsg[] = array("id" => "USER_TYPE_ID", "text" => GetMessage("USER_TYPE_USER_TYPE_ID_INVALID"));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	/**
	 * Функция добавляет пользовательское свойство.
	 *
	 * <p>Сначала вызывается метод экземпляра объекта CheckFields (т.е. $this->CheckFields($arFields) ).</p>
	 * <p>Если проверка прошла успешно, выполняется проверка на существование такого поля для данной сущности.</p>
	 * <p>Далее при необходимости создаются таблички вида <b>b_uts_[ENTITY_ID]</b> и <b>b_utm_[ENTITY_ID]</b>.</p>
	 * <p>После чего метаданные сохраняются в БД.</p>
	 * <p>И только после этого <b>изменяется стуктура таблицы b_uts_[ENTITY_ID]</b>.</p>
	 * <p>Массив arFields:</p>
	 * <ul>
	 * <li>ENTITY_ID - сущность
	 * <li>FIELD_NAME - фактически имя столбца в БД в котором будут храниться значения свойства.
	 * <li>USER_TYPE_ID - тип свойства
	 * <li>XML_ID - идентификатор для использования при импорте/экспорте
	 * <li>SORT - порядок сортировки (по умолчанию 100)
	 * <li>MULTIPLE - признак множественности Y/N (по умолчанию N)
	 * <li>MANDATORY - признак обязательности ввода значения Y/N (по умолчанию N)
	 * <li>SHOW_FILTER - показывать или нет в фильтре админ листа и какой тип использовать. см. ниже.
	 * <li>SHOW_IN_LIST - показывать или нет в админ листе (по умолчанию Y)
	 * <li>EDIT_IN_LIST - разрешать редактирование в формах, но не в API! (по умолчанию Y)
	 * <li>IS_SEARCHABLE - поле участвует в поиске (по умолчанию N)
	 * <li>SETTINGS - массив с настройками свойства зависимыми от типа свойства. Проходят "очистку" через обработчик типа PrepareSettings.
	 * <li>EDIT_FORM_LABEL - массив языковых сообщений вида array("ru"=>"привет", "en"=>"hello")
	 * <li>LIST_COLUMN_LABEL
	 * <li>LIST_FILTER_LABEL
	 * <li>ERROR_MESSAGE
	 * <li>HELP_MESSAGE
	 * </ul>
	 * <p>В случае ошибки ловите исключение приложения!</p>
	 * <p>Значения для SHOW_FILTER:</p>
	 * <ul>
	 * <li>N - не показывать
	 * <li>I - точное совпадение
	 * <li>E - маска
	 * <li>S - подстрока
	 * </ul>
	 * @param array $arFields метаданные нового свойства
	 * @param bool $bCheckUserType
	 * @return integer - иднтификатор добавленного свойства, false - если свойство не было добавлено.
	 */
	function Add($arFields, $bCheckUserType = true)
	{
		global $DB, $APPLICATION, $USER_FIELD_MANAGER, $CACHE_MANAGER;

		if(!$this->CheckFields(0, $arFields, $bCheckUserType))
			return false;

		$rs = CUserTypeEntity::GetList(array(), array(
			"ENTITY_ID" => $arFields["ENTITY_ID"],
			"FIELD_NAME" => $arFields["FIELD_NAME"],
		));

		if($rs->Fetch())
		{
			$aMsg = array();
			$aMsg[] = array(
				"id" => "FIELD_NAME",
				"text" => GetMessage("USER_TYPE_ADD_ALREADY_ERROR", array(
					"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
					"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
				)),
			);
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		unset($arFields["ID"]);
		if(!isset($arFields["SORT"]) || intval($arFields["SORT"]) <= 0)
			$arFields["SORT"] = 100;
		if(!isset($arFields["MULTIPLE"]) || $arFields["MULTIPLE"] !== "Y")
			$arFields["MULTIPLE"] = "N";
		if(!isset($arFields["MANDATORY"]) || $arFields["MANDATORY"] !== "Y")
			$arFields["MANDATORY"] = "N";
		$arFields["SHOW_FILTER"] = mb_substr($arFields["SHOW_FILTER"] ?? '', 0, 1);
		if($arFields["SHOW_FILTER"] == '' || mb_strpos("NIES", $arFields["SHOW_FILTER"]) === false)
			$arFields["SHOW_FILTER"] = "N";
		if(!isset($arFields["SHOW_IN_LIST"]) || $arFields["SHOW_IN_LIST"] !== "N")
			$arFields["SHOW_IN_LIST"] = "Y";
		if(!isset($arFields["EDIT_IN_LIST"]) || $arFields["EDIT_IN_LIST"] !== "N")
			$arFields["EDIT_IN_LIST"] = "Y";
		if(!isset($arFields["IS_SEARCHABLE"]) || $arFields["IS_SEARCHABLE"] !== "Y")
			$arFields["IS_SEARCHABLE"] = "N";

		if(!array_key_exists("SETTINGS", $arFields))
			$arFields["SETTINGS"] = array();
		$arFields["SETTINGS"] = serialize($USER_FIELD_MANAGER->PrepareSettings(0, $arFields, $bCheckUserType));

		/**
		 * events
		 * PROVIDE_STORAGE - use own uf subsystem to store data (uts/utm tables)
		 */
		$commonEventResult = array('PROVIDE_STORAGE' => true);

		foreach(GetModuleEvents("main", "OnBeforeUserTypeAdd", true) as $arEvent)
		{
			$eventResult = ExecuteModuleEventEx($arEvent, array(&$arFields));

			if($eventResult === false)
			{
				if($e = $APPLICATION->GetException())
				{
					return false;
				}

				$aMsg = array();
				$aMsg[] = array(
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_ADD_ERROR", array(
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					))
				);

				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);

				return false;
			}
			elseif(is_array($eventResult))
			{
				$commonEventResult = array_merge($commonEventResult, $eventResult);
			}
		}

		if(is_object($USER_FIELD_MANAGER))
			$USER_FIELD_MANAGER->CleanCache();

		if($commonEventResult['PROVIDE_STORAGE'])
		{
			if(!$this->CreatePropertyTables($arFields["ENTITY_ID"]))
				return false;

			$strType = $USER_FIELD_MANAGER->getUtsDBColumnType($arFields);

			if(!$strType)
			{
				$aMsg = array();
				$aMsg[] = array(
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_ADD_ERROR", array(
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					)),
				);
				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);
				return false;
			}

			$tableName = 'b_uts_' . mb_strtolower($arFields['ENTITY_ID']);
			$tableFields = $DB->GetTableFields($tableName);
			if (!array_key_exists($arFields['FIELD_NAME'], $tableFields))
			{
				$ddl = 'ALTER TABLE ' . $tableName . ' ADD ' . $arFields['FIELD_NAME'] . ' ' . $strType;
				if (!$DB->DDL($ddl, true, "FILE: " . __FILE__ . "<br>LINE: " . __LINE__))
				{
					$aMsg = array();
					$aMsg[] = array(
						"id" => "FIELD_NAME",
						"text" => GetMessage("USER_TYPE_ADD_ERROR", array(
							"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
							"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
						))
					);
					$e = new CAdminException($aMsg);
					$APPLICATION->ThrowException($e);
					return false;
				}
			}
		}

		if($ID = $DB->Add("b_user_field", $arFields, array("SETTINGS")))
		{
			if(CACHED_b_user_field !== false)
				$CACHE_MANAGER->CleanDir("b_user_field");

			$arLabels = array("EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE");
			$arLangs = array();
			foreach($arLabels as $label)
			{
				if(isset($arFields[$label]) && is_array($arFields[$label]))
				{
					foreach($arFields[$label] as $lang => $value)
					{
						$arLangs[$lang][$label] = $value;
					}
				}
			}

			foreach($arLangs as $lang => $arLangFields)
			{
				$arLangFields["USER_FIELD_ID"] = $ID;
				$arLangFields["LANGUAGE_ID"] = $lang;
				$arInsert = $DB->PrepareInsert("b_user_field_lang", $arLangFields);
				$DB->Query("INSERT INTO b_user_field_lang (".$arInsert[0].") VALUES (".$arInsert[1].")");
			}
		}

		// post event
		$arFields['ID'] = $ID;

		foreach(GetModuleEvents("main", "OnAfterUserTypeAdd", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($arFields));
		}

		return $ID;
	}

	/**
	 * Функция изменяет метаданные пользовательского свойства.
	 *
	 * <p>Надо сказать, что для скорейшего завершения разработки было решено пока не реализовывать
	 * такую же гибкость как в инфоблоках (обойдемся пока без alter'ов и прочего).</p>
	 * <p>Сначала вызывается метод экземпляра объекта CheckFields (т.е. $this->CheckFields($arFields) ).</p>
	 * <p>После чего метаданные сохраняются в БД.</p>
	 * <p>Массив arFields (только то что можно изменять):</p>
	 * <ul>
	 * <li>SORT - порядок сортировки
	 * <li>MANDATORY - признак обязательности ввода значения Y/N
	 * <li>SHOW_FILTER - признак показа в фильтре списка Y/N
	 * <li>SHOW_IN_LIST - признак показа в списке Y/N
	 * <li>EDIT_IN_LIST - разрешать редактирование поля в формах админки или нет Y/N
	 * <li>IS_SEARCHABLE - признак поиска Y/N
	 * <li>SETTINGS - массив с настройками свойства зависимыми от типа свойства. Проходят "очистку" через обработчик типа PrepareSettings.
	 * <li>EDIT_FORM_LABEL - массив языковых сообщений вида array("ru"=>"привет", "en"=>"hello")
	 * <li>LIST_COLUMN_LABEL
	 * <li>LIST_FILTER_LABEL
	 * <li>ERROR_MESSAGE
	 * <li>HELP_MESSAGE
	 * </ul>
	 * <p>В случае ошибки ловите исключение приложения!</p>
	 * @param integer $ID идентификатор свойства
	 * @param array $arFields новые метаданные свойства
	 * @return boolean - true в случае успешного обновления, false - в противном случае.
	 */
	function Update($ID, $arFields)
	{
		global $DB, $USER_FIELD_MANAGER, $CACHE_MANAGER, $APPLICATION;
		$ID = intval($ID);

		unset($arFields["ENTITY_ID"]);
		unset($arFields["FIELD_NAME"]);
		unset($arFields["USER_TYPE_ID"]);
		unset($arFields["MULTIPLE"]);

		if(!$this->CheckFields($ID, $arFields))
			return false;

		if(array_key_exists("SETTINGS", $arFields))
			$arFields["SETTINGS"] = serialize($USER_FIELD_MANAGER->PrepareSettings($ID, $arFields));
		if(array_key_exists("MANDATORY", $arFields) && $arFields["MANDATORY"] !== "Y")
			$arFields["MANDATORY"] = "N";
		if(array_key_exists("SHOW_FILTER", $arFields))
		{
			$arFields["SHOW_FILTER"] = mb_substr($arFields["SHOW_FILTER"], 0, 1);
			if(mb_strpos("NIES", $arFields["SHOW_FILTER"]) === false)
				$arFields["SHOW_FILTER"] = "N";
		}
		if(array_key_exists("SHOW_IN_LIST", $arFields) && $arFields["SHOW_IN_LIST"] !== "N")
			$arFields["SHOW_IN_LIST"] = "Y";
		if(array_key_exists("EDIT_IN_LIST", $arFields) && $arFields["EDIT_IN_LIST"] !== "N")
			$arFields["EDIT_IN_LIST"] = "Y";
		if(array_key_exists("IS_SEARCHABLE", $arFields) && $arFields["IS_SEARCHABLE"] !== "Y")
			$arFields["IS_SEARCHABLE"] = "N";

		// events
		foreach(GetModuleEvents("main", "OnBeforeUserTypeUpdate", true) as $arEvent)
		{
			if(ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
			{
				if($e = $APPLICATION->GetException())
				{
					return false;
				}

				$aMsg = array();
				$aMsg[] = array(
					"id" => "FIELD_NAME",
					"text" => GetMessage("USER_TYPE_UPDATE_ERROR", array(
						"#FIELD_NAME#" => htmlspecialcharsbx($arFields["FIELD_NAME"]),
						"#ENTITY_ID#" => htmlspecialcharsbx($arFields["ENTITY_ID"]),
					))
				);

				$e = new CAdminException($aMsg);
				$APPLICATION->ThrowException($e);

				return false;
			}
		}

		if(is_object($USER_FIELD_MANAGER))
			$USER_FIELD_MANAGER->CleanCache();

		$strUpdate = $DB->PrepareUpdate("b_user_field", $arFields);

		static $arLabels = array("EDIT_FORM_LABEL", "LIST_COLUMN_LABEL", "LIST_FILTER_LABEL", "ERROR_MESSAGE", "HELP_MESSAGE");
		$arLangs = array();
		foreach($arLabels as $label)
		{
			if(is_array($arFields[$label]))
			{
				foreach($arFields[$label] as $lang => $value)
				{
					$arLangs[$lang][$label] = $value;
				}
			}
		}

		if($strUpdate <> "" || !empty($arLangs))
		{
			if(CACHED_b_user_field !== false)
			{
				$CACHE_MANAGER->CleanDir("b_user_field");
			}

			if($strUpdate <> "")
			{
				$strSql = "UPDATE b_user_field SET " . $strUpdate . " WHERE ID = " . $ID;
				if(array_key_exists("SETTINGS", $arFields))
					$arBinds = array("SETTINGS" => $arFields["SETTINGS"]);
				else
					$arBinds = array();
				$DB->QueryBind($strSql, $arBinds);
			}

			if(!empty($arLangs))
			{
				$DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $ID);

				foreach($arLangs as $lang => $arLangFields)
				{
					$arLangFields["USER_FIELD_ID"] = $ID;
					$arLangFields["LANGUAGE_ID"] = $lang;
					$arInsert = $DB->PrepareInsert("b_user_field_lang", $arLangFields);
					$DB->Query("INSERT INTO b_user_field_lang (".$arInsert[0].") VALUES (".$arInsert[1].")");
					}
			}

			foreach(GetModuleEvents("main", "OnAfterUserTypeUpdate", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($arFields, $ID));
			}
		}

		return true;
	}

	/**
	 * Функция удаляет пользовательское свойство и все его значения.
	 *
	 * <p>Сначала удаляются метаданные свойства.</p>
	 * <p>Затем из таблички вида <b>b_utm_[ENTITY_ID]</b> удаляются все значения множественных свойств.</p>
	 * <p>После чего у таблички вида <b>b_uts_[ENTITY_ID]</b> дропается колонка.</p>
	 * <p>И если это было "последнее" свойство для сущности, то дропаются сами таблички хранившие значения.</p>
	 * @param integer $ID идентификатор свойства
	 * @return CDBResult - результат выполнения последнего запроса функции.
	 */
	function Delete($ID)
	{
		global $DB, $CACHE_MANAGER, $USER_FIELD_MANAGER, $APPLICATION;
		$ID = intval($ID);

		$rs = $this->GetList(array(), array("ID" => $ID));
		if($arField = $rs->Fetch())
		{
			/**
			 * events
			 * PROVIDE_STORAGE - use own uf subsystem to store data (uts/utm tables)
			 */
			$commonEventResult = array('PROVIDE_STORAGE' => true);

			foreach(GetModuleEvents("main", "OnBeforeUserTypeDelete", true) as $arEvent)
			{
				$eventResult = ExecuteModuleEventEx($arEvent, array(&$arField));

				if($eventResult === false)
				{
					if($e = $APPLICATION->GetException())
					{
						return false;
					}

					$aMsg = array();
					$aMsg[] = array(
						"id" => "FIELD_NAME",
						"text" => GetMessage("USER_TYPE_DELETE_ERROR", array(
							"#FIELD_NAME#" => htmlspecialcharsbx($arField["FIELD_NAME"]),
							"#ENTITY_ID#" => htmlspecialcharsbx($arField["ENTITY_ID"]),
						))
					);

					$e = new CAdminException($aMsg);
					$APPLICATION->ThrowException($e);

					return false;
				}
				elseif(is_array($eventResult))
				{
					$commonEventResult = array_merge($commonEventResult, $eventResult);
				}
			}

			if(is_object($USER_FIELD_MANAGER))
				$USER_FIELD_MANAGER->CleanCache();

			$arType = $USER_FIELD_MANAGER->GetUserType($arField["USER_TYPE_ID"]);
			//We need special handling of file type properties
			if($arType)
			{
				if($arType["BASE_TYPE"] == "file" && $commonEventResult['PROVIDE_STORAGE'])
				{
					// only if we store values
					if($arField["MULTIPLE"] == "Y")
						$strSql = "SELECT VALUE_INT AS VALUE FROM b_utm_".mb_strtolower($arField["ENTITY_ID"]) . " WHERE FIELD_ID=" . $arField["ID"];
					else
						$strSql = "SELECT ".$arField["FIELD_NAME"]." AS VALUE FROM b_uts_".mb_strtolower($arField["ENTITY_ID"]);
					$rsFile = $DB->Query($strSql);
					while($arFile = $rsFile->Fetch())
					{
						CFile::Delete($arFile["VALUE"]);
					}
				}
				elseif($arType["BASE_TYPE"] == "enum")
				{
					$obEnum = new CUserFieldEnum;
					$obEnum->DeleteFieldEnum($arField["ID"]);
				}
			}

			if(CACHED_b_user_field !== false) $CACHE_MANAGER->CleanDir("b_user_field");
			$rs = $DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $ID);
			if($rs)
				$rs = $DB->Query("DELETE FROM b_user_field WHERE ID = " . $ID);

			if($rs && $commonEventResult['PROVIDE_STORAGE'])
			{
				// only if we store values
				$rs = $this->GetList(array(), array("ENTITY_ID" => $arField["ENTITY_ID"]));
				if($rs->Fetch()) // more than one
				{
					foreach($this->DropColumnSQL("b_uts_".mb_strtolower($arField["ENTITY_ID"]), array($arField["FIELD_NAME"])) as $strSql)
						$DB->Query($strSql);
					$rs = $DB->Query("DELETE FROM b_utm_".mb_strtolower($arField["ENTITY_ID"]) . " WHERE FIELD_ID = '" . $ID . "'");
				}
				else
				{
					$DB->Query("DROP TABLE IF EXISTS b_uts_".mb_strtolower($arField["ENTITY_ID"]));
					$rs = $DB->Query("DROP TABLE IF EXISTS b_utm_".mb_strtolower($arField["ENTITY_ID"]));
				}
			}

			foreach(GetModuleEvents("main", "OnAfterUserTypeDelete", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($arField, $ID));
			}
		}
		return $rs;
	}

	/**
	 * Функция удаляет ВСЕ пользовательские свойства сущности.
	 *
	 * <p>Сначала удаляются метаданные свойств.</p>
	 * <p>Можно вызвать при удалении инфоблока например.</p>
	 * <p>Затем таблички вида <b>b_utm_[ENTITY_ID]</b> и <b>b_uts_[ENTITY_ID]</b> дропаются.</p>
	 * @param string $entity_id идентификатор сущности
	 * @return CDBResult - результат выполнения последнего запроса функции.
	 */
	function DropEntity($entity_id)
	{
		global $DB, $CACHE_MANAGER, $USER_FIELD_MANAGER;
		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);

		$rs = true;
		$rsFields = $this->GetList(array(), array("ENTITY_ID" => $entity_id));
		//We need special handling of file and enum type properties
		while($arField = $rsFields->Fetch())
		{
			$arType = $USER_FIELD_MANAGER->GetUserType($arField["USER_TYPE_ID"]);
			if($arType && ($arType["BASE_TYPE"] == "file" || $arType["BASE_TYPE"] == "enum"))
			{
				$this->Delete($arField["ID"]);
			}
		}

		$bDropTable = false;
		$rsFields = $this->GetList(array(), array("ENTITY_ID" => $entity_id));
		while($arField = $rsFields->Fetch())
		{
			$bDropTable = true;
			$DB->Query("DELETE FROM b_user_field_lang WHERE USER_FIELD_ID = " . $arField["ID"]);
			$rs = $DB->Query("DELETE FROM b_user_field WHERE ID = " . $arField["ID"]);
		}

		if($bDropTable)
		{
			$DB->Query("DROP SEQUENCE IF EXISTS SQ_B_UTM_" . $entity_id, true);
			$DB->Query("DROP TABLE IF EXISTS b_uts_".mb_strtolower($entity_id), true);
			$rs = $DB->Query("DROP TABLE IF EXISTS b_utm_".mb_strtolower($entity_id), true);
		}

		if(CACHED_b_user_field !== false)
			$CACHE_MANAGER->CleanDir("b_user_field");

		if(is_object($USER_FIELD_MANAGER))
			$USER_FIELD_MANAGER->CleanCache();

		return $rs;
	}

	/**
	 * Функция Fetch.
	 *
	 * <p>Десериализует поле SETTINGS.</p>
	 * @return array возвращает false в случае последней записи выборки.
	 */
	function Fetch()
	{
		$res = parent::Fetch();
		if($res && $res["SETTINGS"] <> '')
		{
			$res["SETTINGS"] = unserialize($res["SETTINGS"], ['allowed_classes' => false]);
		}
		return $res;
	}
}

class CUserTypeEntity extends CAllUserTypeEntity
{
}
