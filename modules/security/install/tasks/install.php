<?
// *******************************************************************************************************
// Install new right system: operation and tasks
// *******************************************************************************************************
// ############ SECURITY MODULE OPERATION ###########
$arFOp = Array();
$arFOp[] = Array('security_edit_user_otp', 'security', '', 'module');
$arFOp[] = Array('security_filter_bypass', 'security', '', 'module');
$arFOp[] = Array('security_module_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_module_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_panel_view', 'security', '', 'module');
$arFOp[] = Array('security_filter_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_filter_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_otp_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_otp_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_iprule_admin_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_iprule_admin_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_session_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_session_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_redirect_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_redirect_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_stat_activity_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_stat_activity_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_iprule_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_iprule_settings_write', 'security', '', 'module');
$arFOp[] = Array('security_file_verifier_sign', 'security', '', 'module');
$arFOp[] = Array('security_file_verifier_collect', 'security', '', 'module');
$arFOp[] = Array('security_file_verifier_verify', 'security', '', 'module');
$arFOp[] = Array('security_antivirus_settings_read', 'security', '', 'module');
$arFOp[] = Array('security_antivirus_settings_write', 'security', '', 'module');

// ############ SECURITY MODULE TASKS ###########
$arTasksF = Array();
$arTasksF[] = Array('security_denied', 'D', 'security', 'Y', '', 'module');
$arTasksF[] = Array('security_filter', 'F', 'security', 'Y', '', 'module');
$arTasksF[] = Array('security_otp', 'S', 'security', 'Y', '', 'module');
$arTasksF[] = Array('security_view_all_settings', 'T', 'security', 'Y', '', 'module');
$arTasksF[] = Array('security_full_access', 'W', 'security', 'Y', '', 'module');


//Operations in Tasks
$arOInT = Array();
//SECURITY: module
$arOInT['security_denied'] = Array(
);

$arOInT['security_filter'] = Array(
	'security_filter_bypass',
);

$arOInT['security_otp'] = Array(
	'security_edit_user_otp',
);

$arOInT['security_view_all_settings'] = Array(
	'security_module_settings_read',
	'security_panel_view',
	'security_filter_settings_read',
	'security_otp_settings_read',
	'security_iprule_admin_settings_read',
	'security_session_settings_read',
	'security_redirect_settings_read',
	'security_stat_activity_settings_read',
	'security_iprule_settings_read',
	'security_antivirus_settings_read',
);

$arOInT['security_full_access'] = Array(
	'security_edit_user_otp',
	'security_filter_bypass',
	'security_module_settings_read',
	'security_module_settings_write',
	'security_panel_view',
	'security_filter_settings_read',
	'security_filter_settings_write',
	'security_otp_settings_read',
	'security_otp_settings_write',
	'security_iprule_admin_settings_read',
	'security_iprule_admin_settings_write',
	'security_session_settings_read',
	'security_session_settings_write',
	'security_redirect_settings_read',
	'security_redirect_settings_write',
	'security_stat_activity_settings_read',
	'security_stat_activity_settings_write',
	'security_iprule_settings_read',
	'security_iprule_settings_write',
	'security_file_verifier_sign',
	'security_file_verifier_collect',
	'security_file_verifier_verify',
	'security_antivirus_settings_read',
	'security_antivirus_settings_write',
);

foreach($arFOp as $ar)
	$DB->Query("
		INSERT INTO b_operation
		(NAME,MODULE_ID,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

foreach($arTasksF as $ar)
	$DB->Query("
		INSERT INTO b_task
		(NAME,LETTER,MODULE_ID,SYS,DESCRIPTION,BINDING)
		VALUES
		('".$ar[0]."','".$ar[1]."','".$ar[2]."','".$ar[3]."','".$ar[4]."','".$ar[5]."')
	", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_group_task ###########
$sql_str = "
	INSERT INTO b_group_task
	(GROUP_ID,TASK_ID)
	SELECT MG.GROUP_ID, T.ID
	FROM
		b_task T
		INNER JOIN b_module_group MG ON MG.G_ACCESS = T.LETTER
	WHERE
		T.SYS = 'Y'
		AND T.BINDING = 'module'
		AND MG.MODULE_ID = 'security'
		AND T.MODULE_ID = MG.MODULE_ID
";
$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

// ############ b_task_operation ###########
foreach($arOInT as $tname => $arOp)
{
	$sql_str = "
		INSERT INTO b_task_operation
		(TASK_ID,OPERATION_ID)
		SELECT T.ID TASK_ID, O.ID OPERATION_ID
		FROM
			b_task T
			,b_operation O
		WHERE
			T.SYS='Y'
			AND T.NAME='".$tname."'
			AND O.NAME in ('".implode("','", $arOp)."')
			AND O.NAME not in (
				SELECT O2.NAME
				FROM
					b_task T2
					inner join b_task_operation TO2 on TO2.TASK_ID = T2.ID
					inner join b_operation O2 on O2.ID = TO2.OPERATION_ID
				WHERE
					T2.SYS='Y'
					AND T2.NAME='".$tname."'
			)
	";
	$z = $DB->Query($sql_str, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}

global $CACHE_MANAGER;
if(is_object($CACHE_MANAGER))
{
	$CACHE_MANAGER->CleanDir("b_task");
	$CACHE_MANAGER->CleanDir("b_task_operation");
}

?>