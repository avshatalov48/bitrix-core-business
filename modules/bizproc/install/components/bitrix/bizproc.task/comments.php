<?php
/**
 * Comments sandbox (iframe), for compatibility with Live Feed and BP task popup
 */
define("STOP_STATISTICS", true);
global $USER, $APPLICATION;

$SITE_ID = '';
if (isset($_REQUEST["site_id"]) && is_string($_REQUEST["site_id"]))
	$SITE_ID = substr(preg_replace("/[^a-z0-9_]/i", "", $_REQUEST["site_id"]), 0, 2);

if ($SITE_ID != '')
	define("SITE_ID", $SITE_ID);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (!$USER->IsAuthorized() || !check_bitrix_sessid() || !CModule::IncludeModule("bizproc"))
	die;

$taskId = isset($_REQUEST['TASK_ID'])? (int)$_REQUEST['TASK_ID'] : 0;
$userId = isset($_REQUEST['USER_ID'])? (int)$_REQUEST['USER_ID'] : 0;
if (!$userId)
	$userId = $USER->getId();

if ($userId != $USER->getId())
{
	$isAdmin = $USER->IsAdmin() || (CModule::IncludeModule('bitrix24') && CBitrix24::IsPortalAdmin($USER->GetID()));
	if (!$isAdmin && !CBPHelper::checkUserSubordination($USER->GetID(), $userId))
	{
		die;
	}
}

$task = null;

if ($taskId > 0)
{
	$dbTask = CBPTaskService::GetList(
		array(),
		array("ID" => $taskId, "USER_ID" => $userId),
		false,
		false,
		array("ID", "WORKFLOW_ID")
	);
	$task = $dbTask->fetch();
}

if (!$task)
{
	die;
}

$APPLICATION->RestartBuffer();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head><?php
	$APPLICATION->ShowHead();
	$APPLICATION->AddHeadString('
				<style>
				body {background: #F8FAFB !important;}
				.feed-comments-block {margin: 0;}
				</style>
			', false, true);
	?></head>
<body style="overflow-y: hidden;">
	<div id="wrapper">
	<?php
		// A < E < I < M < Q < U < Y
		// A - NO ACCESS, E - READ, I - ANSWER
		// M - NEW TOPIC
		// Q - MODERATE, U - EDIT, Y - FULL_ACCESS
		$APPLICATION->IncludeComponent("bitrix:forum.comments", "bitrix24", array(
			"FORUM_ID" => CBPHelper::getForumId(),
			"ENTITY_TYPE" => "WF",
			"ENTITY_ID" => CBPStateService::getWorkflowIntegerId($task['WORKFLOW_ID']),
			"ENTITY_XML_ID" => "WF_".$task['WORKFLOW_ID'],
			"PERMISSION" => "M",
			"URL_TEMPLATES_PROFILE_VIEW" => "/company/personal/user/#user_id#/",
			"SHOW_RATING" => "Y",
			"SHOW_LINK_TO_MESSAGE" => "N",
			"BIND_VIEWER" => "Y"
		),
			false,
			array('HIDE_ICONS' => 'Y')
		);
	?>
	</div>
</body>
</html><?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
die();