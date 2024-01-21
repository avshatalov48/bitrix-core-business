<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

use Bitrix\Main\Authentication\Internal\ModuleGroupTable;
use Bitrix\Main\TaskTable;
use Bitrix\Main\TaskOperationTable;
use Bitrix\Main\OperationTable;

class CAllTask
{
	protected static $TASK_OPERATIONS_CACHE = [];

	public static function CheckFields(&$arFields, $ID = false)
	{
		global $APPLICATION;

		if ($ID > 0)
		{
			unset($arFields["ID"]);
		}

		$arMsg = [];

		if (($ID === false || isset($arFields["NAME"])) && $arFields["NAME"] == '')
		{
			$arMsg[] = ["id" => "NAME", "text" => GetMessage('MAIN_ERROR_STRING_ID_EMPTY')];
		}

		$filter = ['=NAME' => $arFields['NAME']];
		if ($ID !== false)
		{
			$filter['!=ID'] = $ID;
		}
		if (TaskTable::getList(['select' => ['ID'], 'filter' => $filter])->fetch())
		{
			$arMsg[] = ["id" => "NAME", "text" => GetMessage('MAIN_ERROR_STRING_ID_DOUBLE')];
		}

		if (isset($arFields['LETTER']))
		{
			if (preg_match("/[^A-Z]/i", $arFields['LETTER']) || strlen($arFields['LETTER']) > 1)
			{
				$arMsg[] = ["id" => "LETTER", "text" => GetMessage('MAIN_TASK_WRONG_LETTER')];
			}
			$arFields['LETTER'] = strtoupper($arFields['LETTER']);
		}
		else
		{
			$arFields['LETTER'] = '';
		}

		if (!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		if (!isset($arFields['SYS']) || $arFields['SYS'] != "Y")
		{
			$arFields['SYS'] = "N";
		}
		if (!isset($arFields['BINDING']))
		{
			$arFields['BINDING'] = 'module';
		}

		return true;
	}

	protected static function getOwnFields(array $arFields): array
	{
		$entity = TaskTable::getEntity();
		$fields = [];
		foreach ($arFields as $field => $value)
		{
			if ($entity->hasField($field))
			{
				$fields[$field] = $value;
			}
		}
		return $fields;
	}

	public static function Add($arFields)
	{
		if (!static::CheckFields($arFields))
		{
			return false;
		}

		$result = TaskTable::add(static::getOwnFields($arFields));

		return $result->isSuccess() ? $result->getId() : false;
	}

	public static function Update($arFields, $ID)
	{
		if (!static::CheckFields($arFields, $ID))
		{
			return false;
		}

		$result = TaskTable::update($ID, static::getOwnFields($arFields));

		return $result->isSuccess();
	}

	public static function UpdateModuleRights($id, $moduleId, $letter, $site_id = false)
	{
		global $DB;

		if (!isset($id, $moduleId))
		{
			return false;
		}

		$sql = "SELECT GT.GROUP_ID
				FROM b_group_task GT
				WHERE GT.TASK_ID=" . intval($id);
		$z = $DB->Query($sql);

		$arGroups = [];
		while ($r = $z->Fetch())
		{
			$g = intval($r['GROUP_ID']);
			if ($g > 0)
			{
				$arGroups[] = $g;
			}
		}
		if (empty($arGroups))
		{
			return false;
		}

		$str_groups = implode(',', $arGroups);
		$moduleId = $DB->ForSQL($moduleId);
		$DB->Query(
			"DELETE FROM b_module_group
			WHERE
				MODULE_ID = '" . $moduleId . "' AND
				SITE_ID " . ($site_id ? "='" . $site_id . "'" : "IS NULL") . " AND
				GROUP_ID IN (" . $str_groups . ")"
		);

		if ($letter != '')
		{
			$letter = $DB->ForSQL($letter);
			$DB->Query(
				"INSERT INTO b_module_group (MODULE_ID, GROUP_ID, G_ACCESS, SITE_ID) " .
				"SELECT '" . $moduleId . "', G.ID, '" . $letter . "', " . ($site_id ? "'" . $site_id . "'" : "NULL") . " " .
				"FROM b_group G " .
				"WHERE G.ID IN (" . $str_groups . ")"
			);
		}

		ModuleGroupTable::cleanCache();

		return ($letter != '');
	}

	public static function Delete($ID, $protect = true)
	{
		$ID = intval($ID);

		$delete = !$protect;
		if ($protect)
		{
			$delete = TaskTable::getList(['select' => ['ID'], 'filter' => ['=ID' => $ID, '=SYS' => 'N']])->fetch();
		}

		if ($delete)
		{
			TaskTable::delete($ID);
			TaskOperationTable::deleteByFilter(['=TASK_ID' => $ID]);
		}
	}

	public static function GetList($arOrder = ['MODULE_ID' => 'asc', 'LETTER' => 'asc'], $arFilter = [])
	{
		$arOrder = static::getOwnFields((array)$arOrder);
		$arFilter = static::getOwnFields((array)$arFilter);

		$filter = [];
		foreach ($arFilter as $field => $value)
		{
			if ((string)$value == '' || (string)$value == 'NOT_REF')
			{
				continue;
			}
			if (is_string($value) && str_contains($value, '|'))
			{
				$value = explode('|', $value);
			}
			$filter['=' . $field] = $value;
		}

		$order = [];
		foreach ($arOrder as $field => $direction)
		{
			$order[strtoupper($field)] = (strtoupper($direction) == 'DESC' ? 'DESC' : 'ASC');
		}

		$res = TaskTable::getList([
			'filter' => $filter,
			'order' => $order,
			'cache' => ['ttl' => 3600],
		]);

		$arResult = [];
		while ($arRes = $res->fetch())
		{
			$arRes['TITLE'] = static::GetLangTitle($arRes['NAME'], $arRes['MODULE_ID']);
			$arRes['DESC'] = static::GetLangDescription($arRes['NAME'], $arRes['DESCRIPTION'], $arRes['MODULE_ID']);
			$arResult[] = $arRes;
		}

		$result = new CDBResult();
		$result->InitFromArray($arResult);

		return $result;
	}

	public static function GetOperations($ID, $return_names = false)
	{
		$ID = intval($ID);

		if (!isset(static::$TASK_OPERATIONS_CACHE[$ID]))
		{
			static::$TASK_OPERATIONS_CACHE[$ID] = [];

			$operations = TaskOperationTable::getList([
				'select' => ['OPERATION_ID', 'NAME' => 'OPERATION.NAME'],
				'filter' => ['=TASK_ID' => $ID],
				'cache' => ['ttl' => 3600, 'cache_joins' => true],
			]);

			while ($operation = $operations->fetch())
			{
				static::$TASK_OPERATIONS_CACHE[$ID]['ids'][] = $operation['OPERATION_ID'];
				static::$TASK_OPERATIONS_CACHE[$ID]['names'][] = $operation['NAME'];
			}
		}

		return static::$TASK_OPERATIONS_CACHE[$ID][$return_names ? 'names' : 'ids'] ?? [];
	}

	public static function SetOperations($ID, $arr, $bOpNames = false)
	{
		global $DB;

		$ID = intval($ID);

		//get old operations
		$aPrevOp = [];
		$operations = TaskOperationTable::getList([
			'select' => ['NAME' => 'OPERATION.NAME'],
			'filter' => ['=TASK_ID' => $ID],
			'order' => ['OPERATION_ID' => 'ASC'],
		]);
		while ($operation = $operations->fetch())
		{
			$aPrevOp[] = $operation['NAME'];
		}

		TaskOperationTable::deleteByFilter(['=TASK_ID' => $ID]);

		if (is_array($arr) && !empty($arr))
		{
			$sID = '';
			if ($bOpNames)
			{
				foreach ($arr as $op_id)
				{
					$sID .= ($sID != '' ? ', ' : '') . "'" . $DB->ForSQL($op_id) . "'";
				}

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) " .
					"SELECT '" . $ID . "', O.ID " .
					"FROM b_operation O, b_task T " .
					"WHERE O.NAME IN (" . $sID . ") AND T.MODULE_ID=O.MODULE_ID AND T.ID=" . $ID
				);
			}
			else
			{
				foreach ($arr as $op_id)
				{
					$sID .= ($sID != '' ? ', ' : '') . intval($op_id);
				}

				$DB->Query(
					"INSERT INTO b_task_operation (TASK_ID, OPERATION_ID) " .
					"SELECT '" . $ID . "', ID " .
					"FROM b_operation " .
					"WHERE ID IN (" . $sID . ") "
				);
			}
		}

		unset(static::$TASK_OPERATIONS_CACHE[$ID]);

		TaskOperationTable::cleanCache();

		//get new operations
		$aNewOp = [];
		$operations = TaskOperationTable::getList([
			'select' => ['NAME' => 'OPERATION.NAME'],
			'filter' => ['=TASK_ID' => $ID],
			'order' => ['OPERATION_ID' => 'ASC'],
		]);
		while ($operation = $operations->fetch())
		{
			$aNewOp[] = $operation['NAME'];
		}

		//compare with old one
		$aDiff = array_diff($aNewOp, $aPrevOp);
		if (empty($aDiff))
		{
			$aDiff = array_diff($aPrevOp, $aNewOp);
		}
		if (!empty($aDiff))
		{
			if (COption::GetOptionString("main", "event_log_task", "N") === "Y")
			{
				CEventLog::Log("SECURITY", "TASK_CHANGED", "main", $ID, "(" . implode(", ", $aPrevOp) . ") => (" . implode(", ", $aNewOp) . ")");
			}
			foreach (GetModuleEvents("main", "OnTaskOperationsChanged", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$ID, $aPrevOp, $aNewOp]);
			}
		}
	}

	public static function GetTasksInModules($mode = false, $module_id = false, $binding = false)
	{
		$arFilter = [];
		if ($module_id !== false)
		{
			$arFilter["MODULE_ID"] = $module_id;
		}
		if ($binding !== false)
		{
			$arFilter["BINDING"] = $binding;
		}

		$z = static::GetList(
			[
				"MODULE_ID" => "asc",
				"LETTER" => "asc",
			],
			$arFilter
		);

		$arr = [];
		if ($mode)
		{
			while ($r = $z->Fetch())
			{
				$arr[$r['MODULE_ID']]['reference_id'][] = $r['ID'];
				$arr[$r['MODULE_ID']]['reference'][] = '[' . ($r['LETTER'] ?: '..') . '] ' . static::GetLangTitle($r['NAME'], $r['MODULE_ID']);
			}
		}
		else
		{
			while ($r = $z->Fetch())
			{
				$arr[$r['MODULE_ID']][] = $r;
			}
		}
		return $arr;
	}

	public static function GetByID($ID)
	{
		return static::GetList([], ["ID" => intval($ID)]);
	}

	protected static function GetDescriptions($module)
	{
		static $descriptions = [];

		if (preg_match("/[^a-z0-9._]/i", $module))
		{
			return [];
		}

		if (!isset($descriptions[$module]))
		{
			if (($path = getLocalPath("modules/" . $module . "/admin/task_description.php")) !== false)
			{
				$descriptions[$module] = include($_SERVER["DOCUMENT_ROOT"] . $path);
			}
			else
			{
				$descriptions[$module] = [];
			}
		}

		return $descriptions[$module];
	}

	public static function GetLangTitle($name, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if (isset($descriptions[$nameUpper]["title"]))
		{
			return $descriptions[$nameUpper]["title"];
		}

		return $name;
	}

	public static function GetLangDescription($name, $desc, $module = "main")
	{
		$descriptions = static::GetDescriptions($module);

		$nameUpper = strtoupper($name);

		if (isset($descriptions[$nameUpper]["description"]))
		{
			return $descriptions[$nameUpper]["description"];
		}

		return $desc;
	}

	public static function GetLetter($ID)
	{
		$z = static::GetById($ID);
		if ($r = $z->Fetch())
		{
			if ($r['LETTER'])
			{
				return $r['LETTER'];
			}
		}
		return false;
	}

	public static function GetIdByLetter($letter, $module, $binding = 'module')
	{
		static $TASK_LETTER_CACHE = [];
		if (!$letter)
		{
			return false;
		}

		$k = strtoupper($letter . '_' . $module . '_' . $binding);
		if (isset($TASK_LETTER_CACHE[$k]))
		{
			return $TASK_LETTER_CACHE[$k];
		}

		$z = static::GetList(
			[],
			[
				"LETTER" => $letter,
				"MODULE_ID" => $module,
				"BINDING" => $binding,
				"SYS" => "Y",
			]
		);

		if ($r = $z->Fetch())
		{
			$TASK_LETTER_CACHE[$k] = $r['ID'];
			if ($r['ID'])
			{
				return $r['ID'];
			}
		}

		return false;
	}

	public static function AddFromArray(string $module, array $tasks)
	{
		global $DB;

		$existingOperations = [];
		$records = OperationTable::getList([
			'select' => ['NAME'],
			'filter' => ['=MODULE_ID' => $module],
		]);
		while ($record = $records->fetch())
		{
			$existingOperations[$record['NAME']] = $record['NAME'];
		}

		$existingTasks = [];
		$records = TaskTable::getList([
			'select' => ['NAME'],
			'filter' => ['=MODULE_ID' => $module, '=SYS' => 'Y'],
		]);
		while ($record = $records->fetch())
		{
			$existingTasks[$record['NAME']] = $record['NAME'];
		}

		foreach ($tasks as $taskName => $arTask)
		{
			$binding = empty($arTask["BINDING"]) ? 'module' : $arTask["BINDING"];
			$sqlTaskOperations = [];

			if (isset($arTask["OPERATIONS"]) && is_array($arTask["OPERATIONS"]))
			{
				foreach ($arTask["OPERATIONS"] as $operationName)
				{
					$operationName = mb_substr($operationName, 0, 50);

					if (!isset($existingOperations[$operationName]))
					{
						OperationTable::add([
							'NAME' => $operationName,
							'MODULE_ID' => $module,
							'BINDING' => $binding,
						]);
						$existingOperations[$operationName] = $operationName;
					}

					$sqlTaskOperations[] = $DB->ForSQL($operationName);
				}
			}

			$taskName = mb_substr($taskName, 0, 100);

			if (!isset($existingTasks[$taskName]) && $taskName != '')
			{
				TaskTable::add([
					'NAME' => $taskName,
					'LETTER' => $arTask["LETTER"] ?? null,
					'MODULE_ID' => $module,
					'SYS' => 'Y',
					'BINDING' => $binding,
				]);
			}

			if (!empty($sqlTaskOperations) && $taskName != '')
			{
				$sqlTaskName = $DB->ForSQL($taskName);

				$DB->Query("
					INSERT INTO b_task_operation (TASK_ID, OPERATION_ID)
					SELECT T.ID TASK_ID, O.ID OPERATION_ID
					FROM b_task T, b_operation O
					WHERE T.SYS='Y'
						AND T.NAME='$sqlTaskName'
						AND O.NAME in ('" . implode("','", $sqlTaskOperations) . "')
						AND O.NAME not in (
							SELECT O2.NAME
							FROM b_task T2
								inner join b_task_operation TO2 on TO2.TASK_ID = T2.ID
								inner join b_operation O2 on O2.ID = TO2.OPERATION_ID
							WHERE T2.SYS='Y'
								AND T2.NAME='$sqlTaskName'
						)
				");
			}
		}

		TaskOperationTable::cleanCache();
	}
}

class CTask extends CAllTask
{
}
