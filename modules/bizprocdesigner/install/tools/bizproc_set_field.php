<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
\Bitrix\Main\Loader::includeModule('bizproc');

if (!check_bitrix_sessid())
	die();
if (!CBPDocument::CanUserOperateDocumentType(CBPCanUserOperateOperation::CreateWorkflow, $GLOBALS["USER"]->GetID(), $_REQUEST['DocumentType']))
	die();

$runtime = CBPRuntime::GetRuntime();
$runtime->StartRuntime();
$documentService = $runtime->GetService("DocumentService");

$v = $documentService->GetFieldInputValue($_REQUEST['DocumentType'], $_REQUEST['Type'], $_REQUEST['Field'], $_REQUEST, $arErrors);

echo CUtil::PhpToJSObject([$v, '']);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
