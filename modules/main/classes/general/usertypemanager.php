<?php

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\HtmlFilter;
use Bitrix\Main\UserField\Types\BaseType;
use Bitrix\Main\UserField\Types\DateTimeType;

IncludeModuleLangFile(__FILE__);

/**
 * Данный класс фактически является интерфейсной прослойкой между значениями
 * пользовательских свойств и сущностью к которой они привязаны.
 * @package usertype
 * @subpackage classes
 */
class CUserTypeManager
{
	const BASE_TYPE_INT = "int";
	const BASE_TYPE_FILE = "file";
	const BASE_TYPE_ENUM = "enum";
	const BASE_TYPE_DOUBLE = "double";
	const BASE_TYPE_DATETIME = "datetime";
	const BASE_TYPE_STRING = "string";
	/**
	 * Хранит все типы пользовательских свойств.
	 *
	 * <p>Инициализируется при первом вызове метода GetUserType.</p>
	 * @var array
	 */
	public $arUserTypes = false;
	public $arFieldsCache = [];
	public $arRightsCache = [];
	/**
	 * @var null|array Stores relations of usertype ENTITY_ID to ORM entities. Aggregated by event main:onUserTypeEntityOrmMap.
	 * @see CUserTypeManager::getEntityList()
	 */
	protected $entityList = null;

	public function CleanCache()
	{
		$this->arFieldsCache = [];
		$this->arUserTypes = false;
	}

	/**
	 * Функция возвращает метаданные типа.
	 *
	 * <p>Если это первый вызов функции, то выполняется системное событие OnUserTypeBuildList (main).
	 * Зарегистрированные обработчики должны вернуть даные описания типа. В данном случае действует правило -
	 * кто последний тот и папа. (на случай если один тип зарегились обрабатывать "несколько" классов)</p>
	 * <p>Без параметров функция возвращает полный список типов.<p>
	 * <p>При заданном user_type_id - возвращает массив если такой тип зарегистрирован и false если нет.<p>
	 * @param string|bool $user_type_id необязательный. Идентификатор типа свойства.
	 * @return array|boolean
	 */
	public function GetUserType($user_type_id = false)
	{
		if (!is_array($this->arUserTypes))
		{
			$this->arUserTypes = [];
			foreach (GetModuleEvents("main", "OnUserTypeBuildList", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				$this->arUserTypes[$res["USER_TYPE_ID"]] = $res;
			}
		}
		if ($user_type_id !== false)
		{
			if (array_key_exists($user_type_id, $this->arUserTypes))
			{
				return $this->arUserTypes[$user_type_id];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $this->arUserTypes;
		}
	}

	public function GetDBColumnType($arUserField)
	{
		if ($arType = $this->GetUserType($arUserField["USER_TYPE_ID"]))
		{
			if (is_callable([$arType["CLASS_NAME"], "getdbcolumntype"]))
			{
				return call_user_func_array([$arType["CLASS_NAME"], "getdbcolumntype"], [$arUserField]);
			}
		}
		return "";
	}

	public function getUtsDBColumnType($arUserField)
	{
		if ($arUserField['MULTIPLE'] == 'Y')
		{
			$sqlHelper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
			return $sqlHelper->getColumnTypeByField(new Entity\TextField('TMP'));
		}
		else
		{
			return $this->GetDBColumnType($arUserField);
		}
	}

	public function getUtmDBColumnType($arUserField)
	{
		return $this->GetDBColumnType($arUserField);
	}

	public function PrepareSettings($ID, $arUserField, $bCheckUserType = true)
	{
		$user_type_id = $arUserField["USER_TYPE_ID"];
		if ($ID > 0)
		{
			$rsUserType = CUserTypeEntity::GetList([], ["ID" => $ID]);
			$arUserType = $rsUserType->Fetch();
			if ($arUserType)
			{
				$user_type_id = $arUserType["USER_TYPE_ID"];
				$arUserField += $arUserType;
			}
		}

		if (!$bCheckUserType)
		{
			if (!isset($arUserField["SETTINGS"]))
			{
				return [];
			}

			if (!is_array($arUserField["SETTINGS"]))
			{
				return [];
			}

			if (empty($arUserField["SETTINGS"]))
			{
				return [];
			}
		}

		if ($arType = $this->GetUserType($user_type_id))
		{
			if (is_callable([$arType["CLASS_NAME"], "preparesettings"]))
			{
				return call_user_func_array([$arType["CLASS_NAME"], "preparesettings"], [$arUserField]);
			}
		}
		else
		{
			return [];
		}
		return null;
	}

	public function getCustomData(array $userField, int $entityValueId): array
	{
		$hasCustomData =
			isset($userField['USER_TYPE']['CLASS_NAME'])
			&& is_callable([$userField['USER_TYPE']['CLASS_NAME'], 'getCustomData']);

		$customData = [];
		if ($hasCustomData)
		{
			$customData = call_user_func_array(
				[$userField['USER_TYPE']['CLASS_NAME'], 'getCustomData'],
				[$userField, $entityValueId]
			);
		}

		return is_array($customData) ? $customData : [];
	}

	public function OnEntityDelete($entity_id)
	{
		$obUserField = new CUserTypeEntity;
		return $obUserField->DropEntity($entity_id);
	}

	/**
	 * Функция возвращает метаданные полей определеных для сущности.
	 *
	 * <p>Важно! В $arUserField добалено поле ENTITY_VALUE_ID - это идентификатор экземпляра сущности
	 * позволяющий отделить новые записи от старых и соответсвенно использовать значения по умолчанию.</p>
	 */
	public function GetUserFields($entity_id, $value_id = 0, $LANG = false, $user_id = false, $selectFields = null)
	{
		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);
		$value_id = intval($value_id);
		$cacheId = $entity_id . "." . $LANG . '.' . (int)$user_id;

		global $DB;

		$result = [];
		if (!array_key_exists($cacheId, $this->arFieldsCache))
		{
			$arFilter = ["ENTITY_ID" => $entity_id];
			if ($LANG)
			{
				$arFilter["LANG"] = $LANG;
			}
			$rs = CUserTypeEntity::GetList([], $arFilter);
			while ($arUserField = $rs->Fetch())
			{
				if ($arType = $this->GetUserType($arUserField["USER_TYPE_ID"]))
				{
					if ($user_id !== 0 && is_callable([$arType["CLASS_NAME"], "checkpermission"]))
					{
						if (!call_user_func_array([$arType["CLASS_NAME"], "checkpermission"], [$arUserField, $user_id]))
						{
							continue;
						}
					}
					$arUserField["USER_TYPE"] = $arType;
					$arUserField["VALUE"] = false;
					if (!is_array($arUserField["SETTINGS"]) || empty($arUserField["SETTINGS"]))
					{
						$arUserField["SETTINGS"] = $this->PrepareSettings(0, $arUserField);
					}
					$result[$arUserField["FIELD_NAME"]] = $arUserField;
				}
			}
			$this->arFieldsCache[$cacheId] = $result;
		}
		else
		{
			$result = $this->arFieldsCache[$cacheId];
		}

		if (!empty($result) && $value_id > 0)
		{
			$valuesGottenByEvent = $this->getUserFieldValuesByEvent($result, $entity_id, $value_id);

			if ($selectFields !== null)
			{
				$selectFields = array_flip($selectFields);
			}

			$select = [];
			foreach ($result as $fieldName => $arUserField)
			{
				$result[$fieldName]["ENTITY_VALUE_ID"] = $value_id;

				if (is_array($valuesGottenByEvent))
				{
					$result[$fieldName]["VALUE"] = array_key_exists($fieldName, $valuesGottenByEvent) ? $valuesGottenByEvent[$fieldName] : $result[$fieldName]["VALUE"];
					$result[$fieldName]["CUSTOM_DATA"] = $this->getCustomData($result[$fieldName], $value_id);
				}
				elseif ($selectFields === null || isset($selectFields[$fieldName]))
				{
					if ($arUserField["MULTIPLE"] == "N"
						&& is_array($arUserField["USER_TYPE"])
						&& array_key_exists("CLASS_NAME", $arUserField["USER_TYPE"])
						&& is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "FormatField"]))
					{
						$select[] = call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "FormatField"], [$arUserField, $fieldName]) . " " . $fieldName;
					}
					else
					{
						$select[] = $fieldName;
					}
				}
			}

			if (is_array($valuesGottenByEvent))
			{
				return $result;
			}

			if (!empty($select))
			{
				$rs = $DB->Query("SELECT " . implode(', ', $select) . " FROM b_uts_" . mb_strtolower($entity_id) . " WHERE VALUE_ID = " . $value_id);

				if ($ar = $rs->Fetch())
				{
					foreach ($ar as $key => $value)
					{
						if ($result[$key]["MULTIPLE"] == "Y")
						{
							if (mb_substr($value, 0, 1) !== 'a' && $value > 0)
							{
								$value = $this->LoadMultipleValues($result[$key], $value);
							}
							else
							{
								$value = unserialize($value, ['allowed_classes' => false]);
							}
						}
						$result[$key]["VALUE"] = $this->OnAfterFetch($result[$key], $value);
					}
				}
			}

			foreach ($result as $fieldName => $userField)
			{
				$result[$fieldName]["CUSTOM_DATA"] = $this->getCustomData($userField, $value_id);
			}
		}

		return $result;
	}

	/**
	 * Replacement for getUserFields, if you are already have fetched old data
	 *
	 * @param      $entity_id
	 * @param      $readyData
	 * @param bool $LANG
	 * @param bool $user_id
	 * @param string $primaryIdName
	 *
	 * @return array
	 */
	public function getUserFieldsWithReadyData($entity_id, $readyData, $LANG = false, $user_id = false, $primaryIdName = 'VALUE_ID')
	{
		if ($readyData === null)
		{
			return $this->GetUserFields($entity_id, null, $LANG, $user_id);
		}

		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);
		$cacheId = $entity_id . "." . $LANG . '.' . (int)$user_id;

		//global $DB;

		$result = [];
		if (!array_key_exists($cacheId, $this->arFieldsCache))
		{
			$arFilter = ["ENTITY_ID" => $entity_id];
			if ($LANG)
			{
				$arFilter["LANG"] = $LANG;
			}

			$rs = call_user_func_array(['CUserTypeEntity', 'GetList'], [[], $arFilter]);
			while ($arUserField = $rs->Fetch())
			{
				if ($arType = $this->GetUserType($arUserField["USER_TYPE_ID"]))
				{
					if ($user_id !== 0 && is_callable([$arType["CLASS_NAME"], "checkpermission"]))
					{
						if (!call_user_func_array([$arType["CLASS_NAME"], "checkpermission"], [$arUserField, $user_id]))
						{
							continue;
						}
					}
					$arUserField["USER_TYPE"] = $arType;
					$arUserField["VALUE"] = false;
					if (!is_array($arUserField["SETTINGS"]) || empty($arUserField["SETTINGS"]))
					{
						$arUserField["SETTINGS"] = $this->PrepareSettings(0, $arUserField);
					}
					$result[$arUserField["FIELD_NAME"]] = $arUserField;
				}
			}
			$this->arFieldsCache[$cacheId] = $result;
		}
		else
		{
			$result = $this->arFieldsCache[$cacheId];
		}

		foreach ($readyData as $key => $value)
		{
			if (array_key_exists($key, $result))
			{
				if ($result[$key]["MULTIPLE"] == "Y" && !is_array($value))
				{
					$value = unserialize($value, ['allowed_classes' => false]);
				}

				$result[$key]["VALUE"] = $this->OnAfterFetch($result[$key], $value);
				$result[$key]["ENTITY_VALUE_ID"] = $readyData[$primaryIdName];
			}
		}

		return $result;
	}

	public function GetUserFieldValue($entity_id, $field_id, $value_id, $LANG = false)
	{
		global $DB;
		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);
		$field_id = preg_replace("/[^0-9A-Z_]+/", "", $field_id);
		$value_id = intval($value_id);
		$strTableName = "b_uts_" . mb_strtolower($entity_id);
		$result = false;

		$arFilter = [
			"ENTITY_ID" => $entity_id,
			"FIELD_NAME" => $field_id,
		];
		if ($LANG)
		{
			$arFilter["LANG"] = $LANG;
		}
		$rs = CUserTypeEntity::GetList([], $arFilter);
		if ($arUserField = $rs->Fetch())
		{
			$values = $this->getUserFieldValuesByEvent([$arUserField['FIELD_NAME'] => $arUserField], $entity_id, $value_id);
			if (is_array($values))
			{
				return $values[$arUserField['FIELD_NAME']];
			}
			$arUserField["USER_TYPE"] = $this->GetUserType($arUserField["USER_TYPE_ID"]);
			$arTableFields = $DB->GetTableFields($strTableName);
			if (array_key_exists($field_id, $arTableFields))
			{
				$simpleFormat = true;
				$select = "";
				if ($arUserField["MULTIPLE"] == "N")
				{
					if ($arType = $arUserField["USER_TYPE"])
					{
						if (is_callable([$arType["CLASS_NAME"], "FormatField"]))
						{
							$select = call_user_func_array([$arType["CLASS_NAME"], "FormatField"], [$arUserField, $field_id]);
							$simpleFormat = false;
						}
					}
				}
				if ($simpleFormat)
				{
					$select = $field_id;
				}

				$rs = $DB->Query("SELECT " . $select . " " . $DB->quote('VALUE') . " FROM " . $strTableName . " WHERE VALUE_ID = " . $value_id);
				if ($ar = $rs->Fetch())
				{
					if ($arUserField["MULTIPLE"] == "Y")
					{
						$result = $this->OnAfterFetch($arUserField, unserialize($ar["VALUE"], ['allowed_classes' => false]));
					}
					else
					{
						$result = $this->OnAfterFetch($arUserField, $ar["VALUE"]);
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Aggregates entity map by event.
	 * @return array [ENTITY_ID => 'SomeTable']
	 */
	public function getEntityList()
	{
		if ($this->entityList === null)
		{
			$event = new \Bitrix\Main\Event('main', 'onUserTypeEntityOrmMap');
			$event->send();

			foreach ($event->getResults() as $eventResult)
			{
				if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
				{
					$result = $eventResult->getParameters(); // [ENTITY_ID => 'SomeTable']
					foreach ($result as $entityId => $entityClass)
					{
						if (mb_substr($entityClass, 0, 1) !== '\\')
						{
							$entityClass = '\\' . $entityClass;
						}

						$this->entityList[$entityId] = $entityClass;
					}
				}
			}
		}

		return $this->entityList;
	}

	public function OnAfterFetch($arUserField, $result)
	{
		if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "onafterfetch"]))
		{
			if ($arUserField["MULTIPLE"] == "Y")
			{
				if (is_array($result))
				{
					$resultCopy = $result;
					$result = [];
					foreach ($resultCopy as $value)
					{
						$convertedValue = call_user_func_array(
							[$arUserField["USER_TYPE"]["CLASS_NAME"], "onafterfetch"],
							[
								$arUserField,
								[
									"VALUE" => $value,
								],
							]
						);
						if ($convertedValue !== null)
						{
							$result[] = $convertedValue;
						}
					}
				}
			}
			else
			{
				$result = call_user_func_array(
					[$arUserField["USER_TYPE"]["CLASS_NAME"], "onafterfetch"],
					[
						$arUserField,
						[
							"VALUE" => $result,
						],
					]
				);
			}
		}
		return $result;
	}

	public function LoadMultipleValues($arUserField, $valueId)
	{
		global $DB;
		$result = [];

		$rs = $DB->Query("
			SELECT *
			FROM b_utm_" . mb_strtolower($arUserField["ENTITY_ID"]) . "
			WHERE VALUE_ID = " . intval($valueId) . "
			AND FIELD_ID = " . $arUserField["ID"] . "
		");
		while ($ar = $rs->Fetch())
		{
			if ($arUserField["USER_TYPE"]["USER_TYPE_ID"] == "date")
			{
				$result[] = mb_substr($ar["VALUE_DATE"], 0, 10);
			}
			else
			{
				switch ($arUserField["USER_TYPE"]["BASE_TYPE"])
				{
					case "int":
					case "file":
					case "enum":
						$result[] = $ar["VALUE_INT"];
						break;
					case "double":
						$result[] = $ar["VALUE_DOUBLE"];
						break;
					case "datetime":
						$result[] = $ar["VALUE_DATE"];
						break;
					default:
						$result[] = $ar["VALUE"];
				}
			}
		}
		return $result;
	}

	public function EditFormTab($entity_id)
	{
		return [
			"DIV" => "user_fields_tab",
			"TAB" => GetMessage("USER_TYPE_EDIT_TAB"),
			"ICON" => "none",
			"TITLE" => GetMessage("USER_TYPE_EDIT_TAB_TITLE"),
		];
	}

	public function EditFormShowTab($entity_id, $bVarsFromForm, $ID)
	{
		global $APPLICATION;

		if ($this->GetRights($entity_id) >= "W")
		{
			echo "<tr colspan=\"2\"><td align=\"left\"><a href=\"/bitrix/admin/userfield_edit.php?lang=" . LANG . "&ENTITY_ID=" . urlencode($entity_id) . "&back_url=" . urlencode($APPLICATION->GetCurPageParam("", ["bxpublic"]) . "&tabControl_active_tab=user_fields_tab") . "\">" . GetMessage("USER_TYPE_EDIT_TAB_HREF") . "</a></td></tr>";
		}

		$arUserFields = $this->GetUserFields($entity_id, $ID, LANGUAGE_ID);
		if (!empty($arUserFields))
		{
			foreach ($arUserFields as $FIELD_NAME => $arUserField)
			{
				$arUserField["VALUE_ID"] = intval($ID);
				echo $this->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME], $arUserField);
			}
		}
	}

	public function EditFormAddFields($entity_id, &$arFields, array $options = null)
	{
		if (!is_array($options))
		{
			$options = [];
		}

		if (!is_array($arFields))
		{
			$arFields = [];
		}

		$files = $options['FILES'] ?? $_FILES;
		$form = isset($options['FORM']) && is_array($options['FORM']) ? $options['FORM'] : $GLOBALS;

		$arUserFields = $this->GetUserFields($entity_id);
		foreach ($arUserFields as $arUserField)
		{
			$fieldName = $arUserField['FIELD_NAME'];
			if ($arUserField["EDIT_IN_LIST"] == "Y")
			{
				if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "file")
				{
					if (isset($files[$fieldName]))
					{
						if (is_array($files[$fieldName]["name"]))
						{
							$arFields[$fieldName] = [];
							foreach ($files[$fieldName]["name"] as $key => $value)
							{
								$old_id = $form[$fieldName . "_old_id"][$key] ?? null;
								$arFields[$fieldName][$key] = [
									"name" => $files[$fieldName]["name"][$key],
									"type" => $files[$fieldName]["type"][$key],
									"tmp_name" => $files[$fieldName]["tmp_name"][$key],
									"error" => $files[$fieldName]["error"][$key],
									"size" => $files[$fieldName]["size"][$key],
									"del" =>
										isset($form[$fieldName . "_del"])
										&& is_array($form[$fieldName . "_del"]) &&
										(in_array($old_id, $form[$fieldName . "_del"]) ||
											(
												array_key_exists($key, $form[$fieldName . "_del"]) &&
												$form[$fieldName . "_del"][$key] == "Y"
											)
										),
									"old_id" => $old_id,
								];
							}
						}
						else
						{
							$arFields[$fieldName] = $files[$fieldName];
							$arFields[$fieldName]["del"] = $form[$fieldName . "_del"] ?? '';
							$arFields[$fieldName]["old_id"] = $form[$fieldName . "_old_id"] ?? '';
						}
					}
					else
					{
						if (isset($form[$fieldName]))
						{
							if (!is_array($form[$fieldName]))
							{
								if (intval($form[$fieldName]) > 0)
								{
									$arFields[$fieldName] = intval($form[$fieldName]);
								}
							}
							else
							{
								$fields = [];
								foreach ($form[$fieldName] as $val)
								{
									if (intval($val) > 0)
									{
										$fields[] = intval($val);
									}
								}
								$arFields[$fieldName] = $fields;
							}
						}
					}
				}
				else
				{
					if (isset($files[$fieldName]))
					{
						$arFile = [];
						CFile::ConvertFilesToPost($files[$fieldName], $arFile);

						if (isset($form[$fieldName]))
						{
							if ($arUserField["MULTIPLE"] == "Y")
							{
								foreach ($form[$fieldName] as $key => $value)
								{
									$arFields[$fieldName][$key] = array_merge($value, $arFile[$key]);
								}
							}
							else
							{
								$arFields[$fieldName] = array_merge($form[$fieldName], $arFile);
							}
						}
						else
						{
							$arFields[$fieldName] = $arFile;
						}
					}
					else
					{
						if (isset($form[$fieldName]))
						{
							$arFields[$fieldName] = $form[$fieldName];
						}
					}
				}
			}
		}
	}

	/**
	 * Add field for filter.
	 * @param int $entityId Entity id.
	 * @param array $arFilterFields Array for fill.
	 */
	public function AdminListAddFilterFields($entityId, &$arFilterFields)
	{
		$arUserFields = $this->GetUserFields($entityId);
		foreach ($arUserFields as $fieldName => $arUserField)
		{
			if ($arUserField['SHOW_FILTER'] != 'N' && $arUserField['USER_TYPE']['BASE_TYPE'] != 'file')
			{
				$arFilterFields[] = 'find_' . $fieldName;
				if ($arUserField['USER_TYPE']['BASE_TYPE'] == 'datetime')
				{
					$arFilterFields[] = 'find_' . $fieldName . '_from';
					$arFilterFields[] = 'find_' . $fieldName . '_to';
				}
			}
		}
	}

	public function AdminListAddFilterFieldsV2($entityId, &$arFilterFields)
	{
		$arUserFields = $this->GetUserFields($entityId, 0, $GLOBALS["lang"]);
		foreach ($arUserFields as $fieldName => $arUserField)
		{
			if ($arUserField['SHOW_FILTER'] != 'N' && $arUserField['USER_TYPE']['BASE_TYPE'] != 'file')
			{
				if (is_callable([$arUserField['USER_TYPE']['CLASS_NAME'], 'GetFilterData']))
				{
					$arFilterFields[] = call_user_func_array(
						[$arUserField['USER_TYPE']['CLASS_NAME'], 'GetFilterData'],
						[
							$arUserField,
							[
								'ID' => $fieldName,
								'NAME' => $arUserField['LIST_FILTER_LABEL'] ?: $arUserField['FIELD_NAME'],
							],
						]
					);
				}
			}
		}
	}

	public function IsNotEmpty($value)
	{
		if (is_array($value))
		{
			foreach ($value as $v)
			{
				if ((string)$v <> '')
				{
					return true;
				}
			}

			return false;
		}
		else
		{
			if ((string)$value <> '')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Add value for filter.
	 * @param int $entityId Entity id.
	 * @param array $arFilter Array for fill.
	 */
	public function AdminListAddFilter($entityId, &$arFilter)
	{
		$arUserFields = $this->GetUserFields($entityId);
		foreach ($arUserFields as $fieldName => $arUserField)
		{
			if (
				$arUserField['SHOW_FILTER'] != 'N' &&
				$arUserField['USER_TYPE']['BASE_TYPE'] == 'datetime'
			)
			{
				$value1 = $GLOBALS['find_' . $fieldName . '_from'];
				$value2 = $GLOBALS['find_' . $fieldName . '_to'];
				if ($this->IsNotEmpty($value1) && \Bitrix\Main\Type\Date::isCorrect($value1))
				{
					$date = new \Bitrix\Main\Type\Date($value1);
					$arFilter['>=' . $fieldName] = $date;
				}
				if ($this->IsNotEmpty($value2) && \Bitrix\Main\Type\Date::isCorrect($value2))
				{
					$date = new \Bitrix\Main\Type\Date($value2);
					if ($arUserField['USER_TYPE_ID'] != 'date')
					{
						$date->add('+1 day');
					}
					$arFilter['<=' . $fieldName] = $date;
				}
				continue;
			}
			else
			{
				$value = $GLOBALS['find_' . $fieldName] ?? null;
			}
			if (
				$arUserField['SHOW_FILTER'] != 'N'
				&& $arUserField['USER_TYPE']['BASE_TYPE'] != 'file'
				&& $this->IsNotEmpty($value)
			)
			{
				if ($arUserField['SHOW_FILTER'] == 'I')
				{
					$arFilter['=' . $fieldName] = $value;
				}
				elseif ($arUserField['SHOW_FILTER'] == 'S')
				{
					$arFilter['%' . $fieldName] = $value;
				}
				else
				{
					$arFilter[$fieldName] = $value;
				}
			}
		}
	}

	public function AdminListAddFilterV2($entityId, &$arFilter, $filterId, $filterFields)
	{
		$filterOption = new Bitrix\Main\UI\Filter\Options($filterId);
		$filterData = $filterOption->getFilter($filterFields);

		$arUserFields = $this->GetUserFields($entityId);
		foreach ($arUserFields as $fieldName => $arUserField)
		{
			if ($arUserField['SHOW_FILTER'] != 'N' && $arUserField['USER_TYPE']['BASE_TYPE'] == 'datetime')
			{
				$value1 = $filterData[$fieldName . '_from'] ?? '';
				$value2 = $filterData[$fieldName . '_to'] ?? '';
				if ($this->IsNotEmpty($value1) && \Bitrix\Main\Type\Date::isCorrect($value1))
				{
					$date = new \Bitrix\Main\Type\Date($value1);
					$arFilter['>=' . $fieldName] = $date;
				}
				if ($this->IsNotEmpty($value2) && \Bitrix\Main\Type\Date::isCorrect($value2))
				{
					$date = new \Bitrix\Main\Type\Date($value2);
					if ($arUserField['USER_TYPE_ID'] != 'date')
					{
						$date->add('+1 day');
					}
					$arFilter['<=' . $fieldName] = $date;
				}
				continue;
			}
			elseif ($arUserField['SHOW_FILTER'] != 'N' && $arUserField['USER_TYPE']['BASE_TYPE'] == 'int')
			{
				switch ($arUserField['USER_TYPE_ID'])
				{
					case 'boolean':
						if (isset($filterData[$fieldName]) && $filterData[$fieldName] === 'Y')
						{
							$filterData[$fieldName] = 1;
						}
						if (isset($filterData[$fieldName]) && $filterData[$fieldName] === 'N')
						{
							$filterData[$fieldName] = 0;
						}
						$value = $filterData[$fieldName] ?? null;
						break;
					default:
						$value = $filterData[$fieldName] ?? null;
				}
			}
			else
			{
				$value = $filterData[$fieldName] ?? null;
			}
			if (
				$arUserField['SHOW_FILTER'] != 'N'
				&& $arUserField['USER_TYPE']['BASE_TYPE'] != 'file'
				&& $this->IsNotEmpty($value)
			)
			{
				if ($arUserField['SHOW_FILTER'] == 'I')
				{
					unset($arFilter[$fieldName]);
					$arFilter['=' . $fieldName] = $value;
				}
				elseif ($arUserField['SHOW_FILTER'] == 'S')
				{
					unset($arFilter[$fieldName]);
					$arFilter['%' . $fieldName] = $value;
				}
				else
				{
					$arFilter[$fieldName] = $value;
				}
			}
		}
	}

	public function AdminListPrepareFields($entity_id, &$arFields)
	{
		$arUserFields = $this->GetUserFields($entity_id);
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField["EDIT_IN_LIST"] != "Y")
			{
				unset($arFields[$FIELD_NAME]);
			}
		}
	}

	public function AdminListAddHeaders($entity_id, &$arHeaders)
	{
		$arUserFields = $this->GetUserFields($entity_id, 0, $GLOBALS["lang"]);
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField["SHOW_IN_LIST"] == "Y")
			{
				$arHeaders[] = [
					"id" => $FIELD_NAME,
					"content" => htmlspecialcharsbx($arUserField["LIST_COLUMN_LABEL"] ?: $arUserField["FIELD_NAME"]),
					"sort" => $arUserField["MULTIPLE"] == "N" ? $FIELD_NAME : false,
				];
			}
		}
	}

	public function AddUserFields($entity_id, $arRes, &$row)
	{
		$arUserFields = $this->GetUserFields($entity_id);
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField["SHOW_IN_LIST"] == "Y" && array_key_exists($FIELD_NAME, $arRes))
			{
				$this->AddUserField($arUserField, $arRes[$FIELD_NAME], $row);
			}
		}
	}

	public function AddFindFields($entity_id, &$arFindFields)
	{
		$arUserFields = $this->GetUserFields($entity_id, 0, $GLOBALS["lang"]);
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField["SHOW_FILTER"] != "N" && $arUserField["USER_TYPE"]["BASE_TYPE"] != "file")
			{
				if ($arUserField["USER_TYPE"] && is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getfilterhtml"]))
				{
					if ($arUserField["LIST_FILTER_LABEL"])
					{
						$arFindFields[$FIELD_NAME] = htmlspecialcharsbx($arUserField["LIST_FILTER_LABEL"]);
					}
					else
					{
						$arFindFields[$FIELD_NAME] = $arUserField["FIELD_NAME"];
					}
				}
			}
		}
	}

	public function AdminListShowFilter($entity_id)
	{
		$arUserFields = $this->GetUserFields($entity_id, 0, $GLOBALS["lang"]);
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField["SHOW_FILTER"] != "N" && $arUserField["USER_TYPE"]["BASE_TYPE"] != "file")
			{
				echo $this->GetFilterHTML($arUserField, "find_" . $FIELD_NAME, $GLOBALS["find_" . $FIELD_NAME]);
			}
		}
	}

	public function ShowScript()
	{
		global $APPLICATION;

		$APPLICATION->AddHeadScript("/bitrix/js/main/usertype.js");

		return "";
	}

	public function GetEditFormHTML($bVarsFromForm, $form_value, $arUserField)
	{
		global $APPLICATION;
		global $adminPage, $adminSidePanelHelper;

		if ($arUserField["USER_TYPE"])
		{
			if ($this->GetRights($arUserField["ENTITY_ID"]) >= "W")
			{
				$selfFolderUrl = $adminPage->getSelfFolderUrl();
				$userFieldUrl = $selfFolderUrl . "userfield_edit.php?lang=" . LANGUAGE_ID . "&ID=" . $arUserField["ID"];
				$userFieldUrl = $adminSidePanelHelper->editUrlToPublicPage($userFieldUrl);
				$edit_link = ($arUserField["HELP_MESSAGE"] ? htmlspecialcharsex($arUserField["HELP_MESSAGE"]) . '<br>' : '') . '<a href="' . htmlspecialcharsbx($userFieldUrl . '&back_url=' . urlencode($APPLICATION->GetCurPageParam("", ["bxpublic"]) . '&tabControl_active_tab=user_fields_tab')) . '">' . htmlspecialcharsex(GetMessage("MAIN_EDIT")) . '</a>';
			}
			else
			{
				$edit_link = '';
			}

			$hintHTML = '<span id="hint_' . $arUserField["FIELD_NAME"] . '"></span><script>BX.hint_replace(BX(\'hint_' . $arUserField["FIELD_NAME"] . '\'), \'' . CUtil::JSEscape($edit_link) . '\');</script>&nbsp;';

			if ($arUserField["MANDATORY"] == "Y")
			{
				$strLabelHTML = $hintHTML . '<span class="adm-required-field">' . htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"] ?: $arUserField["FIELD_NAME"]) . '</span>' . ':';
			}
			else
			{
				$strLabelHTML = $hintHTML . htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"] ?: $arUserField["FIELD_NAME"]) . ':';
			}

			if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtml"]))
			{
				$js = $this->ShowScript();

				if (!$bVarsFromForm)
				{
					$form_value = $arUserField["VALUE"];
				}
				elseif ($arUserField["USER_TYPE"]["BASE_TYPE"] == "file")
				{
					$form_value = $GLOBALS[$arUserField["FIELD_NAME"] . "_old_id"];
				}
				elseif ($arUserField["EDIT_IN_LIST"] == "N")
				{
					$form_value = $arUserField["VALUE"];
				}

				if (
					$arUserField["MULTIPLE"] === "N"
					||
					!empty($arUserField['USER_TYPE']['USE_FIELD_COMPONENT'])
				)
				{
					$valign = "";
					$rowClass = "";
					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtml"],
						[
							$arUserField,
							[
								"NAME" => $arUserField["FIELD_NAME"],
								"VALUE" => (is_array($form_value) ? $form_value : htmlspecialcharsbx($form_value)),
								"VALIGN" => &$valign,
								"ROWCLASS" => &$rowClass,
							],
							$bVarsFromForm,
						]
					);
					return '<tr' . ($rowClass != '' ? ' class="' . $rowClass . '"' : '') . '><td' . ($valign <> 'middle' ? ' class="adm-detail-valign-top"' : '') . ' width="40%">' . $strLabelHTML . '</td><td width="60%">' . $html . '</td></tr>' . $js;
				}
				elseif (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtmlmulty"]))
				{
					if (!is_array($form_value))
					{
						$form_value = [];
					}
					foreach ($form_value as $key => $value)
					{
						if (!is_array($value))
						{
							$form_value[$key] = htmlspecialcharsbx($value);
						}
					}

					$rowClass = "";
					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtmlmulty"],
						[
							$arUserField,
							[
								"NAME" => $arUserField["FIELD_NAME"] . "[]",
								"VALUE" => $form_value,
								"ROWCLASS" => &$rowClass,
							],
							$bVarsFromForm,
						]
					);
					return '<tr' . ($rowClass != '' ? ' class="' . $rowClass . '"' : '') . '><td class="adm-detail-valign-top">' . $strLabelHTML . '</td><td>' . $html . '</td></tr>' . $js;
				}
				else
				{
					if (!is_array($form_value))
					{
						$form_value = [];
					}
					$html = "";
					$i = -1;
					foreach ($form_value as $i => $value)
					{
						if (
							(is_array($value) && (implode("", $value) <> ''))
							|| ((!is_array($value)) && ((string)$value <> ''))
						)
						{
							$html .= '<tr><td>' . call_user_func_array(
									[$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtml"],
									[
										$arUserField,
										[
											"NAME" => $arUserField["FIELD_NAME"] . "[" . $i . "]",
											"VALUE" => (is_array($value) ? $value : htmlspecialcharsbx($value)),
										],
										$bVarsFromForm,
									]
								) . '</td></tr>';
						}
					}
					//Add multiple values support
					$rowClass = "";
					$FIELD_NAME_X = str_replace('_', 'x', $arUserField["FIELD_NAME"]);
					$fieldHtml = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "geteditformhtml"],
						[
							$arUserField,
							[
								"NAME" => $arUserField["FIELD_NAME"] . "[" . ($i + 1) . "]",
								"VALUE" => "",
								"ROWCLASS" => &$rowClass,
							],
							$bVarsFromForm,
						]
					);
					return '<tr' . ($rowClass != '' ? ' class="' . $rowClass . '"' : '') . '><td class="adm-detail-valign-top">' . $strLabelHTML . '</td><td>' .
						'<table id="table_' . $arUserField["FIELD_NAME"] . '">' . $html . '<tr><td>' . $fieldHtml . '</td></tr>' .
						'<tr><td style="padding-top: 6px;"><input type="button" value="' . GetMessage("USER_TYPE_PROP_ADD") . '" onClick="addNewRow(\'table_' . $arUserField["FIELD_NAME"] . '\', \'' . $FIELD_NAME_X . '|' . $arUserField["FIELD_NAME"] . '|' . $arUserField["FIELD_NAME"] . '_old_id\')"></td></tr>' .
						"<script type=\"text/javascript\">BX.addCustomEvent('onAutoSaveRestore', function(ob, data) {for (var i in data){if (i.substring(0," . (mb_strlen($arUserField['FIELD_NAME']) + 1) . ")=='" . CUtil::JSEscape($arUserField['FIELD_NAME']) . "['){" .
						'addNewRow(\'table_' . $arUserField["FIELD_NAME"] . '\', \'' . $FIELD_NAME_X . '|' . $arUserField["FIELD_NAME"] . '|' . $arUserField["FIELD_NAME"] . '_old_id\')' .
						"}}})</script>" .
						'</table>' .
						'</td></tr>' . $js;
				}
			}
		}
		return '';
	}

	public function GetFilterHTML($arUserField, $filter_name, $filter_value)
	{
		if ($arUserField["USER_TYPE"])
		{
			if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getfilterhtml"]))
			{
				$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "getfilterhtml"],
						[
							$arUserField,
							[
								"NAME" => $filter_name,
								"VALUE" => htmlspecialcharsex($filter_value),
							],
						]
					) . CAdminCalendar::ShowScript();
				return '<tr><td>' . htmlspecialcharsbx($arUserField["LIST_FILTER_LABEL"] ?: $arUserField["FIELD_NAME"]) . ':</td><td>' . $html . '</td></tr>';
			}
		}
		return '';
	}

	/**
	 * @param $arUserField
	 * @param $value
	 * @param CAdminListRow $row
	 */
	public function AddUserField($arUserField, $value, $row)
	{
		if ($arUserField["USER_TYPE"])
		{
			$js = $this->ShowScript();
			$useFieldComponent = !empty($arUserField['USER_TYPE']['USE_FIELD_COMPONENT']);

			if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"]))
			{
				if ($arUserField["MULTIPLE"] == "N")
				{
					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"],
						[
							$arUserField,
							[
								"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]",
								"VALUE" => ($useFieldComponent ? $value : htmlspecialcharsbx($value)),
							],
						]
					);
					if ($html === '')
					{
						$html = '&nbsp;';
					}
					$row->AddViewField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
				elseif (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtmlmulty"]))
				{
					if (is_array($value))
					{
						$form_value = $value;
					}
					else
					{
						$form_value = unserialize($value, ['allowed_classes' => false]);
					}

					if (!is_array($form_value))
					{
						$form_value = [];
					}

					foreach ($form_value as $key => $val)
					{
						$form_value[$key] = ($useFieldComponent ? $val : htmlspecialcharsbx($val));
					}

					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtmlmulty"],
						[
							$arUserField,
							[
								"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]" . "[]",
								"VALUE" => $form_value,
							],
						]
					);
					if ($html == '')
					{
						$html = '&nbsp;';
					}
					$row->AddViewField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
				else
				{
					$html = "";

					if (is_array($value))
					{
						$form_value = $value;
					}
					else
					{
						$form_value = $value <> '' ? unserialize($value, ['allowed_classes' => false]) : false;
					}

					if (!is_array($form_value))
					{
						$form_value = [];
					}

					foreach ($form_value as $i => $val)
					{
						if ($html != "")
						{
							$html .= " / ";
						}
						$html .= call_user_func_array(
							[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"],
							[
								$arUserField,
								[
									"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]" . "[" . $i . "]",
									"VALUE" => ($useFieldComponent ? $val : htmlspecialcharsbx($val)),
								],
							]
						);
					}
					if ($html == '')
					{
						$html = '&nbsp;';
					}
					$row->AddViewField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
			}
			if ($arUserField["EDIT_IN_LIST"] == "Y" && is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtml"]))
			{
				if (!$row->bEditMode)
				{
					// put dummy
					$row->AddEditField($arUserField["FIELD_NAME"], "&nbsp;");
				}
				elseif ($arUserField["MULTIPLE"] == "N")
				{
					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtml"],
						[
							$arUserField,
							[
								"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]",
								"VALUE" => ($useFieldComponent ? $value : htmlspecialcharsbx($value)),
							],
						]
					);
					if ($html == '')
					{
						$html = '&nbsp;';
					}
					$row->AddEditField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
				elseif (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtmlmulty"]))
				{
					if (is_array($value))
					{
						$form_value = $value;
					}
					else
					{
						$form_value = $value <> '' ? unserialize($value, ['allowed_classes' => false]) : false;
					}

					if (!is_array($form_value))
					{
						$form_value = [];
					}

					foreach ($form_value as $key => $val)
					{
						$form_value[$key] = ($useFieldComponent ? $val : htmlspecialcharsbx($val));
					}

					$html = call_user_func_array(
						[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtmlmulty"],
						[
							$arUserField,
							[
								"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "][]",
								"VALUE" => $form_value,
							],
						]
					);
					if ($html == '')
					{
						$html = '&nbsp;';
					}
					$row->AddEditField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
				else
				{
					$html = "<table id=\"table_" . $arUserField["FIELD_NAME"] . "_" . $row->id . "\">";
					if (is_array($value))
					{
						$form_value = $value;
					}
					else
					{
						$form_value = unserialize($value, ['allowed_classes' => false]);
					}

					if (!is_array($form_value))
					{
						$form_value = [];
					}

					$i = -1;
					foreach ($form_value as $i => $val)
					{
						$html .= '<tr><td>' . call_user_func_array(
								[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtml"],
								[
									$arUserField,
									[
										"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]" . "[" . $i . "]",
										"VALUE" => ($useFieldComponent ? $val : htmlspecialcharsbx($val)),
									],
								]
							) . '</td></tr>';
					}
					$html .= '<tr><td>' . call_user_func_array(
							[$arUserField["USER_TYPE"]["CLASS_NAME"], "getadminlistedithtml"],
							[
								$arUserField,
								[
									"NAME" => "FIELDS[" . $row->id . "][" . $arUserField["FIELD_NAME"] . "]" . "[" . ($i + 1) . "]",
									"VALUE" => "",
								],
							]
						) . '</td></tr>';
					$html .= '<tr><td><input type="button" value="' . GetMessage("USER_TYPE_PROP_ADD") . '" onClick="addNewRow(\'table_' . $arUserField["FIELD_NAME"] . '_' . $row->id . '\', \'FIELDS\\\\[' . $row->id . '\\\\]\\\\[' . $arUserField["FIELD_NAME"] . '\\\\]\')"></td></tr>' .
						'</table>';
					$row->AddEditField($arUserField["FIELD_NAME"], $html . $js . CAdminCalendar::ShowScript());
				}
			}
		}
	}

	public function getListView($userfield, $value)
	{
		$html = '';

		if (is_callable([$userfield["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"]))
		{
			if ($userfield["MULTIPLE"] == "N")
			{
				$html = call_user_func_array(
					[$userfield["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"],
					[
						$userfield,
						[
							"VALUE" => htmlspecialcharsbx($value),
						],
					]
				);
			}
			elseif (is_callable([$userfield["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtmlmulty"]))
			{
				$form_value = is_array($value) ? $value : unserialize($value, ['allowed_classes' => false]);

				if (!is_array($form_value))
				{
					$form_value = [];
				}

				foreach ($form_value as $key => $val)
				{
					$form_value[$key] = htmlspecialcharsbx($val);
				}

				$html = call_user_func_array(
					[$userfield["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtmlmulty"],
					[
						$userfield,
						[
							"VALUE" => $form_value,
						],
					]
				);
			}
			else
			{
				if (is_array($value))
				{
					$form_value = $value;
				}
				else
				{
					$form_value = $value <> '' ? unserialize($value, ['allowed_classes' => false]) : false;
				}

				if (!is_array($form_value))
				{
					$form_value = [];
				}

				foreach ($form_value as $val)
				{
					if ($html != "")
					{
						$html .= " / ";
					}

					$html .= call_user_func_array(
						[$userfield["USER_TYPE"]["CLASS_NAME"], "getadminlistviewhtml"],
						[
							$userfield,
							[
								"VALUE" => htmlspecialcharsbx($val),
							],
						]
					);
				}
			}
		}

		return $html <> '' ? $html : '&nbsp;';
	}

	public function CallUserTypeComponent($componentName, $componentTemplate, $arUserField, $arAdditionalParameters = [])
	{
		global $APPLICATION;
		$arParams = $arAdditionalParameters;
		$arParams['arUserField'] = $arUserField;
		ob_start();
		$APPLICATION->IncludeComponent(
			$componentName,
			$componentTemplate,
			$arParams,
			null,
			["HIDE_ICONS" => "Y"]
		);
		return ob_get_clean();
	}

	/**
	 * @param array $userField
	 * @param array|null $additionalParameters
	 * @return string|null
	 */
	public function renderField(array $userField, ?array $additionalParameters = []): ?string
	{
		$userType = $this->getUserType($userField['USER_TYPE_ID']);
		if (!empty($userType['CLASS_NAME']) && is_callable([$userType['CLASS_NAME'], 'renderField']))
		{
			return call_user_func([$userType['CLASS_NAME'], 'renderField'], $userField, $additionalParameters);
		}
		return null;
	}

	public function GetPublicView($arUserField, $arAdditionalParameters = [])
	{
		$event = new \Bitrix\Main\Event("main", "onBeforeGetPublicView", [&$arUserField, &$arAdditionalParameters]);
		$event->send();

		$arType = $this->GetUserType($arUserField["USER_TYPE_ID"]);

		$html = null;
		$event = new \Bitrix\Main\Event("main", "onGetPublicView", [$arUserField, $arAdditionalParameters]);
		$event->send();
		foreach ($event->getResults() as $evenResult)
		{
			if ($evenResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$html = $evenResult->getParameters();
				break;
			}
		}

		if ($html !== null)
		{
			//All done
		}
		elseif (isset($arUserField["VIEW_CALLBACK"]) && is_callable($arUserField['VIEW_CALLBACK']))
		{
			$html = call_user_func_array($arUserField["VIEW_CALLBACK"], [
				$arUserField,
				$arAdditionalParameters,
			]);
		}
		elseif ($arType && isset($arType["VIEW_CALLBACK"]) && is_callable($arType['VIEW_CALLBACK']))
		{
			$html = call_user_func_array($arType["VIEW_CALLBACK"], [
				$arUserField,
				$arAdditionalParameters,
			]);
		}
		elseif (isset($arUserField["VIEW_COMPONENT_NAME"]))
		{
			$html = $this->CallUserTypeComponent(
				$arUserField["VIEW_COMPONENT_NAME"],
				$arUserField["VIEW_COMPONENT_TEMPLATE"],
				$arUserField,
				$arAdditionalParameters
			);
		}
		elseif ($arType && isset($arType["VIEW_COMPONENT_NAME"]))
		{
			$html = $this->CallUserTypeComponent(
				$arType["VIEW_COMPONENT_NAME"],
				$arType["VIEW_COMPONENT_TEMPLATE"],
				$arUserField,
				$arAdditionalParameters
			);
		}
		else
		{
			$html = $this->CallUserTypeComponent(
				"bitrix:system.field.view",
				$arUserField["USER_TYPE_ID"],
				$arUserField,
				$arAdditionalParameters
			);
		}

		$event = new \Bitrix\Main\Event("main", "onAfterGetPublicView", [$arUserField, $arAdditionalParameters, &$html]);
		$event->send();

		return $html;
	}

	public function getPublicText($userField)
	{
		$userType = $this->getUserType($userField['USER_TYPE_ID']);
		if (!empty($userType['CLASS_NAME']) && is_callable([$userType['CLASS_NAME'], 'getPublicText']))
		{
			return call_user_func_array([$userType['CLASS_NAME'], 'getPublicText'], [$userField]);
		}

		return join(', ', array_map(function ($v) {
			return is_null($v) || is_scalar($v) ? (string)$v : '';
		}, (array)$userField['VALUE']));
	}

	public function GetPublicEdit($arUserField, $arAdditionalParameters = [])
	{
		$event = new \Bitrix\Main\Event("main", "onBeforeGetPublicEdit", [&$arUserField, &$arAdditionalParameters]);
		$event->send();

		$arType = $this->GetUserType($arUserField["USER_TYPE_ID"]);

		$html = null;
		$event = new \Bitrix\Main\Event("main", "onGetPublicEdit", [$arUserField, $arAdditionalParameters]);
		$event->send();
		foreach ($event->getResults() as $evenResult)
		{
			if ($evenResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$html = $evenResult->getParameters();
				break;
			}
		}

		if ($html !== null)
		{
			//All done
		}
		elseif (isset($arUserField["EDIT_CALLBACK"]) && is_callable($arUserField['EDIT_CALLBACK']))
		{
			$html = call_user_func_array($arUserField["EDIT_CALLBACK"], [
				$arUserField,
				$arAdditionalParameters,
			]);
		}
		elseif ($arType && isset($arType["EDIT_CALLBACK"]) && is_callable($arType['EDIT_CALLBACK']))
		{
			$html = call_user_func_array($arType["EDIT_CALLBACK"], [
				$arUserField,
				$arAdditionalParameters,
			]);
		}
		elseif (isset($arUserField["EDIT_COMPONENT_NAME"]))
		{
			$html = $this->CallUserTypeComponent(
				$arUserField["EDIT_COMPONENT_NAME"],
				$arUserField["EDIT_COMPONENT_TEMPLATE"],
				$arUserField,
				$arAdditionalParameters
			);
		}
		elseif ($arType && isset($arType["EDIT_COMPONENT_NAME"]))
		{
			$html = $this->CallUserTypeComponent(
				$arType["EDIT_COMPONENT_NAME"],
				$arType["EDIT_COMPONENT_TEMPLATE"],
				$arUserField,
				$arAdditionalParameters
			);
		}
		else
		{
			$html = $this->CallUserTypeComponent(
				"bitrix:system.field.edit",
				$arUserField["USER_TYPE_ID"],
				$arUserField,
				$arAdditionalParameters
			);
		}

		$event = new \Bitrix\Main\Event("main", "onAfterGetPublicEdit", [$arUserField, $arAdditionalParameters, &$html]);
		$event->send();

		return $html;
	}

	public function GetSettingsHTML($arUserField, $bVarsFromForm = false)
	{
		if (!is_array($arUserField)) // New field
		{
			if ($arType = $this->GetUserType($arUserField))
			{
				if (is_callable([$arType["CLASS_NAME"], "getsettingshtml"]))
				{
					return call_user_func_array([$arType["CLASS_NAME"], "getsettingshtml"], [false, ["NAME" => "SETTINGS"], $bVarsFromForm]);
				}
			}
		}
		else
		{
			if (!is_array($arUserField["SETTINGS"]) || empty($arUserField["SETTINGS"]))
			{
				$arUserField["SETTINGS"] = $this->PrepareSettings(0, $arUserField);
			}

			if ($arType = $this->GetUserType($arUserField["USER_TYPE_ID"]))
			{
				if (is_callable([$arType["CLASS_NAME"], "getsettingshtml"]))
				{
					return call_user_func_array([$arType["CLASS_NAME"], "getsettingshtml"], [$arUserField, ["NAME" => "SETTINGS"], $bVarsFromForm]);
				}
			}
		}
		return null;
	}

	/**
	 * @param       $entity_id
	 * @param       $ID
	 * @param       $arFields
	 * @param bool $user_id False means current user id.
	 * @param bool $checkRequired Whether to check required fields.
	 * @param array|null $requiredFields Conditionally required fields.
	 * @param array|null $filteredFields Filtered fields.
	 * @return bool
	 */
	public function CheckFields($entity_id, $ID, $arFields, $user_id = false, $checkRequired = true, array $requiredFields = null, array $filteredFields = null)
	{
		global $APPLICATION;
		$requiredFieldMap = is_array($requiredFields) ? array_fill_keys($requiredFields, true) : null;
		$aMsg = [];
		//1 Get user typed fields list for entity
		$arUserFields = $this->GetUserFields($entity_id, $ID, LANGUAGE_ID);
		//2 Filter user typed fields
		if (isset($filteredFields))
		{
			$arUserFields = array_filter($arUserFields, static function ($fieldName) use ($filteredFields) {
				return in_array($fieldName, $filteredFields);
			}, ARRAY_FILTER_USE_KEY);
		}
		//3 For each field
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			$enableRequiredFieldCheck = $arUserField["MANDATORY"] === "Y"
				? $checkRequired : ($requiredFieldMap && isset($requiredFieldMap[$FIELD_NAME]));

			//common Check for all fields
			$isSingleValue = ($arUserField["MULTIPLE"] === "N");
			if (
				$enableRequiredFieldCheck
				&& (
					(isset($ID) && $ID <= 0)
					|| array_key_exists($FIELD_NAME, $arFields)
				)
			)
			{
				$EDIT_FORM_LABEL = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];

				if ($arUserField["USER_TYPE"]["BASE_TYPE"] === "file")
				{
					$isNewFilePresent = false;
					$files = [];
					if (is_array($arUserField["VALUE"]))
					{
						$files = array_flip($arUserField["VALUE"]);
					}
					elseif ($arUserField["VALUE"] > 0)
					{
						$files = [$arUserField["VALUE"] => 0];
					}
					elseif (is_numeric($arFields[$FIELD_NAME]))
					{
						$files = [$arFields[$FIELD_NAME] => 0];
					}

					if ($isSingleValue)
					{
						$value = $arFields[$FIELD_NAME];
						if (is_array($value) && array_key_exists("tmp_name", $value))
						{
							if (array_key_exists("del", $value) && $value["del"])
							{
								unset($files[$value["old_id"]]);
							}
							elseif (array_key_exists("size", $value) && $value["size"] > 0)
							{
								$isNewFilePresent = true;
							}
						}
						elseif ($value > 0)
						{
							$isNewFilePresent = true;
							$files[$value] = $value;
						}
					}
					else
					{
						if (is_array($arFields[$FIELD_NAME]))
						{
							foreach ($arFields[$FIELD_NAME] as $value)
							{
								if (is_array($value) && array_key_exists("tmp_name", $value))
								{
									if (array_key_exists("del", $value) && $value["del"])
									{
										unset($files[$value["old_id"]]);
									}
									elseif (array_key_exists("size", $value) && $value["size"] > 0)
									{
										$isNewFilePresent = true;
									}
								}
								elseif ($value > 0)
								{
									$isNewFilePresent = true;
									$files[$value] = $value;
								}
							}
						}
					}

					if (!$isNewFilePresent && empty($files))
					{
						$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
					}
				}
				elseif ($isSingleValue)
				{
					if ($this->isValueEmpty($arUserField, $arFields[$FIELD_NAME]))
					{
						$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
					}
				}
				else
				{
					if (!is_array($arFields[$FIELD_NAME]))
					{
						$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
					}
					else
					{
						$bFound = false;
						foreach ($arFields[$FIELD_NAME] as $value)
						{
							if (
								(
									is_array($value)
									&& (implode("", $value) <> '')
								)
								||
								(
									!is_array($value)
									&& !$this->isValueEmpty($arUserField, $value)
								)
							)
							{
								$bFound = true;
								break;
							}
						}
						if (!$bFound)
						{
							$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
						}
					}
				}
			}
			//identify user type
			if ($arUserField["USER_TYPE"])
			{
				$CLASS_NAME = $arUserField["USER_TYPE"]["CLASS_NAME"];
				if (array_key_exists($FIELD_NAME, $arFields) && is_callable([$CLASS_NAME, "checkfields"]))
				{
					if ($isSingleValue)
					{
						if (!($arFields[$FIELD_NAME] instanceof SqlExpression))
						{
							$canUseArrayValueForSingleField = false;
							if (
								is_callable([$CLASS_NAME, 'canUseArrayValueForSingleField'])
								&& $CLASS_NAME::canUseArrayValueForSingleField()
							)
							{
								$canUseArrayValueForSingleField = true;
							}

							if (is_array($arFields[$FIELD_NAME]) && !$canUseArrayValueForSingleField)
							{
								$fieldName = HtmlFilter::encode(
									empty($arUserField['EDIT_FORM_LABEL'])
										? $arUserField['FIELD_NAME']
										: $arUserField['EDIT_FORM_LABEL']
								);
								$messages = [
									[
										'id' => $arUserField['FIELD_NAME'],
										'text' => Loc::getMessage('USER_TYPE_FIELD_VALUE_IS_MULTIPLE', [
											'#FIELD_NAME#' => $fieldName,
										]),
									],
								];
							}
							else
							{
								//apply appropriate check function
								$messages = call_user_func_array(
									[
										$CLASS_NAME,
										'checkfields',
									],
									[
										$arUserField,
										$arFields[$FIELD_NAME],
										$user_id,
									]
								);
							}
							$aMsg = array_merge($aMsg, $messages);
						}
					}
					elseif (is_array($arFields[$FIELD_NAME]))
					{
						foreach ($arFields[$FIELD_NAME] as $value)
						{
							if (!empty($value))
							{
								if ($value instanceof SqlExpression)
								{
									$aMsg[] = [
										'id' => $FIELD_NAME,
										'text' => "Multiple field \"#FIELD_NAME#\" can't handle SqlExpression because of serialized uts cache",
									];
								}
								else
								{
									//apply appropriate check function
									$ar = call_user_func_array(
										[$CLASS_NAME, "checkfields"],
										[$arUserField, $value, $user_id]
									);
									$aMsg = array_merge($aMsg, $ar);
								}
							}
						}
					}
				}
			}
		}
		//3 Return succsess/fail flag
		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	/**
	 * Replacement for CheckFields, if you are already have fetched old data
	 *
	 * @param $entity_id
	 * @param $oldData
	 * @param $arFields
	 *
	 * @return bool
	 */
	public function CheckFieldsWithOldData($entity_id, $oldData, $arFields)
	{
		global $APPLICATION;

		$aMsg = [];

		//1 Get user typed fields list for entity
		$arUserFields = $this->getUserFieldsWithReadyData($entity_id, $oldData, LANGUAGE_ID);

		//2 For each field
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			//identify user type
			if ($arUserField["USER_TYPE"])
			{
				$CLASS_NAME = $arUserField["USER_TYPE"]["CLASS_NAME"];
				$EDIT_FORM_LABEL = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];

				if (array_key_exists($FIELD_NAME, $arFields) && is_callable([$CLASS_NAME, "checkfields"]))
				{
					// check required values
					if ($arUserField["MANDATORY"] === "Y")
					{
						if ($arUserField["USER_TYPE"]["BASE_TYPE"] === "file")
						{
							$isNewFilePresent = false;
							$files = [];
							if (is_array($arUserField["VALUE"]))
							{
								$files = array_flip($arUserField["VALUE"]);
							}
							elseif ($arUserField["VALUE"] > 0)
							{
								$files = [$arUserField["VALUE"] => 0];
							}
							elseif (is_numeric($arFields[$FIELD_NAME]))
							{
								$files = [$arFields[$FIELD_NAME] => 0];
							}

							if ($arUserField["MULTIPLE"] === "N")
							{
								$value = $arFields[$FIELD_NAME];
								if (is_array($value) && array_key_exists("tmp_name", $value))
								{
									if (array_key_exists("del", $value) && $value["del"])
									{
										unset($files[$value["old_id"]]);
									}
									elseif (array_key_exists("size", $value) && $value["size"] > 0)
									{
										$isNewFilePresent = true;
									}
								}
								elseif ($value > 0)
								{
									$isNewFilePresent = true;
									$files[$value] = $value;
								}
							}
							else
							{
								if (is_array($arFields[$FIELD_NAME]))
								{
									foreach ($arFields[$FIELD_NAME] as $value)
									{
										if (is_array($value) && array_key_exists("tmp_name", $value))
										{
											if (array_key_exists("del", $value) && $value["del"])
											{
												unset($files[$value["old_id"]]);
											}
											elseif (array_key_exists("size", $value) && $value["size"] > 0)
											{
												$isNewFilePresent = true;
											}
										}
										elseif ($value > 0)
										{
											$isNewFilePresent = true;
											$files[$value] = $value;
										}
									}
								}
							}

							if (!$isNewFilePresent && empty($files))
							{
								$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
							}
						}
						elseif ($arUserField["MULTIPLE"] == "N")
						{
							if ($this->isValueEmpty($arUserField, $arFields[$FIELD_NAME]))
							{
								$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
							}
						}
						else
						{
							if (!is_array($arFields[$FIELD_NAME]))
							{
								$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
							}
							else
							{
								$bFound = false;
								foreach ($arFields[$FIELD_NAME] as $value)
								{
									if (
										(is_array($value) && (implode("", $value) <> ''))
										|| ((!is_array($value)) && ((string)$value <> ''))
									)
									{
										$bFound = true;
										break;
									}
								}
								if (!$bFound)
								{
									$aMsg[] = ["id" => $FIELD_NAME, "text" => str_replace("#FIELD_NAME#", $EDIT_FORM_LABEL, GetMessage("USER_TYPE_FIELD_VALUE_IS_MISSING"))];
								}
							}
						}
					}

					// check regular values
					if ($arUserField["MULTIPLE"] == "N")
					{
						//apply appropriate check function
						$ar = call_user_func_array(
							[$CLASS_NAME, "checkfields"],
							[$arUserField, $arFields[$FIELD_NAME]]
						);
						$aMsg = array_merge($aMsg, $ar);
					}
					elseif (is_array($arFields[$FIELD_NAME]))
					{
						foreach ($arFields[$FIELD_NAME] as $value)
						{
							if (!empty($value))
							{
								//apply appropriate check function
								$ar = call_user_func_array(
									[$CLASS_NAME, "checkfields"],
									[$arUserField, $value]
								);
								$aMsg = array_merge($aMsg, $ar);
							}
						}
					}
				}
			}
		}

		//3 Return succsess/fail flag
		if (!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}

		return true;
	}

	protected function isValueEmpty(array $userField, $value): bool
	{
		$className = $userField['USER_TYPE']['CLASS_NAME'] ?? null;
		if (!is_a($className, BaseType::class, true))
		{
			$className = BaseType::class;
		}
		if (!$className::isMandatorySupported())
		{
			return false;
		}
		$isNumberType = (
			$userField['USER_TYPE_ID'] === \Bitrix\Main\UserField\Types\IntegerType::USER_TYPE_ID
			|| $userField['USER_TYPE_ID'] === \Bitrix\Main\UserField\Types\DoubleType::USER_TYPE_ID
		);
		if (
			$isNumberType
			&& (
				$value === 0 || $value === 0.0 || $value === "0" || $value === "0.0" || $value === "0,0"
			)
		)
		{
			return false;
		}

		return (string)$value === "";
	}

	public function Update($entity_id, $ID, $arFields, $user_id = false)
	{
		global $DB;

		$entity_id = preg_replace("/[^0-9A-Z_]+/", "", $entity_id);

		$result = $this->updateUserFieldValuesByEvent($entity_id, (int)$ID, $arFields);
		if ($result !== null)
		{
			return $result;
		}

		$arUpdate = [];
		$arBinds = [];
		$arInsert = [];
		$arInsertType = [];
		$arDelete = [];

		$arUserFields = $this->GetUserFields($entity_id, $ID, false, $user_id, array_keys($arFields));

		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if (array_key_exists($FIELD_NAME, $arFields))
			{
				$arUserField['VALUE_ID'] = $ID;
				if ($arUserField["MULTIPLE"] == "N")
				{
					if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesave"]))
					{
						$arFields[$FIELD_NAME] = call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesave"], [$arUserField, $arFields[$FIELD_NAME], $user_id]);
					}

					if ((string)$arFields[$FIELD_NAME] !== '')
					{
						$arUpdate[$FIELD_NAME] = $arFields[$FIELD_NAME];
					}
					else
					{
						$arUpdate[$FIELD_NAME] = false;
					}
				}
				elseif (is_array($arFields[$FIELD_NAME]))
				{
					$arInsert[$arUserField["ID"]] = [];
					$arInsertType[$arUserField["ID"]] = $arUserField["USER_TYPE"];
					$arInsertType[$arUserField['ID']]['FIELD_NAME'] = $arUserField['FIELD_NAME'];

					if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesaveall"]))
					{
						$arInsert[$arUserField["ID"]] = call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesaveall"], [$arUserField, $arFields[$FIELD_NAME], $user_id]);
					}
					else
					{
						foreach ($arFields[$FIELD_NAME] as $value)
						{
							if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesave"]))
							{
								$value = call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "onbeforesave"], [$arUserField, $value, $user_id]);
							}

							if ((string)$value <> '')
							{
								switch ($arInsertType[$arUserField["ID"]]["BASE_TYPE"])
								{
									case "int":
									case "file":
									case "enum":
										$value = intval($value);
										break;
									case "double":
										$value = doubleval($value);
										if (!is_finite($value))
										{
											$value = 0;
										}
										break;
									case "string":
										$value = (string)$value;
										break;
								}
								$arInsert[$arUserField["ID"]][] = $value;
							}
						}
					}

					if ($arUserField['USER_TYPE_ID'] == 'datetime')
					{
						$serialized = \Bitrix\Main\UserFieldTable::serializeMultipleDatetime($arInsert[$arUserField["ID"]]);
					}
					elseif ($arUserField['USER_TYPE_ID'] == 'date')
					{
						$serialized = \Bitrix\Main\UserFieldTable::serializeMultipleDate($arInsert[$arUserField["ID"]]);
					}
					else
					{
						$serialized = serialize($arInsert[$arUserField["ID"]]);
					}

					$arBinds[$FIELD_NAME] = $arUpdate[$FIELD_NAME] = $serialized;

					$arDelete[$arUserField["ID"]] = true;
				}
			}
		}

		$lower_entity_id = mb_strtolower($entity_id);

		if (!empty($arUpdate))
		{
			$strUpdate = $DB->PrepareUpdate("b_uts_" . $lower_entity_id, $arUpdate);
		}
		else
		{
			return false;
		}

		$DB->StartTransaction();

		$result = false;
		if ($strUpdate <> '')
		{
			$result = true;
			$rs = $DB->QueryBind("UPDATE b_uts_" . $lower_entity_id . " SET " . $strUpdate . " WHERE VALUE_ID = " . intval($ID), $arBinds);
			$rows = $rs->AffectedRowsCount();
		}
		else
		{
			$rows = 0;
		}

		if (intval($rows) <= 0)
		{
			$rs = $DB->Query("SELECT 'x' FROM b_uts_" . $lower_entity_id . " WHERE VALUE_ID = " . intval($ID));
			if ($rs->Fetch())
			{
				$rows = 1;
			}
		}

		if ($rows <= 0)
		{
			$arUpdate["ID"] = $arUpdate["VALUE_ID"] = $ID;
			$DB->Add("b_uts_" . $lower_entity_id, $arUpdate, array_keys($arBinds));
		}
		else
		{
			foreach ($arDelete as $key => $value)
			{
				$DB->Query("DELETE from b_utm_" . $lower_entity_id . " WHERE FIELD_ID = " . intval($key) . " AND VALUE_ID = " . intval($ID));
			}
		}

		foreach ($arInsert as $FieldId => $arField)
		{
			switch ($arInsertType[$FieldId]["BASE_TYPE"])
			{
				case "int":
				case "file":
				case "enum":
					$COLUMN = "VALUE_INT";
					break;
				case "double":
					$COLUMN = "VALUE_DOUBLE";
					break;
				case "datetime":
					$COLUMN = "VALUE_DATE";
					break;
				default:
					$COLUMN = "VALUE";
			}
			foreach ($arField as $value)
			{
				if ($value instanceof \Bitrix\Main\Type\Date)
				{
					// little hack to avoid timezone vs 00:00:00 ambiguity. for utm only
					$value = new \Bitrix\Main\Type\DateTime($value->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
				}

				switch ($arInsertType[$FieldId]["BASE_TYPE"])
				{
					case "int":
					case "file":
					case "enum":
					case "double":
						break;
					case "datetime":
						$userFieldName = $arInsertType[$FieldId]['FIELD_NAME'];
						$value = DateTimeType::charToDate($arUserFields[$userFieldName], $value);
						break;
					default:
						$value = "'" . $DB->ForSql($value) . "'";
				}
				$DB->Query("INSERT INTO b_utm_" . $lower_entity_id . " (VALUE_ID, FIELD_ID, " . $COLUMN . ")
					VALUES (" . intval($ID) . ", '" . $FieldId . "', " . $value . ")");
			}
		}

		$DB->Commit();

		return $result;
	}

	public function copy($entity_id, $id, $copiedId, $entityObject, $userId = false, $ignoreList = [])
	{
		$userFields = $this->getUserFields($entity_id, $id);

		$fields = [];
		foreach ($userFields as $fieldName => $userField)
		{
			if (!in_array($fieldName, $ignoreList))
			{
				if (is_callable([$userField["USER_TYPE"]["CLASS_NAME"], "onBeforeCopy"]))
				{
					$fields[$fieldName] = call_user_func_array(
						[$userField["USER_TYPE"]["CLASS_NAME"], "onBeforeCopy"],
						[$userField, $copiedId, $userField["VALUE"], $entityObject, $userId]
					);
				}
				else
				{
					$fields[$fieldName] = $userField["VALUE"];
				}
			}
		}

		$this->update($entity_id, $copiedId, $fields, $userId);

		foreach ($userFields as $fieldName => $userField)
		{
			if (!in_array($fieldName, $ignoreList))
			{
				if (is_callable([$userField["USER_TYPE"]["CLASS_NAME"], "onAfterCopy"]))
				{
					$fields[$fieldName] = call_user_func_array(
						[$userField["USER_TYPE"]["CLASS_NAME"], "onAfterCopy"],
						[$userField, $copiedId, $fields[$fieldName], $entityObject, $userId]
					);
				}
			}
		}
	}

	public function Delete($entity_id, $ID)
	{
		global $DB;

		$result = $this->deleteUserFieldValuesByEvent($entity_id, $ID);
		if ($result !== null)
		{
			return;
		}

		if ($arUserFields = $this->GetUserFields($entity_id, $ID, false, 0))
		{
			foreach ($arUserFields as $arUserField)
			{
				if (is_array($arUserField["VALUE"]))
				{
					foreach ($arUserField["VALUE"] as $value)
					{
						if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "ondelete"]))
						{
							call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "ondelete"], [$arUserField, $value]);
						}

						if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "file")
						{
							CFile::Delete($value);
						}
					}
				}
				else
				{
					if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "ondelete"]))
					{
						call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "ondelete"], [$arUserField, $arUserField["VALUE"]]);
					}

					if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "file")
					{
						CFile::Delete($arUserField["VALUE"]);
					}
				}
			}
			$DB->Query("DELETE FROM b_utm_" . mb_strtolower($entity_id) . " WHERE VALUE_ID = " . intval($ID));
			$DB->Query("DELETE FROM b_uts_" . mb_strtolower($entity_id) . " WHERE VALUE_ID = " . intval($ID));
		}
	}

	public function OnSearchIndex($entity_id, $ID)
	{
		$result = "";
		if ($arUserFields = $this->GetUserFields($entity_id, $ID, false, 0))
		{
			foreach ($arUserFields as $arUserField)
			{
				if ($arUserField["IS_SEARCHABLE"] == "Y")
				{
					if ($arUserField["USER_TYPE"])
					{
						if (is_callable([$arUserField["USER_TYPE"]["CLASS_NAME"], "onsearchindex"]))
						{
							$result .= "\r\n" . call_user_func_array([$arUserField["USER_TYPE"]["CLASS_NAME"], "onsearchindex"], [$arUserField]);
						}
					}
				}
			}
		}
		return $result;
	}

	public function GetRights($ENTITY_ID = false, $ID = false)
	{
		if (($ID !== false) && array_key_exists("ID:" . $ID, $this->arRightsCache))
		{
			return $this->arRightsCache["ID:" . $ID];
		}
		if (($ENTITY_ID !== false) && array_key_exists("ENTITY_ID:" . $ENTITY_ID, $this->arRightsCache))
		{
			return $this->arRightsCache["ENTITY_ID:" . $ENTITY_ID];
		}

		global $USER;
		if (is_object($USER) && $USER->CanDoOperation('edit_other_settings'))
		{
			$RIGHTS = "X";
		}
		else
		{
			$RIGHTS = "D";
			if ($ID !== false)
			{
				$ar = CUserTypeEntity::GetByID($ID);
				if ($ar)
				{
					$ENTITY_ID = $ar["ENTITY_ID"];
				}
			}

			foreach (GetModuleEvents("main", "OnUserTypeRightsCheck", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent, [$ENTITY_ID]);
				if ($res > $RIGHTS)
				{
					$RIGHTS = $res;
				}
			}
		}

		if ($ID !== false)
		{
			$this->arRightsCache["ID:" . $ID] = $RIGHTS;
		}
		if ($ENTITY_ID !== false)
		{
			$this->arRightsCache["ENTITY_ID:" . $ENTITY_ID] = $RIGHTS;
		}

		return $RIGHTS;
	}

	/**
	 * @param             $arUserField
	 * @param null|string $fieldName
	 * @param array $fieldParameters
	 *
	 * @return Entity\DatetimeField|Entity\FloatField|Entity\IntegerField|Entity\StringField|mixed
	 * @throws Bitrix\Main\ArgumentException
	 */
	public function getEntityField($arUserField, $fieldName = null, $fieldParameters = [])
	{
		if (empty($fieldName))
		{
			$fieldName = $arUserField['FIELD_NAME'];
		}

		if (is_callable([$arUserField['USER_TYPE']['CLASS_NAME'], 'getEntityField']))
		{
			$field = call_user_func([$arUserField['USER_TYPE']['CLASS_NAME'], 'getEntityField'], $fieldName, $fieldParameters);
		}
		elseif ($arUserField['USER_TYPE']['USER_TYPE_ID'] == 'date')
		{
			$field = new Entity\DateField($fieldName, $fieldParameters);
		}
		else
		{
			switch ($arUserField['USER_TYPE']['BASE_TYPE'])
			{
				case 'int':
				case 'enum':
				case 'file':
					$field = (new Entity\IntegerField($fieldName, $fieldParameters))
						->configureNullable()
					;
					break;
				case 'double':
					$field = (new Entity\FloatField($fieldName, $fieldParameters))
						->configureNullable()
					;
					break;
				case 'string':
					$field = (new Entity\StringField($fieldName, $fieldParameters))
						->configureNullable()
					;
					break;
				case 'datetime':
					$field = (new Entity\DatetimeField($fieldName, $fieldParameters))
						->configureNullable()
						->configureUseTimezone(isset($arUserField['SETTINGS']['USE_TIMEZONE']) && $arUserField['SETTINGS']['USE_TIMEZONE'] == 'Y')
					;
					break;
				default:
					throw new \Bitrix\Main\ArgumentException(sprintf(
						'Unknown userfield base type `%s`', $arUserField["USER_TYPE"]['BASE_TYPE']
					));
			}
		}

		$ufHandlerClass = $arUserField['USER_TYPE']['CLASS_NAME'];

		if (is_subclass_of($ufHandlerClass, BaseType::class))
		{
			$defaultValue = $ufHandlerClass::getDefaultValue($arUserField);
			$field->configureDefaultValue($defaultValue);
		}

		return $field;
	}

	/**
	 * @param                    $arUserField
	 * @param Entity\ScalarField $entityField
	 *
	 * @return Entity\ReferenceField[]
	 */
	public function getEntityReferences($arUserField, Entity\ScalarField $entityField)
	{
		if (is_callable([$arUserField['USER_TYPE']['CLASS_NAME'], 'getEntityReferences']))
		{
			return call_user_func([$arUserField['USER_TYPE']['CLASS_NAME'], 'getEntityReferences'], $arUserField, $entityField);
		}

		return [];
	}

	protected function getUserFieldValuesByEvent(array $userFields, string $entityId, int $value): ?array
	{
		$result = [];
		if ($value === 0)
		{
			return null;
		}
		$isGotByEvent = false;
		$event = new \Bitrix\Main\Event('main', 'onGetUserFieldValues', ['userFields' => $userFields, 'entityId' => $entityId, 'value' => $value]);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
			{
				$parameters = $eventResult->getParameters();
				if (isset($parameters['values']) && is_array($parameters['values']))
				{
					$isGotByEvent = true;
					foreach ($userFields as $fieldName => $userField)
					{
						if (isset($parameters['values'][$fieldName]))
						{
							$result[$fieldName] = $parameters['values'][$fieldName];
						}
					}
				}
			}
		}
		if ($isGotByEvent)
		{
			return $result;
		}

		return null;
	}

	protected function updateUserFieldValuesByEvent(string $entityId, int $id, array $fields): ?bool
	{
		$result = null;

		$event = new \Bitrix\Main\Event('main', 'onUpdateUserFieldValues', ['entityId' => $entityId, 'id' => $id, 'fields' => $fields]);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
			{
				$result = true;
			}
			elseif ($eventResult->getType() === \Bitrix\Main\EventResult::ERROR)
			{
				$result = false;
			}
		}

		return $result;
	}

	protected function deleteUserFieldValuesByEvent(string $entityId, int $id): ?bool
	{
		$result = null;

		$event = new \Bitrix\Main\Event('main', 'onDeleteUserFieldValues', ['entityId' => $entityId, 'id' => $id]);
		$event->send();
		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
			{
				$result = true;
			}
			elseif ($eventResult->getType() === \Bitrix\Main\EventResult::ERROR)
			{
				$result = false;
			}
		}

		return $result;
	}
}
