<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!CModule::IncludeModule("bizproc"))
	return false;

global $USER, $APPLICATION;

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm('');
	return false;
}

$currentUserId = $USER->GetID();
$isAdmin = $USER->IsAdmin() || (CModule::IncludeModule('bitrix24') && CBitrix24::IsPortalAdmin($USER->GetID()));

$targetUserId = intval(empty($arParams["USER_ID"]) ? $USER->GetID() : $arParams["USER_ID"]);
if (
	$targetUserId != $currentUserId
	&& !$isAdmin
	&& !CBPHelper::checkUserSubordination($currentUserId, $targetUserId)
)
{
	ShowError(GetMessage("BPATL_ERROR_SUBORDINATION"));
	return false;
}

$arParams["WORKFLOW_ID"] = (empty($arParams["WORKFLOW_ID"]) ? ($_REQUEST["WORKFLOW_ID"] ?? '') : $arParams["WORKFLOW_ID"]);

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arResult['back_url'] = urlencode(empty($_REQUEST['back_url']) ? $APPLICATION->GetCurPage() : $_REQUEST['back_url']);

$arParams["TASK_EDIT_URL"] =
	isset($arParams["TASK_EDIT_URL"]) && is_string($arParams["TASK_EDIT_URL"])
		? trim($arParams["TASK_EDIT_URL"])
		: ''
;
if (empty($arParams["TASK_EDIT_URL"]))
	$arParams["TASK_EDIT_URL"] = $APPLICATION->GetCurPage()."?PAGE_NAME=task_edit&ID=#ID#&back_url=".$arResult["back_url"];
else
	$arParams["TASK_EDIT_URL"] .= (mb_strpos($arParams["TASK_EDIT_URL"], "?") === false ? "?" : "&")."back_url=".$arResult["back_url"];

$arParams["~TASK_EDIT_URL"] = $arParams["TASK_EDIT_URL"];
$arParams["TASK_EDIT_URL"] = htmlspecialcharsbx($arParams["~TASK_EDIT_URL"]);

$arParams['PAGE_ELEMENTS'] =
	(isset($arParams['PAGE_ELEMENTS']) && intval($arParams['PAGE_ELEMENTS']) > 0)
		? intval($arParams['PAGE_ELEMENTS'])
		: 50
;

$arParams["PAGE_NAVIGATION_TEMPLATE"] =
	isset($arParams["PAGE_NAVIGATION_TEMPLATE"]) && is_string($arParams["PAGE_NAVIGATION_TEMPLATE"])
		? trim($arParams["PAGE_NAVIGATION_TEMPLATE"])
		: ''
;
$arParams["SHOW_TRACKING"] = isset($arParams['SHOW_TRACKING']) && $arParams['SHOW_TRACKING'] === 'Y' ? 'Y' : 'N';

$arParams["SET_TITLE"] = isset($arParams['SET_TITLE']) && $arParams['SET_TITLE'] === 'N' ? 'N' : 'Y'; //Turn on by default
$arParams["SET_NAV_CHAIN"] = isset($arParams['SET_NAV_CHAIN']) && $arParams['SET_NAV_CHAIN'] === 'N' ? 'N' : 'Y'; //Turn on by default
$arParams['COUNTERS_ONLY'] = (isset($arParams['COUNTERS_ONLY']) && $arParams['COUNTERS_ONLY'] == 'Y');

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arResult["NAV_RESULT"] = "";
$arResult["NAV_STRING"] = "";
$arResult["TASKS"] = array();
$arResult["TRACKING"] = array();

if ($arResult["FatalErrorMessage"] == '' && !$arParams['COUNTERS_ONLY'])
{
	$arResult['ERRORS'] = array();
	$arResult['USE_SUBORDINATION'] = (bool)CModule::IncludeModule('intranet');
	$arResult['GRID_ID'] = 'bizproc_task_list';
	$arResult['FILTER_ID'] = 'bizproc_task_list_filter';

	$arSelectFields = [
		"ID", "WORKFLOW_ID", "PARAMETERS", "MODIFIED", "OVERDUE_DATE", 'IS_INLINE', 'STATUS',
		'USER_ID', 'USER_STATUS', 'WORKFLOW_STATE', 'ACTIVITY', 'DOCUMENT_NAME'
	];

	$gridOptions = new CGridOptions($arResult["GRID_ID"]);
	$gridColumns = $gridOptions->GetVisibleColumns();
	$gridSort = $gridOptions->GetSorting(array("sort" => array("ID" => "desc")));

	$arResult['COLUMNS'] = [
		[
			'id' => 'ID',
			'name' => 'ID',
			'default' => false,
			'sort' => 'ID',
		],
		[
			'id' => 'DOCUMENT_NAME',
			'name' => GetMessage('BPATL_DOCUMENT_NAME'),
			'default' => false,
			'sort' => 'DOCUMENT_NAME'
		],
		[
			'id' => 'DESCRIPTION',
			'name' => GetMessage('BPATL_DESCRIPTION'),
			'default' => true,
			'sort' => '',
		],
		[
			'id' => 'COMMENTS',
			'name' => GetMessage('BPATL_COMMENTS'),
			'default' => true,
			'sort' => '',
			'hideName' => true,
			'iconCls' => 'bp-comments-icon',
		],
		[
			'id' => 'WORKFLOW_PROGRESS',
			'name' => GetMessage('BPATL_WORKFLOW_PROGRESS'),
			'default' => true,
			'sort' => '',
		],
		[
			'id' => 'NAME',
			'name' => GetMessage('BPATL_NAME'),
			'default' => true,
			'sort' => 'NAME',
		],
		[
			'id' => 'MODIFIED',
			'name' => GetMessage('BPATL_MODIFIED'),
			'default' => false,
			'sort' => 'MODIFIED',
		],
		[
			'id' => 'WORKFLOW_STARTED',
			'name' => GetMessage('BPATL_STARTED'),
			'default' => false,
			'sort' => 'WORKFLOW_STARTED',
		],
		[
			'id' => 'WORKFLOW_STARTED_BY',
			'name' => GetMessage('BPATL_STARTED_BY'),
			'default' => false,
			'sort' => 'WORKFLOW_STARTED_BY',
		],
		[
			'id' => 'OVERDUE_DATE',
			'name' => GetMessage('BPATL_OVERDUE_DATE'),
			'default' => false,
			'sort' => 'OVERDUE_DATE',
		],
		[
			'id' => 'WORKFLOW_TEMPLATE_NAME',
			'name' => GetMessage('BPATL_WORKFLOW_NAME'),
			'default' => false,
			'sort' => 'WORKFLOW_TEMPLATE_NAME',
		],
		[
			'id' => 'WORKFLOW_STATE',
			'name' => GetMessage('BPATL_WORKFLOW_STATE'),
			'default' => false,
			'sort' => 'WORKFLOW_STATE',
		],
	];

	foreach ($arResult['COLUMNS'] as $column)
	{
		if (
			!$gridColumns
			|| (
				in_array($column['id'], $gridColumns, true)
				&& !in_array($column['id'], $arSelectFields, true)
			)
		)
		{
			$arSelectFields[] = $column['id'];
		}
	}

	$arResult['FILTER'] = [
		[
			'id' => 'NAME',
			'name' => GetMessage('BPATL_NAME'),
			'type' => 'string',
			'default' => true
		],
		[
			'id' => 'DESCRIPTION',
			'name' => GetMessage('BPATL_DESCRIPTION'),
			'type' => 'string'
		],
		[
			'id' => 'MODIFIED',
			'name' => GetMessage('BPATL_MODIFIED'),
			'type' => 'date',
			'default' => true,
		],
		[
			'id' => 'USER_STATUS',
			'name' => GetMessage('BPATL_FILTER_STATUS'),
			'type' => 'list',
			'items' => [
				2 => GetMessage('BPATL_FILTER_STATUS_ALL'),
				0 => GetMessage('BPATL_FILTER_STATUS_RUNNING_1'),
				1 => GetMessage('BPATL_FILTER_STATUS_COMPLETE_1'),
			],
			'default' => true
		],
	];
	if (empty($arParams['SHOW_DOCUMENT_TYPES_TOOLBAR']) || $arParams['SHOW_DOCUMENT_TYPES_TOOLBAR'] !== 'N')
	{
		$arResult['FILTER'][] = [
			'id' => 'DOCUMENT_TYPE',
			'name' => \Bitrix\Main\Localization\Loc::getMessage('BPATL_FILTER_DOCTYPE'),
			'type' => 'list',
			'items' => [
				'' => GetMessage('BPATL_FILTER_DOCTYPE_ALL'),
				'processes' => GetMessage('BPATL_FILTER_DOCTYPE_CLAIMS'),
				'crm' => GetMessage('BPATL_FILTER_DOCTYPE_CRM'),
				'disk' => GetMessage('BPATL_FILTER_DOCTYPE_DISK'),
				'lists' => GetMessage('BPATL_FILTER_DOCTYPE_LISTS'),
				'rpa' => GetMessage('BPATL_FILTER_DOCTYPE_RPA')
			],
			'default' => true,
		];
	}

	if ($arResult['USE_SUBORDINATION'])
	{
		$arResult['FILTER'][] = [
			'id' => 'USER_ID',
			'name' => GetMessage('BPATL_FILTER_USER'),
			'type' => 'entity_selector',
			'default' => true,
			'params' => [
				'multiple' => 'N',
				'dialogOptions' => [
					'context' => 'filter',
					'entities' => [
						[
							'id' => 'user',
							'options' => [
								'intranetUsersOnly' => true,
								'inviteEmployeeLink' => false,
							],
						],
					],
				],
			],
		];
	}

	$arResult['FILTER_PRESETS'] = [
		'filter_running' => [
			'name' => GetMessage('BPATL_FILTER_STATUS_RUNNING_1'),
			'fields' => ['USER_STATUS' => 0],
			'default' => true,
		],
		'filter_complete' => [
			'name' => GetMessage('BPATL_FILTER_STATUS_COMPLETE_1'),
			'fields' => ['USER_STATUS' => 1],
		],
	];

	$arFilter = [
		'USER_ID' => $targetUserId,
		'USER_STATUS' => CBPTaskUserStatus::Waiting,
	];

	if (!empty($arParams['MODULE_ID']))
	{
		$arFilter['MODULE_ID'] = $arParams['MODULE_ID'];
		unset($arFilter['ENTITY']);
	}

	if (empty($arParams["WORKFLOW_ID"]))
	{
		$ar = array("" => GetMessage("BPATL_WORKFLOW_ID_ANY"));
		$dbResTmp = CBPWorkflowTemplateLoader::GetList(
			array('NAME' => 'ASC'),
			array(
				"ACTIVE" => "Y",
				"USER_ID" => $currentUserId,
				'<AUTO_EXECUTE' => CBPDocumentEventType::Automation
			),
			false, false,
			array('ID', 'NAME')
		);

		while ($arResTmp = $dbResTmp->GetNext())
			$ar[$arResTmp['ID']] = $arResTmp["NAME"];

		$arResult["FILTER"][] = array("id" => "WORKFLOW_TEMPLATE_ID", "name" => GetMessage("BPATL_WORKFLOW_ID"), "type" => "list", "items" => $ar);
	}
	else
	{
		$arFilter["WORKFLOW_ID"] = $arParams["WORKFLOW_ID"];
	}

	if (!empty($_REQUEST['USER_ID']) && !empty($_REQUEST['clear_filter']))
		unset($_REQUEST['USER_ID']);

	$filterOptions = new \Bitrix\Main\UI\Filter\Options($arResult['FILTER_ID']);
	$gridFilter = $filterOptions->getFilter();

	if (isset($gridFilter['DOCUMENT_TYPE']))
	{
		$moduleId = $gridFilter['DOCUMENT_TYPE'];
		switch ($moduleId)
		{
			case 'processes':
				$arFilter['MODULE_ID'] = 'lists';
				$arFilter['ENTITY'] = 'BizprocDocument';
				break;

			case 'lists':
				$arFilter['ENTITY'] = 'Bitrix\Lists\BizprocDocumentLists';

			case 'crm':
			case 'disk':
			case 'rpa':
				$arFilter['MODULE_ID'] = $moduleId;
				break;
		}

		unset($gridFilter['DOCUMENT_TYPE']);
	}

	foreach ($gridFilter as $key => $value)
	{
		if (mb_substr($key, -5) == "_from")
		{
			$op = ">=";
			$newKey = mb_substr($key, 0, -5);

			if (in_array($newKey, array("MODIFIED", "OVERDUE_DATE")) && mb_strlen($value) <= 10)
			{
				$dt = MakeTimeStamp($value, FORMAT_DATE);
				$value = FormatDate('FULL', $dt);
			}

		}
		elseif (mb_substr($key, -3) == "_to")
		{
			$op = "<=";
			$newKey = mb_substr($key, 0, -3);

			if (in_array($newKey, array("MODIFIED", "OVERDUE_DATE")) && mb_strlen($value) <= 10)
			{
				$dt = MakeTimeStamp($value, FORMAT_DATE) + 86399;// + 23:59:59
				$value = FormatDate('FULL', $dt);
			}
		}
		else
		{
			$op = "";
			$newKey = $key;
		}

		if (!in_array($newKey, array("NAME", "MODIFIED", "OVERDUE_DATE", "WORKFLOW_TEMPLATE_ID", "DESCRIPTION", 'USER_ID', 'USER_STATUS')))
			continue;

		if (in_array($newKey, array("NAME", "DESCRIPTION")) && $op == "")
		{
			$op = "~";
			$value = "%".$value."%";
		}

		if ($newKey == 'USER_STATUS')
		{
			if ($value == 2)
			{
				unset($arFilter['USER_STATUS']);
				continue;
			}
			if ($value == 1)
			{
				$value = array(CBPTaskUserStatus::Ok, CBPTaskUserStatus::Yes, CBPTaskUserStatus::No, CBPTaskUserStatus::Cancel);
				$arResult['IS_COMPLETED'] = true;
			}
			else
				$value = CBPTaskUserStatus::Waiting;
		}

		if ($newKey == 'USER_ID')
		{
			if (!$value || $value == $targetUserId)
				continue;

			if ($isAdmin || CBPHelper::checkUserSubordination($currentUserId, $value))
			{
				$targetUserId = $value;
			}
			else
			{
				$arResult['ERRORS'][] = GetMessage('BPATL_ERROR_SUBORDINATION');
				$value = 0;
			}
		}

		$arFilter[$op.$newKey] = $value;
	}
	$arResult["SORT"] = $gridSort["sort"];
	$arResult["RECORDS"] = array();

	if (!empty($_REQUEST['action_button_'.$arResult["GRID_ID"]]) && check_bitrix_sessid())
	{
		$action = $_REQUEST['action_button_'.$arResult["GRID_ID"]];
		$ids = (isset($_REQUEST['ID']) && is_array($_REQUEST['ID'])) ? $_REQUEST['ID'] : null;
		if (isset($_REQUEST['action_all_rows_'.$arResult["GRID_ID"]]) && $_REQUEST['action_all_rows_'.$arResult["GRID_ID"]] == 'Y')
			$ids = array();
		if (is_array($ids))
		{
			if (mb_strpos($action, 'set_status_') === 0)
			{
				$status = mb_substr($action, mb_strlen('set_status_'));
				CBPDocument::setTasksUserStatus($targetUserId, $status, $ids, $arResult['ERRORS']);
			}
			if ($action == 'delegate_to' && !empty($_REQUEST['ACTION_DELEGATE_TO_ID']))
			{
				$allowedDelegationType = array(CBPTaskDelegationType::AllEmployees);
				if ($isAdmin)
				{
					$allowedDelegationType = null;
				}
				elseif (CBPHelper::checkUserSubordination($currentUserId, $_REQUEST['ACTION_DELEGATE_TO_ID']))
				{
					$allowedDelegationType[] = CBPTaskDelegationType::Subordinate;
				}

				CBPDocument::delegateTasks(
					$targetUserId,
					$_REQUEST['ACTION_DELEGATE_TO_ID'],
					$ids,
					$arResult['ERRORS'],
					$allowedDelegationType
				);
			}
		}
	}

	$dbRecordsList = CBPTaskService::GetList(
		$gridSort["sort"],
		$arFilter,
		false,
		$gridOptions->GetNavParams(),
		$arSelectFields
	);
	$arResult['IS_MY_TASKS'] = $currentUserId == $targetUserId;
	$arResult['TARGET_USER_ID'] = (int)$targetUserId;

	$useComments = (bool)CModule::IncludeModule("forum");
	$workflows = array();

	while ($arRecord = $dbRecordsList->getNext())
	{
		if ($useComments)
			$workflows[] = 'WF_'.$arRecord['WORKFLOW_ID'];

		$arRecord["IS_MY"] = $arResult['IS_MY_TASKS'];
		$arRecord['MODIFIED'] = FormatDateFromDB($arRecord['MODIFIED']);
		$documentId = isset($arRecord["PARAMETERS"]["DOCUMENT_ID"]) && is_array($arRecord["PARAMETERS"]["DOCUMENT_ID"]) ?
			$arRecord["PARAMETERS"]["DOCUMENT_ID"] : null;
		$arRecord["DOCUMENT_URL"] = $documentId ? CBPDocument::GetDocumentAdminPage($documentId) : '';

		$arRecord["MODULE_ID"] = $documentId ? $documentId[0] : '';
		$arRecord["ENTITY"] = $documentId ? $documentId[1] : '';
		$arRecord["DOCUMENT_ID"] = $documentId ? $documentId[2] : '';

		if (empty($arRecord['DOCUMENT_NAME']))
		{
			$arRecord['DOCUMENT_NAME'] = GetMessage("BPATL_DOCUMENT_NAME");
		}

		$taskUrlParams = ["ID" => $arRecord["ID"], "task_id" => $arRecord["ID"]];
		if (!empty($arRecord["PARAMETERS"]["TASK_EDIT_URL"]))
		{
			$arRecord["URL"] = array(
				"~TASK" => CComponentEngine::MakePathFromTemplate(
					$arRecord["PARAMETERS"]["TASK_EDIT_URL"], $taskUrlParams
				),
				"TASK" => CComponentEngine::MakePathFromTemplate(
					$arRecord["PARAMETERS"]["TASK_EDIT_URL"], $taskUrlParams
				)
			);
		}
		else
		{
			$arRecord["URL"] = array(
				"~TASK" => CComponentEngine::MakePathFromTemplate($arParams["~TASK_EDIT_URL"], $taskUrlParams),
				"TASK" => CComponentEngine::MakePathFromTemplate($arParams["TASK_EDIT_URL"], $taskUrlParams)
			);
		}

		if (isset($arRecord['WORKFLOW_TEMPLATE_NAME']))
			$arRecord["WORKFLOW_NAME"] = $arRecord["WORKFLOW_TEMPLATE_NAME"]; // compatibility
		if (isset($arRecord['WORKFLOW_STARTED']))
			$arRecord["WORKFLOW_STARTED"] = FormatDateFromDB($arRecord["WORKFLOW_STARTED"]);

		if (!empty($arRecord['WORKFLOW_STARTED_BY']))
		{
			$tmpUserId = (int) $arRecord['WORKFLOW_STARTED_BY'];
			$arRecord["WORKFLOW_STARTED_BY"] = "";
			if ($tmpUserId > 0)
			{
				$dbUserTmp = CUser::GetByID($tmpUserId);
				$arUserTmp = $dbUserTmp->fetch();
				$arRecord["WORKFLOW_STARTED_BY"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arUserTmp, true);
				$arRecord["WORKFLOW_STARTED_BY"] .= " [".$tmpUserId."]";
			}
		}

		if (!$arRecord["IS_MY"])
		{
			$arRecord["URL"]["TASK"] = CHTTP::urlAddParams($arRecord["URL"]["TASK"], array('USER_ID' => $targetUserId));
			if (isset($arRecord['PARAMETERS']['AccessControl']) && $arRecord['PARAMETERS']['AccessControl'] == 'Y')
			{
				$arRecord["DESCRIPTION"] = '';
			}
		}

		$aActions = [
			[
				'DEFAULT' => true,
				'TEXT' => GetMessage('BPTL_C_DETAIL'),
				'ONCLICK' => 'window.location="' . $arRecord['URL']['TASK'] . '";',
			],
		];
		if ($arRecord['DOCUMENT_URL'] !== '')
		{
			$aActions[] = [
				'DEFAULT' => false,
				'TEXT' => GetMessage('BPTL_C_DOCUMENT'),
				'ONCLICK' => 'window.open("' . $arRecord['DOCUMENT_URL'] . '");'
			];
		}

		$arResult['RECORDS'][] = [
			'data' => $arRecord,
			'actions' => $aActions,
			'editable' => $arRecord['STATUS'] == CBPTaskStatus::Running,
		];
	}

	$arResult["COMMENTS_COUNT"] = array();
	if ($useComments)
	{
		$workflows = array_unique($workflows);
		if ($workflows)
		{
			$iterator = CForumTopic::getList(array(), array("@XML_ID" => $workflows));
			while ($row = $iterator->fetch())
			{
				$arResult["COMMENTS_COUNT"][$row['XML_ID']] = $row['POSTS'];
			}
		}
	}

	$arResult["ROWS_COUNT"] = $dbRecordsList->SelectedRowsCount();
	$arResult["NAV_STRING"] = $dbRecordsList->GetPageNavStringEx($navComponentObject, GetMessage("INTS_TASKS_NAV"), "", false);
	$arResult["NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["NAV_RESULT"] = $dbRecordsList;

	$arResult['HIDE_WORKFLOW_PROGRESS'] = $gridColumns && is_array($gridColumns) && !in_array('WORKFLOW_PROGRESS', $gridColumns);
}

if ($arParams["SHOW_TRACKING"] == "Y")
{
	$arResult["H_GRID_ID"] = "bizproc_tasksListH_".$currentUserId;

	$hgridOptions = new CGridOptions($arResult["H_GRID_ID"]);
	$hgridColumns = $hgridOptions->GetVisibleColumns();
	$hgridSort = $hgridOptions->GetSorting(array("sort"=>array("ID" => "desc")));

	$arResult["H_HEADERS"] = array(
		array("id" => "MODIFIED", "name" => GetMessage("BPATL_MODIFIED"), "default" => true, "sort" => ""),
		array("id" => "ACTION_NOTE", "name" => GetMessage("BPATL_DESCRIPTION"), "default" => true, "sort" => ""),
	);

	$arResult["H_SORT"] = $hgridSort["sort"];

	$arResult["H_RECORDS"] = array();

	$arFilter = array("MODIFIED_BY" => $targetUserId);
	if (!empty($arParams["WORKFLOW_ID"]))
		$arFilter["WORKFLOW_ID"] = $arParams["WORKFLOW_ID"];

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");

	$dbRecordsList = CBPTrackingService::GetList(
		$hgridSort["sort"],
		$arFilter
	);
	while ($arRecord = $dbRecordsList->GetNext())
	{
		if ($arRecord["WORKFLOW_ID"] <> '')
		{
			$arRecord["STATE"] = CBPStateService::GetWorkflowState($arRecord["WORKFLOW_ID"]);
			$arRecord["DOCUMENT_URL"] = CBPDocument::GetDocumentAdminPage($arRecord["STATE"]["DOCUMENT_ID"]);

			try
			{
				$dt = $documentService->GetDocumentType($arRecord["STATE"]["DOCUMENT_ID"]);
			}
			catch (Exception $e)
			{

			}

			$arRecord["ACTION_NOTE"] = CBPTrackingService::parseStringParameter($arRecord["ACTION_NOTE"], $dt);
		}

		$aActions = array();
		if ($arRecord["DOCUMENT_URL"] <> '')
			$aActions[] = array("ICONCLASS"=>"", "DEFAULT" => false, "TEXT"=>GetMessage("BPTL_C_DOCUMENT"), "ONCLICK"=>"window.open('".$arRecord["DOCUMENT_URL"]."');");

		$arResult["H_RECORDS"][] = array("data" => $arRecord, "actions" => $aActions, "columns" => array(), "editable" => false);
	}

	$arResult["H_ROWS_COUNT"] = $dbRecordsList->SelectedRowsCount();
	$arResult["H_NAV_STRING"] = $dbRecordsList->GetPageNavStringEx($navComponentObject, GetMessage("INTS_TASKS_NAV"), "", false);
	$arResult["H_NAV_CACHED_DATA"] = $navComponentObject->GetTemplateCachedData();
	$arResult["H_NAV_RESULT"] = $dbRecordsList;
}

if ($arResult["FatalErrorMessage"] == '')
{
	if (!$arParams['COUNTERS_ONLY'])
	{
		if($arParams["SET_TITLE"] == "Y")
			$APPLICATION->SetTitle(GetMessage("BPABS_TITLE"));
		if ($arParams["SET_NAV_CHAIN"] == "Y")
			$APPLICATION->AddChainItem(GetMessage("BPABS_TITLE"));
	}

	$arResult['COUNTERS'] = CBPTaskService::getCounters($targetUserId);

	if ($arParams['COUNTERS_ONLY'])
	{
		$arResult['COUNTERS_RUNNING'] = CBPStateService::getRunningCounters($targetUserId);

	}

	//counter autofixer
	$currentCounter = (int)CUserCounter::GetValue($targetUserId, 'bp_tasks', '**');
	if (isset($arResult['COUNTERS']['*']) && $currentCounter != $arResult['COUNTERS']['*'])
	{
		CUserCounter::Set($targetUserId, 'bp_tasks', $arResult['COUNTERS']['*'], '**');
	}
}
elseif (!$arParams['COUNTERS_ONLY'])
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WLC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WLC_ERROR"));
}

$this->IncludeComponentTemplate();
