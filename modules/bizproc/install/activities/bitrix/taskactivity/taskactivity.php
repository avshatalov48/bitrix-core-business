<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPTaskActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"TaskType" => "",
			"TaskOwnerId" => "",
			"TaskCreatedBy" => "",
			"TaskActiveFrom" => "",
			"TaskActiveTo" => "",
			"TaskName" => "",
			"TaskDetailText" => "",
			"TaskPriority" => "",
			"TaskAssignedTo" => "",
			"TaskTrackers" => "",
			"TaskForumId" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("intranet"))
			return CBPActivityExecutionStatus::Closed;

		$iblockId = COption::GetOptionInt("intranet", "iblock_tasks", 0);
		if ($iblockId <= 0)
			return CBPActivityExecutionStatus::Closed;

		$parentSectionId = 0;

		$dbSectionsList = CIBlockSection::GetList(
			array(),
			array(
				"GLOBAL_ACTIVE" => "Y",
				"XML_ID" => (($this->TaskType == "group") ? $this->TaskOwnerId : "users_tasks"),
				"IBLOCK_ID" => $iblockId,
				"SECTION_ID" => 0
			),
			false
		);
		if ($arSection = $dbSectionsList->GetNext())
			$parentSectionId = $arSection["ID"];

		if ($parentSectionId <= 0)
		{
			$dbSectionsList = CIBlockSection::GetList(
				array(),
				array(
					"GLOBAL_ACTIVE" => "Y",
					"XML_ID" => "users_tasks",
					"IBLOCK_ID" => $iblockId,
					"SECTION_ID" => 0
				),
				false
			);
			if ($arSection = $dbSectionsList->GetNext())
				$parentSectionId = $arSection["ID"];
		}

		if ($parentSectionId <= 0)
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arTaskCreatedBy = CBPHelper::ExtractUsers($this->TaskCreatedBy, $documentId, true);
		$arTaskAssignedTo = CBPHelper::ExtractUsers($this->TaskAssignedTo, $documentId, true);

		if (!$arTaskCreatedBy || !$arTaskAssignedTo)
			return CBPActivityExecutionStatus::Closed;

		if ($this->TaskType != "group")
			$this->TaskOwnerId = $arTaskAssignedTo;

		$arTaskTrackers = CBPHelper::ExtractUsers($this->TaskTrackers, $documentId);

		$arFields = array(
			"IBLOCK_SECTION_ID" => $parentSectionId,
			"MODIFIED_BY" => $arTaskCreatedBy,
			"CREATED_BY" => $arTaskCreatedBy,
			"DATE_CREATE" => date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATETIME)),
			"ACTIVE_FROM" => $this->TaskActiveFrom,
			"ACTIVE_TO" => $this->TaskActiveTo,
			"NAME" => $this->TaskName,
			"DETAIL_TEXT" => $this->TaskDetailText,
			"PROPERTY_TaskPriority" => $this->TaskPriority,
			"PROPERTY_TaskAssignedTo" => $arTaskAssignedTo,
			"PROPERTY_TaskTrackers" => $arTaskTrackers,
		);

		$taskId = CIntranetTasksDocument::CreateDocument($arFields);

		if ($this->TaskType == "group")
		{
			$pathTemplate = str_replace(
				array("#GROUP_ID#", "#TASK_ID#"),
				array($this->TaskOwnerId, "{=Document:ID}"),
				COption::GetOptionString("intranet", "path_task_group_entry", "/workgroups/group/#GROUP_ID#/tasks/task/view/#TASK_ID#/")
			);
		}
		else
		{
			$pathTemplate = str_replace(
				array("#USER_ID#", "#TASK_ID#"),
				array($this->TaskOwnerId, "{=Document:ID}"),
				COption::GetOptionString("intranet", "path_task_user_entry", "/company/personal/user/#USER_ID#/tasks/task/view/#TASK_ID#/")
			);
		}
		$pathTemplate = str_replace('#HTTP_HOST#', $_SERVER['HTTP_HOST'], "http://#HTTP_HOST#".$pathTemplate);

		$arTemplateStates = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
			array("intranet", "CIntranetTasksDocument", "x".$iblockId),
			CBPDocumentEventType::Create
		);

		foreach ($arTemplateStates as $arState)
		{
			CBPDocument::StartWorkflow(
				$arState["TEMPLATE_ID"],
				array("intranet", "CIntranetTasksDocument", $taskId),
				array(
					"OwnerId" => $this->TaskOwnerId,
					"TaskType" => $this->TaskType,
					"PathTemplate" => $pathTemplate,
					"ForumId" => intval($this->TaskForumId),
					"IBlockId" => $iblockId,
				),
				$arErrorsTmp
			);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("TaskAssignedTo", $arTestProperties) || count($arTestProperties["TaskAssignedTo"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "TaskAssignedTo", "message" => GetMessage("BPSNMA_EMPTY_TASKASSIGNEDTO"));
		if (!array_key_exists("TaskName", $arTestProperties) || count($arTestProperties["TaskName"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "TaskName", "message" => GetMessage("BPSNMA_EMPTY_TASKNAME"));
		if (!array_key_exists("TaskPriority", $arTestProperties) || strlen($arTestProperties["TaskPriority"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "TaskPriority", "message" => GetMessage("BPSNMA_EMPTY_TASKPRIORITY"));
		if (!array_key_exists("TaskType", $arTestProperties) || strlen($arTestProperties["TaskType"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "TaskType", "message" => GetMessage("BPSNMA_EMPTY_TASKTYPE"));
		//if (!array_key_exists("TaskOwnerId", $arTestProperties) || strlen($arTestProperties["TaskOwnerId"]) <= 0)
		//	$arErrors[] = array("code" => "NotExist", "parameter" => "TaskOwnerId", "message" => GetMessage("BPSNMA_EMPTY_TASKOWNERID"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!CModule::IncludeModule("socialnetwork"))
			return;

		$arMap = array(
			"TaskType" => "task_type",
			"TaskOwnerId" => "task_owner_id",
			"TaskCreatedBy" => "task_created_by",
			"TaskActiveFrom" => "task_active_from",
			"TaskActiveTo" => "task_active_to",
			"TaskName" => "task_name",
			"TaskDetailText" => "task_detail_text",
			"TaskPriority" => "task_priority",
			"TaskAssignedTo" => "task_assigned_to",
			"TaskTrackers" => "task_trackers",
			"TaskForumId" => "task_forum_id",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "TaskCreatedBy" || $k == "TaskAssignedTo" || $k == "TaskTrackers")
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						else
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
					}
					else
					{
						$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}
		}

		$arGroups = array();
		$db = CSocNetGroup::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
		while ($ar = $db->GetNext())
			$arGroups[$ar["ID"]] = $ar["NAME"];

		$arTaskPriority = array();
		$db = CIBlockProperty::GetPropertyEnum("TaskPriority");
		while ($ar = $db->GetNext())
			$arTaskPriority[$ar["ID"]] = $ar["VALUE"];

		$arForums = array();
		if (CModule::IncludeModule("forum"))
		{
			$db = CForumNew::GetListEx();
			while ($ar = $db->GetNext())
				$arForums[$ar["ID"]] = "[".$ar["ID"]."] ".$ar["NAME"];
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"arGroups" => $arGroups,
				"arTaskPriority" => $arTaskPriority,
				"arForums" => $arForums,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"task_type" => "TaskType",
			"task_owner_id" => "TaskOwnerId",
			"task_created_by" => "TaskCreatedBy",
			"task_active_from" => "TaskActiveFrom",
			"task_active_to" => "TaskActiveTo",
			"task_name" => "TaskName",
			"task_detail_text" => "TaskDetailText",
			"task_priority" => "TaskPriority",
			"task_assigned_to" => "TaskAssignedTo",
			"task_trackers" => "TaskTrackers",
			"task_forum_id" => "TaskForumId",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "task_created_by" || $key == "task_assigned_to" || $key == "task_trackers")
				continue;
			$arProperties[$value] = $arCurrentValues[$key];
		}

		$arProperties["TaskCreatedBy"] = CBPHelper::UsersStringToArray($arCurrentValues["task_created_by"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arProperties["TaskAssignedTo"] = CBPHelper::UsersStringToArray($arCurrentValues["task_assigned_to"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arProperties["TaskTrackers"] = CBPHelper::UsersStringToArray($arCurrentValues["task_trackers"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>