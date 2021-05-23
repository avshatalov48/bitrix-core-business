<?
/** @global CUser $USER */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$requiredModules = array('report');

foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return 0;
	}
}

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($isPost && !check_bitrix_sessid())
{
	LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
}

$helperClassName = $arResult['HELPER_CLASS'] = isset($arParams['REPORT_HELPER_CLASS']) ? $arParams['REPORT_HELPER_CLASS'] : '';
if($isPost && isset($_POST['HELPER_CLASS']))
{
	$helperClassName = $arResult['HELPER_CLASS'] = $_POST['HELPER_CLASS'];
}

if (!is_string($helperClassName)
	|| mb_strlen($helperClassName) < 1
	|| !class_exists($helperClassName)
	|| !is_subclass_of($helperClassName, 'CReportHelper'))
{
	ShowError(GetMessage("REPORT_HELPER_NOT_DEFINED"));
	return 0;
}

$ownerId = $arResult['OWNER_ID'] = call_user_func(array($helperClassName, 'getOwnerId'));

if($isPost && isset($_POST['EXPORT_REPORT']))
{
	$reportId = intval($_POST['EXPORT_REPORT']);
	$rightsmanager = new Bitrix\Report\RightsManager($USER->GetID());
	if(!$rightsmanager->canRead($reportId))
	{
		$_SESSION['REPORT_LIST_ERROR'] = GetMessage('REPORT_ERROR_ACCESS_DENIED');
		LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
	}

	$queryObject = Bitrix\Report\ReportTable::getById($reportId);
	if($report = $queryObject->fetch())
	{
		unset($report['ID']);
		unset($report['CREATED_BY']);
		unset($report['CREATED_DATE']);
		unset($report['MARK_DEFAULT']);
		$arResult['REPORT'] = $report;
	}

	$APPLICATION->RestartBuffer();

	Header('Content-Type: text/csv');
	Header('Content-Disposition: attachment;filename=report'.$reportId.'.csv');

	$this->IncludeComponentTemplate('csv');
	die();
}

if($isPost && isset($_POST['IMPORT_REPORT']))
{
	if(is_uploaded_file($_FILES['IMPORT_REPORT_FILE']['tmp_name']))
	{
		$file = file_get_contents($_FILES['IMPORT_REPORT_FILE']['tmp_name']);
		$reportData = explode('|', $file);
		if(!empty($reportData) && is_array($reportData))
		{
			$fields = array();
			foreach($reportData as $report)
			{
				$field = explode('=', $report);
				if(empty($field[0]))
					continue;
				$fields[$field[0]] = $field[1];
			}
			$whileList = array('OWNER_ID', 'TITLE', 'DESCRIPTION', 'SETTINGS');
			foreach($whileList as $fieldId)
			{
				if(!array_key_exists($fieldId, $fields))
				{
					$_SESSION['REPORT_LIST_ERROR'] = GetMessage('REPORT_IMPORT_ERROR_FILE_EXT');
					LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
				}
			}
			$report = array(
				'OWNER_ID' => $fields['OWNER_ID'],
				'TITLE' => $fields['TITLE'],
				'DESCRIPTION' => $fields['DESCRIPTION'],
				'SETTINGS' => $fields['SETTINGS'],
				'MARK_DEFAULT' => 0,
				'CREATED_BY' => $USER->GetID()
			);
			Bitrix\Report\ReportTable::add($report);
		}
		else
		{
			$_SESSION['REPORT_LIST_ERROR'] = GetMessage('REPORT_IMPORT_ERROR_READ_UPLOADED_FILE');
		}
	}
	else
	{
		$_SESSION['REPORT_LIST_ERROR'] = GetMessage('REPORT_IMPORT_ERROR_UPLOADED_FILE');
	}
	LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
}

// auto create fresh default reports only if some reports alredy exist
$userReportVersion = CUserOptions::GetOption(
	'report', '~U_'.$ownerId,
	call_user_func(array($helperClassName, 'getFirstVersion'))
);

$sysReportVersion = call_user_func(array($helperClassName, 'getCurrentVersion'));
if ($sysReportVersion !== $userReportVersion  && CheckVersion($sysReportVersion, $userReportVersion))
{
	CUserOptions::SetOption('report', '~U_'.$ownerId, $sysReportVersion);

	if (CReport::GetCountInt($ownerId) > 0)
	{
		$dReports = call_user_func(array($helperClassName, 'getDefaultReports'));

		foreach ($dReports as  $moduleVer => $vReports)
		{
			if ($moduleVer !== $userReportVersion && CheckVersion($moduleVer, $userReportVersion))
			{
				// add fresh vReports
				CReport::addFreshDefaultReports($vReports, $ownerId);
			}
		}
	}
}

// create default reports by user request
if ($isPost && !empty($_POST['CREATE_DEFAULT']))
{
	$dReports = call_user_func(array($helperClassName, 'getDefaultReports'));
	foreach ($dReports as $moduleVer => $vReports)
	{
		CReport::addFreshDefaultReports($vReports, $ownerId);
	}

	LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
}

// main action
$arResult['list'] = array();

$userId = $USER->GetID();

$result = Bitrix\Report\ReportTable::getList(array(
	'order' => array('ID' => 'ASC'),
	'select' => array('ID', 'TITLE', 'DESCRIPTION', 'CREATED_DATE', 'MARK_DEFAULT'),
	'filter' => array('=CREATED_BY' => $userId, '=OWNER_ID' => $ownerId)
));

while ($row = $result->fetch())
{
	if(intval($row['MARK_DEFAULT']) > 0)
		$arResult['list']['default'][] = $row;
	else
		$arResult['list']['personal'][] = $row;
}

// add default reports always if them isn't present
if (empty($arResult['list']))
{
	$dReports = call_user_func(array($helperClassName, 'getDefaultReports'));
	foreach ($dReports as $moduleVer => $vReports)
	{
		CReport::addFreshDefaultReports($vReports, $ownerId);
	}

	LocalRedirect($arParams['PATH_TO_REPORT_LIST']);
}

/* Sharing reports */
$rightsManager = new Bitrix\Report\RightsManager($userId);
$listEntity = $rightsManager->getGroupsAndDepartments();

$result = Bitrix\Report\Internals\SharingTable::getList(array(
	'select' =>array('REPORT_ID', 'RIGHTS'),
	'filter'=>array('=ENTITY' => $listEntity),
));
$sharingRows = $result->fetchAll();
$listReportId = array();
$sharingData = array();
foreach($sharingRows as $rows)
{
	$listReportId[] = $rows['REPORT_ID'];
	$sharingData[$rows['REPORT_ID']]['REPORT_ID'] = $rows['REPORT_ID'];
	$sharingData[$rows['REPORT_ID']]['RIGHTS'] = $rows['RIGHTS'];
}

$queryObject = Bitrix\Report\ReportTable::getList(array(
	'select' => array('ID', 'TITLE', 'DESCRIPTION', 'CREATED_DATE', 'MARK_DEFAULT', 'CREATED_BY'),
	'filter' => array('=ID' => $listReportId, '=OWNER_ID' => $ownerId),
	'order' => array('ID' => 'ASC')
));
$arResult['SHARED_REPORT'] = array();
$arResult['NAME_FORMAT'] = CSite::getNameFormat(false);
while ($report = $queryObject->fetch())
{
	if($userId == $report['CREATED_BY'])
		continue;

	$users = CUser::getList($by='id', $order='asc', array('ID' => $report['CREATED_BY']),
		array('FIELDS' => array('ID', 'NAME', 'LAST_NAME')));
	if($user = $users->fetch())
	{
		$report['CREATED_BY_FULL'] = CUser::formatName($arResult['NAME_FORMAT'], $user, false, false);
	}
	$report['RIGHTS'] = $sharingData[$report['ID']]['RIGHTS'];
	$arResult['SHARED_REPORT'][] = $report;
}


$arResult['NEED_DISPLAY_UPDATE_14_5_2_MESSAGE'] = false;
if(CUserOptions::GetOption('report', 'NEED_DISPLAY_UPDATE_14_5_2_MESSAGE', 'Y') === 'Y')
{
	$arResult['NEED_DISPLAY_UPDATE_14_5_2_MESSAGE'] = true;
	CUserOptions::SetOption('report', 'NEED_DISPLAY_UPDATE_14_5_2_MESSAGE', 'N');
}

global $DB;
$arResult['dateFormat'] = $DB->DateFormatToPHP(CSite::GetDateFormat("SHORT"));
$arResult['randomString'] = $this->randString();

$this->IncludeComponentTemplate();

