<?
/**
 * @deprecated
 * Now use \Bitrix\Bizproc\Controller\Activity
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('bizproc');

global $APPLICATION;

if (!check_bitrix_sessid())
	die();
if (!CBPDocument::CanUserOperateDocumentType(CBPCanUserOperateOperation::CreateWorkflow, $GLOBALS["USER"]->GetID(), $_REQUEST['document_type']))
	die();

$activityType = $_REQUEST['activity'];

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();

$arActivityDescription = $runtime->GetActivityDescription($activityType);
if ($arActivityDescription == null)
	die ("Bad activity type!".htmlspecialcharsbx($activityType));

$runtime->IncludeActivityFile($activityType);

$isHtml = (!empty($_REQUEST['content_type']) && $_REQUEST['content_type'] == 'html');
if ($isHtml)
	$APPLICATION->ShowAjaxHead();

$res = CBPActivity::CallStaticMethod(
	$activityType,
	"getAjaxResponse",
	array(
		$_REQUEST
	)
);
echo $isHtml? $res : CUtil::PhpToJSObject($res);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");